<?php
// Update order status
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

if (!hasPermission($conn, 'orders', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật đơn hàng'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$order_id = intval($data['order_id'] ?? 0);
$new_status = $data['status'] ?? '';

// Valid statuses
$valid_statuses = ['pending', 'approved', 'processing', 'shipping', 'shipped', 'delivered', 'cancelled'];

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID đơn hàng không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Check if order exists
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Không tìm thấy đơn hàng');
    }
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    
    // Get status text for Vietnamese
    $status_texts = [
        'pending' => 'Chờ xử lý',
        'approved' => 'Đã duyệt',
        'processing' => 'Đang xử lý',
        'shipping' => 'Đang giao hàng',
        'shipped' => 'Đã giao hàng',
        'delivered' => 'Đã nhận hàng',
        'cancelled' => 'Đã hủy'
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật trạng thái thành công: ' . ($status_texts[$new_status] ?? $new_status)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
