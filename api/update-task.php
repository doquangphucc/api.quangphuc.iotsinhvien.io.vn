<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only PUT/POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $taskId = $input['id'] ?? '';
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

    if (empty($taskId) || empty($title)) {
        throw new Exception('Task ID and title are required');
    }

    // Check if task exists
    $checkQuery = "SELECT id FROM tasks WHERE id = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$taskId]);
    
    if (!$checkStmt->fetch()) {
        throw new Exception('Task not found');
    }

    // Update task
    $query = "UPDATE tasks 
              SET title = ?, description = ?, category = ?, priority = ?, 
                  scheduled_date = ?, scheduled_time = ?, updated_at = NOW()
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $title, 
        $description, 
        $category, 
        $priority, 
        $scheduledDate, 
        $scheduledTime,
        $taskId
    ]);

    if ($result) {
        // Lấy thông tin task sau khi update
        $getTaskQuery = "SELECT * FROM tasks WHERE id = ?";
        $getTaskStmt = $pdo->prepare($getTaskQuery);
        $getTaskStmt->execute([$taskId]);
        $updatedTask = $getTaskStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $updatedTask
        ]);
    } else {
        throw new Exception('Failed to update task');
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
