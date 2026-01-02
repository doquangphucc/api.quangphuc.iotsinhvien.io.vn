<?php
// Update order status
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/audit_logger.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Apply admin security (WAF, rate limiting)
applySecurityMiddleware([
    'csrf' => false,  // TODO: Enable after frontend update
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 30,
    'rate_limit_window' => 60,
    'audit' => true
]);

if (!hasPermission($conn, 'orders', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền cập nhật đơn hàng'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(getRawRequestBody(), true);
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
    // Check if order exists and get current status
    $stmt = $conn->prepare("SELECT id, order_status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Không tìm thấy đơn hàng');
    }
    
    $order = $result->fetch_assoc();
    $old_status = $order['order_status'];
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    
    // Audit log
    AuditLogger::logUpdate('order', (string)$order_id, 
        ['status' => $old_status], 
        ['status' => $new_status], 
        "Cập nhật trạng thái đơn hàng #$order_id: $old_status → $new_status"
    );
    
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
