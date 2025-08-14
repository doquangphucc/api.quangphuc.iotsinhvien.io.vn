<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';
require_once 'connect.php';

try {
    // Get parameters
    $username = $_GET['username'] ?? '';
    $status = $_GET['status'] ?? 'all'; // all, completed, pending
    $category = $_GET['category'] ?? '';
    $priority = $_GET['priority'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 50), 100); // Max 100 items
    $offset = max(intval($_GET['offset'] ?? 0), 0);
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }
    
    // Get user_id
    $userQuery = "SELECT id FROM tai_khoan WHERE user = ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$username]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    $userId = $user['id'];
    
    // Build query
    $whereClause = ['user_id = ?'];
    $params = [$userId];
    
    // Filter by status
    if ($status === 'completed') {
        $whereClause[] = 'is_completed = 1';
    } elseif ($status === 'pending') {
        $whereClause[] = 'is_completed = 0';
    }
    
    // Filter by category
    if (!empty($category)) {
        $whereClause[] = 'category = ?';
        $params[] = $category;
    }
    
    // Filter by priority
    if (!empty($priority)) {
        $whereClause[] = 'priority = ?';
        $params[] = $priority;
    }
    
    // Main query
    $query = "SELECT 
                id, item_id, title, description, category, priority, 
                scheduled_date, scheduled_time, is_completed, 
                created_at, updated_at
              FROM tasks 
              WHERE " . implode(' AND ', $whereClause) . "
              ORDER BY 
                CASE priority 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                END,
                scheduled_date ASC,
                created_at DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count total
    $countQuery = "SELECT COUNT(*) as total FROM tasks WHERE " . implode(' AND ', $whereClause);
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Format data
    foreach ($tasks as &$task) {
        $task['is_completed'] = (bool)$task['is_completed'];
        
        // Add priority icon
        $priorityIcons = ['low' => '🟢', 'medium' => '🟡', 'high' => '🔴'];
        $task['priority_icon'] = $priorityIcons[$task['priority']] ?? '🟡';
        
        // Format dates
        if ($task['scheduled_date']) {
            $task['formatted_date'] = date('d/m/Y', strtotime($task['scheduled_date']));
        }
        
        if ($task['scheduled_time']) {
            $task['formatted_time'] = date('H:i', strtotime($task['scheduled_time']));
        }
        
        $task['formatted_created'] = date('d/m/Y H:i', strtotime($task['created_at']));
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'tasks' => $tasks,
            'pagination' => [
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ],
            'filters' => [
                'status' => $status,
                'category' => $category,
                'priority' => $priority
            ]
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'TASKS_FETCH_ERROR'
    ]);
}
?>
