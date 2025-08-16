<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connect.php';

try {
    // Lấy tham số từ query
    $username = $_GET['username'] ?? '';
    $type = $_GET['type'] ?? 'both'; // 'tasks', 'wishes', hoặc 'both'
    $status = $_GET['status'] ?? 'all'; // 'completed', 'pending', hoặc 'all'
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    $sort = $_GET['sort'] ?? 'created_at'; // 'created_at', 'title', 'due_date', 'priority'
    $order = $_GET['order'] ?? 'DESC'; // 'ASC' hoặc 'DESC'
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    $data = [
        'tasks' => [],
        'wishes' => [],
        'total_count' => 0,
        'filtered_count' => 0
    ];

    // Xây dựng điều kiện WHERE cho status
    $statusCondition = '';
    $statusParams = [];
    
    if ($status === 'completed') {
        $statusCondition = ' AND t.completed = 1';
    } elseif ($status === 'pending') {
        $statusCondition = ' AND t.completed = 0';
    }

    // Validate sort column - dựa trên database schema thực tế
    $validSortColumns = ['created_at', 'updated_at', 'title', 'scheduled_date'];
    if (!in_array($sort, $validSortColumns)) {
        $sort = 'created_at';
    }

    // Validate order
    $order = strtoupper($order);
    if (!in_array($order, ['ASC', 'DESC'])) {
        $order = 'DESC';
    }

    // Lấy tasks nếu được yêu cầu
    if ($type === 'tasks' || $type === 'both') {
        // Đếm tổng số tasks
        $countTasksStmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM tasks t
            WHERE t.username = ? $statusCondition
        ");
        $countTasksStmt->execute([$username]);
        $tasksCount = $countTasksStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy danh sách tasks - theo database schema thực tế
        $tasksStmt = $pdo->prepare("
            SELECT 
                t.id, t.username, t.title, t.description, 
                t.scheduled_date, t.scheduled_time, t.completed as is_completed, 
                t.created_at, t.updated_at
            FROM tasks t
            WHERE t.username = ? $statusCondition
            ORDER BY t.$sort $order
            LIMIT ? OFFSET ?
        ");
        $tasksStmt->execute([$username, $limit, $offset]);
        $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format dữ liệu tasks - theo database schema
        foreach ($tasks as &$task) {
            $task['type'] = 'task';
            $task['id'] = (int)$task['id'];
            $task['is_completed'] = (bool)$task['is_completed'];
            
            // Format dates
            if ($task['scheduled_date']) {
                $task['scheduled_date_formatted'] = date('d/m/Y', strtotime($task['scheduled_date']));
            }
            if ($task['scheduled_time']) {
                $task['scheduled_time_formatted'] = date('H:i', strtotime($task['scheduled_time']));
            }
            $task['created_at_formatted'] = date('d/m/Y H:i', strtotime($task['created_at']));
            if ($task['updated_at']) {
                $task['updated_at_formatted'] = date('d/m/Y H:i', strtotime($task['updated_at']));
            }
        }

        $data['tasks'] = $tasks;
        $data['total_count'] += $tasksCount;
        $data['filtered_count'] += count($tasks);
    }

    // Sửa điều kiện status cho wishes
    $wishStatusCondition = '';
    if ($status === 'completed') {
        $wishStatusCondition = ' AND w.completed = 1';
    } elseif ($status === 'pending') {
        $wishStatusCondition = ' AND w.completed = 0';
    }

    // Lấy wishes nếu được yêu cầu
    if ($type === 'wishes' || $type === 'both') {
        // Đếm tổng số wishes
        $countWishesStmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM wishes w
            WHERE w.username = ? $wishStatusCondition
        ");
        $countWishesStmt->execute([$username]);
        $wishesCount = $countWishesStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy danh sách wishes - theo database schema thực tế
        $wishesStmt = $pdo->prepare("
            SELECT 
                w.id, w.username, w.title, w.description, 
                w.scheduled_date, w.scheduled_time, w.completed as is_completed, 
                w.created_at, w.updated_at
            FROM wishes w
            WHERE w.username = ? $wishStatusCondition
            ORDER BY w.$sort $order
            LIMIT ? OFFSET ?
        ");
        $wishesStmt->execute([$username, $limit, $offset]);
        $wishes = $wishesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format dữ liệu wishes - theo database schema  
        foreach ($wishes as &$wish) {
            $wish['type'] = 'wish';
            $wish['id'] = (int)$wish['id'];
            $wish['is_completed'] = (bool)$wish['is_completed'];
            
            // Format dates
            if ($wish['scheduled_date']) {
                $wish['scheduled_date_formatted'] = date('d/m/Y', strtotime($wish['scheduled_date']));
            }
            if ($wish['scheduled_time']) {
                $wish['scheduled_time_formatted'] = date('H:i', strtotime($wish['scheduled_time']));
            }
            $wish['created_at_formatted'] = date('d/m/Y H:i', strtotime($wish['created_at']));
            if ($wish['updated_at']) {
                $wish['updated_at_formatted'] = date('d/m/Y H:i', strtotime($wish['updated_at']));
            }
        }

        $data['wishes'] = $wishes;
        $data['total_count'] += $wishesCount;
        $data['filtered_count'] += count($wishes);
    }

    // Nếu type là 'both', gộp và sắp xếp lại theo thời gian
    if ($type === 'both' && ($sort === 'created_at' || $sort === 'updated_at')) {
        $allItems = array_merge($data['tasks'], $data['wishes']);
        
        // Sắp xếp theo sort column
        usort($allItems, function($a, $b) use ($sort, $order) {
            $aValue = strtotime($a[$sort]);
            $bValue = strtotime($b[$sort]);
            
            if ($order === 'DESC') {
                return $bValue - $aValue;
            } else {
                return $aValue - $bValue;
            }
        });

        // Cắt theo limit và offset
        $allItems = array_slice($allItems, $offset, $limit);
        
        $data['combined'] = $allItems;
    }

    // Thêm thông tin phân trang
    $data['pagination'] = [
        'limit' => $limit,
        'offset' => $offset,
        'has_more' => ($offset + $limit) < $data['total_count']
    ];

    echo json_encode([
        'success' => true,
        'data' => $data,
        'message' => 'Dashboard items loaded successfully'
    ], JSON_UNESCAPED_UNICODE);

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
?>
