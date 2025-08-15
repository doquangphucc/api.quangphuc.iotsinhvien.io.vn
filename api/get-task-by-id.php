<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only GET method allowed']);
    exit;
}

try {
    $taskId = $_GET['id'] ?? '';
    $username = $_GET['username'] ?? '';

    if (empty($taskId)) {
        throw new Exception('Task ID is required');
    }

    // Xây dựng query với điều kiện username nếu có
    $query = "SELECT id, item_id, title, description, category, priority, 
                     scheduled_date, scheduled_time, status, user_id,
                     created_at, updated_at, completed_at
              FROM tasks 
              WHERE id = ?";
    
    $params = [$taskId];
    
    // Thêm điều kiện username nếu có
    if (!empty($username)) {
        $query .= " AND user_id = (SELECT id FROM tai_khoan WHERE user = ?)";
        $params[] = $username;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        throw new Exception('Task not found');
    }

    // Format dữ liệu
    $task['is_completed'] = (bool)$task['status'];
    $task['scheduled_date'] = $task['scheduled_date'] ?: null;
    $task['scheduled_time'] = $task['scheduled_time'] ?: null;

    echo json_encode([
        'success' => true,
        'data' => [$task] // Trả về array để tương thích với code hiện tại
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_type' => 'PDO_ERROR'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'GENERAL_ERROR'
    ]);
}
?>
