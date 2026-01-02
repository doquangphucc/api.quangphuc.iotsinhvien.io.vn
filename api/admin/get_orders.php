<?php
// Get all orders
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Apply admin security (WAF, rate limiting, no CSRF for GET)
applySecurityMiddleware([
    'csrf' => false,
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 60,
    'rate_limit_window' => 60
]);

if (!hasPermission($conn, 'orders', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem đơn hàng']);
    exit;
}

$status = $_GET['status'] ?? '';

$sql = "SELECT o.*, u.username, u.full_name as user_full_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id";

if (!empty($status)) {
    $sql .= " WHERE o.order_status = '" . $conn->real_escape_string($status) . "'";
}

$sql .= " ORDER BY o.created_at DESC";

$result = $conn->query($sql);

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Get order items
    $order_id = $row['id'];
    $items_sql = "SELECT * FROM order_items WHERE order_id = $order_id";
    $items_result = $conn->query($items_sql);
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    $row['items'] = $items;
    
    // Get order vouchers
    $vouchers_sql = "SELECT * FROM order_vouchers WHERE order_id = $order_id";
    $vouchers_result = $conn->query($vouchers_sql);
    $vouchers = [];
    while ($voucher = $vouchers_result->fetch_assoc()) {
        $vouchers[] = $voucher;
    }
    $row['vouchers'] = $vouchers;
    
    $orders[] = $row;
}

echo json_encode([
    'success' => true,
    'orders' => $orders
]);

$conn->close();
?>

