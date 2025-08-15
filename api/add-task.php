<?php
// Đặt timezone cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug log
    error_log('ADD TASK INPUT: ' . json_encode($input));
    
    $username = $input['username'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? null;
    $category = $input['category'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    
    // Handle both old datetime format and new separate date/time
    $scheduledDate = $input['scheduled_date'] ?? null;
    $scheduledTime = $input['scheduled_time'] ?? null;
    
    // Debug log
    error_log('SCHEDULED DATA: date=' . ($scheduledDate ?: 'NULL') . ', time=' . ($scheduledTime ?: 'NULL'));
    
    // Legacy datetime support
    $datetime = $input['datetime'] ?? null;
    if ($datetime && !$scheduledDate) {
        $dt = new DateTime($datetime);
        $scheduledDate = $dt->format('Y-m-d');
        $scheduledTime = $dt->format('H:i:s');
        error_log('LEGACY DATETIME CONVERTED: date=' . $scheduledDate . ', time=' . $scheduledTime);
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
              (item_id, title, description, category, priority, user_id, scheduled_date, scheduled_time, status, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())";
    
    // Debug log before execute
    error_log('FINAL BIND VALUES: ' . json_encode([
        'item_id' => $itemId,
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'priority' => $priority,
        'user_id' => $userId,
        'scheduled_date' => $scheduledDate,
        'scheduled_time' => $scheduledTime
    ]));
    
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
            'data' => $newTask,
            'debug' => [
                'input_scheduled_date' => $input['scheduled_date'] ?? 'NOT_SET',
                'input_scheduled_time' => $input['scheduled_time'] ?? 'NOT_SET',
                'processed_scheduled_date' => $scheduledDate,
                'processed_scheduled_time' => $scheduledTime,
                'full_input' => $input
            ]
        ]);
    } else {
        throw new Exception('Failed to add task');
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_type' => 'PDO_ERROR',
        'debug' => [
            'input' => $input ?? null,
            'sql_error' => $e->errorInfo ?? null
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'GENERAL_ERROR',
        'debug' => [
            'input' => $input ?? null,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>