<?php
require_once 'connect.php';

requireAuth();

$userId = getCurrentUserId();
$orderId = $_GET['id'] ?? null;

if (!$orderId) {
    sendError('Thiếu ID đơn hàng');
    exit;
}

if (!is_numeric($orderId)) {
    sendError('ID đơn hàng không hợp lệ');
    exit;
}

try {
    $db = Database::getInstance();

    // Get order with items
    $order = $db->select('orders', ['id' => $orderId, 'user_id' => $userId], '*');

    if (empty($order)) {
        sendError('Không tìm thấy đơn hàng');
        exit;
    }

    $order = $order[0]; // Get first (and only) result

    // Get order items
    $items = $db->select('order_items', ['order_id' => $orderId], '*');
    
    // Get order vouchers
    $vouchers = $db->select('order_vouchers', ['order_id' => $orderId], '*');

    // Attach items and vouchers to order
    $order['items'] = $items;
    $order['vouchers'] = $vouchers;

    sendSuccess(['order' => $order], 'Lấy chi tiết đơn hàng thành công');

} catch (Exception $e) {
    error_log("Get Order Detail error: " . $e->getMessage());
    sendError('Lỗi hệ thống, không thể lấy chi tiết đơn hàng.');
}
?>
