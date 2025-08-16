<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once 'connect.php';

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $id = $input['id'] ?? null;              // ID của item cần xóa
    $type = $input['type'] ?? null;          // 'task' | 'wish'

    if (!$id || !$type) {
        throw new Exception('Missing id or type');
    }

    if (!in_array($type, ['task', 'wish'])) {
        throw new Exception('Invalid type. Must be task or wish');
    }

    // Xác định bảng dựa trên type
    $table = ($type === 'task') ? 'tasks' : 'wishes';

    // Kiểm tra xem item có tồn tại không
    $checkStmt = $pdo->prepare("SELECT id FROM {$table} WHERE id = ?");
    $checkStmt->execute([$id]);
    
    if (!$checkStmt->fetch()) {
        throw new Exception('Item not found');
    }

    // Xóa item
    $deleteStmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
    $deleteStmt->execute([$id]);

    if ($deleteStmt->rowCount() === 0) {
        throw new Exception('Failed to delete item');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Item deleted successfully',
        'deleted_id' => $id,
        'type' => $type
    ]);

} catch (Exception $e) {
    error_log("Delete item error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Database error in delete-item.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
