<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Chỉ cho phép POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include database connection
require_once 'connect.php';

try {
    // Lấy dữ liệu JSON từ request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }
    
    $itemId = $input['id'] ?? '';
    $itemType = $input['type'] ?? ''; // 'task' hoặc 'wish'
    $status = $input['status'] ?? ''; // 'completed' hoặc 'pending'
    
    // Validate input
    if (empty($itemId) || empty($itemType) || empty($status)) {
        throw new Exception('Missing required fields: id, type, status');
    }
    
    if (!in_array($itemType, ['task', 'wish'])) {
        throw new Exception('Invalid type. Must be "task" or "wish"');
    }
    
    if (!in_array($status, ['completed', 'pending'])) {
        throw new Exception('Invalid status. Must be "completed" or "pending"');
    }
    
    // Chọn bảng phù hợp
    $tableName = $itemType === 'task' ? 'tasks' : 'wishes';
    $statusValue = $status === 'completed' ? 1 : 0;
    
    // Kiểm tra xem item có tồn tại không
    $checkStmt = $pdo->prepare("SELECT id FROM {$tableName} WHERE item_id = ?");
    $checkStmt->execute([$itemId]);
    
    if ($checkStmt->rowCount() === 0) {
        // Nếu chưa tồn tại, tạo record mới
        $insertStmt = $pdo->prepare("
            INSERT INTO {$tableName} (item_id, title, status, created_at, updated_at) 
            VALUES (?, 'Auto-generated item', ?, NOW(), NOW())
        ");
        $insertStmt->execute([$itemId, $statusValue]);
    } else {
        // Nếu đã tồn tại, cập nhật status
        $updateStmt = $pdo->prepare("
            UPDATE {$tableName} 
            SET status = ?, updated_at = NOW() 
            WHERE item_id = ?
        ");
        $updateStmt->execute([$statusValue, $itemId]);
    }
    
    // Trả về kết quả thành công
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'data' => [
            'id' => $itemId,
            'type' => $itemType,
            'status' => $status
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
