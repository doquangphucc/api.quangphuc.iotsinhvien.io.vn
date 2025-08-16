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
    
    $username = $input['username'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? null;
    $category = $input['category'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    
    // Handle both old datetime format and new separate date/time
    $scheduledDate = $input['scheduled_date'] ?? null;
    $scheduledTime = $input['scheduled_time'] ?? null;
    
    // Legacy datetime support
    $datetime = $input['datetime'] ?? null;
    if ($datetime && !$scheduledDate) {
        $dt = new DateTime($datetime);
        $scheduledDate = $dt->format('Y-m-d');
        $scheduledTime = $dt->format('H:i:s');
    }

    if (empty($username) || empty($title)) {
        throw new Exception('Username and title are required');
    }

    // Generate unique item_id (không cần thiết cho database mới nhưng giữ lại cho tương thích)
    $itemId = 'task_' . uniqid() . '_' . time();
    
    // Database mới dùng username trực tiếp, không cần user_id
    $query = "INSERT INTO tasks 
              (username, title, description, scheduled_date, scheduled_time, completed) 
              VALUES (?, ?, ?, ?, ?, 0)";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $username,  // Dùng username trực tiếp
        $title, 
        $description, 
        $scheduledDate,  // Sửa từ $scheduled_date
        $scheduledTime   // Sửa từ $scheduled_time 
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