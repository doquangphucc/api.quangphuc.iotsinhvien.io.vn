<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connect.php';

try {
    // Lấy tham số từ query string
    $username = $_GET['username'] ?? '';
    $status = $_GET['status'] ?? 'all'; // 'all', 'completed', 'pending'
    $limit = (int)($_GET['limit'] ?? 100);
    $offset = (int)($_GET['offset'] ?? 0);
    $sort = $_GET['sort'] ?? 'created_at'; // 'created_at', 'title', 'due_date', 'priority'
    $order = strtoupper($_GET['order'] ?? 'DESC'); // 'ASC' hoặc 'DESC'
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Validate sort column
    $validSortColumns = ['created_at', 'title', 'due_date', 'priority', 'category'];
    if (!in_array($sort, $validSortColumns)) {
        $sort = 'created_at';
    }

    // Validate order
    if (!in_array($order, ['ASC', 'DESC'])) {
        $order = 'DESC';
    }

    // Xây dựng điều kiện WHERE cho status
    $statusCondition = '';
    $params = [$username];
    
    if ($status === 'completed') {
        $statusCondition = ' AND t.status = 1';
    } elseif ($status === 'pending') {
        $statusCondition = ' AND t.status = 0';
    }

    // Đếm tổng số tasks
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM tasks t
        LEFT JOIN tai_khoan tk ON t.user_id = tk.id
        WHERE tk.user = ? $statusCondition
    ");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy danh sách tasks với phân trang (thêm t.id cuối ORDER BY để tránh mập mờ khi cùng timestamp)
    $stmt = $pdo->prepare("
        SELECT 
            t.id, t.title, t.description, t.category, t.priority, 
            t.scheduled_date as due_date, t.status as is_completed, 
            t.created_at, t.updated_at,
            CASE 
                WHEN t.scheduled_date IS NOT NULL AND t.scheduled_date < CURDATE() AND t.status = 0 
                THEN 1 
                ELSE 0 
            END as is_overdue
        FROM tasks t
        LEFT JOIN tai_khoan tk ON t.user_id = tk.id
        WHERE tk.user = ? $statusCondition
        ORDER BY $sort $order, t.id DESC
        LIMIT ? OFFSET ?
    ");
    
    $executeParams = array_merge($params, [$limit, $offset]);
    $stmt->execute($executeParams);
    $rawTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Khử trùng lặp theo id (nếu vì lý do nào đó MySQL trả trùng)
    $uniq = [];
    foreach ($rawTasks as $row) {
        $uniq[$row['id']] = $row; // overwrite duplicates with latest row data
    }
    $tasks = array_values($uniq);

    // Format dữ liệu bằng array_map để tránh tham chiếu
    $tasks = array_map(function($task){
        $task['id'] = (int)$task['id'];
        $task['is_completed'] = (bool)$task['is_completed'];
        $task['is_overdue'] = (bool)$task['is_overdue'];
        if ($task['due_date']) {
            $task['due_date_formatted'] = date('d/m/Y', strtotime($task['due_date']));
            $task['due_date_relative'] = getDaysUntilDue($task['due_date']);
        } else {
            $task['due_date_formatted'] = null;
            $task['due_date_relative'] = null;
        }
        $task['created_at_formatted'] = date('d/m/Y H:i', strtotime($task['created_at']));
        if (!empty($task['updated_at'])) {
            $task['updated_at_formatted'] = date('d/m/Y H:i', strtotime($task['updated_at']));
        }
        return $task;
    }, $tasks);

    // Thống kê nhanh
    $stats = [
        'total' => $totalCount,
        'completed' => 0,
        'pending' => 0,
        'overdue' => 0
    ];

    foreach ($tasks as $task) {
        if ($task['is_completed']) {
            $stats['completed']++;
        } else {
            $stats['pending']++;
            if ($task['is_overdue']) {
                $stats['overdue']++;
            }
        }
    }
    $payload = [
        'success' => true,
        'data' => $tasks,
        'stats' => $stats,
        'pagination' => [
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalCount
        ],
        'message' => 'Tasks loaded successfully'
    ];
    if (!empty($_GET['debug'])) {
        $payload['raw'] = $rawTasks; // trước khử trùng lặp / format
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// Helper function để tính số ngày đến hạn
function getDaysUntilDue($dueDate) {
    if (!$dueDate) return null;
    
    $today = new DateTime();
    $due = new DateTime($dueDate);
    $diff = $today->diff($due);
    
    if ($due < $today) {
        return "Quá hạn " . $diff->days . " ngày";
    } elseif ($diff->days === 0) {
        return "Hôm nay";
    } elseif ($diff->days === 1) {
        return "Ngày mai";
    } else {
        return "Còn " . $diff->days . " ngày";
    }
}
?>
