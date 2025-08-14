<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Chỉ cho phép GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include database connection
require_once 'connect.php';

try {
    $itemIds = $_GET['ids'] ?? '';
    $itemType = $_GET['type'] ?? ''; // 'task' hoặc 'wish'
    
    if (empty($itemIds) || empty($itemType)) {
        throw new Exception('Missing required parameters: ids, type');
    }
    
    if (!in_array($itemType, ['task', 'wish'])) {
        throw new Exception('Invalid type. Must be "task" or "wish"');
    }
    
    // Chuyển chuỗi ids thành array
    $idsArray = explode(',', $itemIds);
    $idsArray = array_map('trim', $idsArray);
    $idsArray = array_filter($idsArray); // Remove empty values
    
    if (empty($idsArray)) {
        throw new Exception('No valid IDs provided');
    }
    
    // Chọn bảng phù hợp
    $tableName = $itemType === 'task' ? 'tasks' : 'wishes';
    
    // Tạo placeholders cho prepared statement
    $placeholders = str_repeat('?,', count($idsArray) - 1) . '?';
    
    // Lấy trạng thái của các items
    $stmt = $pdo->prepare("SELECT item_id, status FROM {$tableName} WHERE item_id IN ({$placeholders})");
    $stmt->execute($idsArray);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Chuyển đổi kết quả thành format dễ sử dụng
    $statuses = [];
    foreach ($results as $row) {
        $statuses[$row['item_id']] = $row['status'] == 1 ? 'completed' : 'pending';
    }
    
    // Các items không có trong DB sẽ mặc định là 'pending'
    foreach ($idsArray as $id) {
        if (!isset($statuses[$id])) {
            $statuses[$id] = 'pending';
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $statuses
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
