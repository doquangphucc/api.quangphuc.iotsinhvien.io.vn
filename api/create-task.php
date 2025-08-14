<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
    
    // Required fields
    $username = trim($input['username'] ?? '');
    $content = trim($input['content'] ?? '');
    
    // Optional fields với giá trị mặc định
    $description = trim($input['description'] ?? '');
    $category = $input['category'] ?? '';
    $priority = $input['priority'] ?? 'medium';
    $scheduledDate = $input['scheduled_date'] ?? null;
    $scheduledTime = $input['scheduled_time'] ?? null;
    
    // Validation
    if (empty($username)) {
        throw new Exception('Username is required');
    }
    
    if (empty($content)) {
        throw new Exception('Content is required');
    }
    
    // Validate priority
    $validPriorities = ['low', 'medium', 'high'];
    if (!in_array($priority, $validPriorities)) {
        $priority = 'medium';
    }
    
    // Validate category
    $validCategories = ['work', 'study', 'personal', 'health', 'hobby', 'family', 'other', ''];
    if (!in_array($category, $validCategories)) {
        $category = '';
    }
    
    // Validate date format
    if ($scheduledDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $scheduledDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }
    
    // Validate time format
    if ($scheduledTime && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $scheduledTime)) {
        throw new Exception('Invalid time format. Use HH:MM or HH:MM:SS');
    }
    
    // Generate unique item_id
    $itemId = 'task_' . date('Ymd') . '_' . uniqid();
    
    // Get user_id from username
    $userQuery = "SELECT id FROM tai_khoan WHERE user = ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$username]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    $userId = $user['id'];
    
    // Insert task
    $query = "INSERT INTO tasks 
              (item_id, title, description, category, priority, user_id, scheduled_date, scheduled_time, is_completed, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $itemId,
        $content,
        $description ?: null,
        $category ?: null,
        $priority,
        $userId,
        $scheduledDate,
        $scheduledTime
    ]);
    
    if ($result) {
        $taskId = $pdo->lastInsertId();
        
        // Return created task info
        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'id' => $taskId,
                'item_id' => $itemId,
                'title' => $content,
                'description' => $description ?: null,
                'category' => $category ?: null,
                'priority' => $priority,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => $scheduledTime,
                'is_completed' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Failed to create task');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'TASK_CREATE_ERROR'
    ]);
}
?>
