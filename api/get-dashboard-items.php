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
        $statusCondition = ' AND is_completed = 1';
    } elseif ($status === 'pending') {
        $statusCondition = ' AND is_completed = 0';
    }

    // Validate sort column
    $validSortColumns = ['created_at', 'title', 'due_date', 'priority'];
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
            FROM tasks 
            WHERE username = ? $statusCondition
        ");
        $countTasksStmt->execute([$username]);
        $tasksCount = $countTasksStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy danh sách tasks
        $tasksStmt = $pdo->prepare("
            SELECT 
                id, title, description, priority, category, 
                due_date, is_completed, created_at, updated_at
            FROM tasks 
            WHERE username = ? $statusCondition
            ORDER BY $sort $order
            LIMIT ? OFFSET ?
        ");
        $tasksStmt->execute([$username, $limit, $offset]);
        $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format dữ liệu tasks
        foreach ($tasks as &$task) {
            $task['type'] = 'task';
            $task['id'] = (int)$task['id'];
            $task['is_completed'] = (bool)$task['is_completed'];
            
            // Format dates
            if ($task['due_date']) {
                $task['due_date'] = date('d/m/Y', strtotime($task['due_date']));
            }
            $task['created_at'] = date('d/m/Y H:i', strtotime($task['created_at']));
            if ($task['updated_at']) {
                $task['updated_at'] = date('d/m/Y H:i', strtotime($task['updated_at']));
            }
        }

        $data['tasks'] = $tasks;
        $data['total_count'] += $tasksCount;
        $data['filtered_count'] += count($tasks);
    }

    // Lấy wishes nếu được yêu cầu
    if ($type === 'wishes' || $type === 'both') {
        // Đếm tổng số wishes
        $countWishesStmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM wishes 
            WHERE username = ? $statusCondition
        ");
        $countWishesStmt->execute([$username]);
        $wishesCount = $countWishesStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy danh sách wishes
        $wishesStmt = $pdo->prepare("
            SELECT 
                id, title, description, price, category, store_location,
                purchase_link, priority, is_completed, created_at, updated_at
            FROM wishes 
            WHERE username = ? $statusCondition
            ORDER BY $sort $order
            LIMIT ? OFFSET ?
        ");
        $wishesStmt->execute([$username, $limit, $offset]);
        $wishes = $wishesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format dữ liệu wishes
        foreach ($wishes as &$wish) {
            $wish['type'] = 'wish';
            $wish['id'] = (int)$wish['id'];
            $wish['is_completed'] = (bool)$wish['is_completed'];
            $wish['price'] = $wish['price'] ? (float)$wish['price'] : null;
            
            // Format dates
            $wish['created_at'] = date('d/m/Y H:i', strtotime($wish['created_at']));
            if ($wish['updated_at']) {
                $wish['updated_at'] = date('d/m/Y H:i', strtotime($wish['updated_at']));
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
