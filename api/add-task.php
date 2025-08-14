<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? null;
    $category = $input['category'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    $datetime = $input['datetime'] ?? null;
    $scheduledDate = null;
    $scheduledTime = null;

    // Parse datetime if provided
    if ($datetime) {
        $dt = new DateTime($datetime);
        $scheduledDate = $dt->format('Y-m-d');
        $scheduledTime = $dt->format('H:i:s');
    }

    if (empty($username) || empty($title)) {
        throw new Exception('Username and title are required');
    }

    // Generate unique item_id
    $itemId = 'task_' . uniqid() . '_' . time();
    
    // Get user_id from username
    $userQuery = "SELECT id FROM tai_khoan WHERE user = ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$username]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userId = $user ? $user['id'] : null;

    $query = "INSERT INTO tasks 
              (item_id, title, description, category, priority, user_id, scheduled_date, scheduled_time, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $itemId, 
        $title, 
        $description, 
        $category, 
        $priority, 
        $userId, 
        $scheduledDate, 
        $scheduledTime
    ]);

    if ($result) {
        $taskId = $pdo->lastInsertId();
        
        // Lấy thông tin task vừa tạo
        $getTaskQuery = "SELECT * FROM tasks WHERE id = ?";
        $getTaskStmt = $pdo->prepare($getTaskQuery);
        $getTaskStmt->execute([$taskId]);
        $newTask = $getTaskStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Task added successfully',
            'data' => $newTask
        ]);
    } else {
        throw new Exception('Failed to add task');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'input' => $input ?? null,
            'error_details' => $e->getTraceAsString()
        ]
    ]);
}
?>