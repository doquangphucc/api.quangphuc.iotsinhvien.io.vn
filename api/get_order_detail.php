<?php
require_once 'connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

requireAuth();

$userId = getCurrentUserId();
$orderId = $_GET['id'] ?? null;

error_log("Get Order Detail - User ID: " . $userId . ", Order ID: " . $orderId);

if (!$orderId) {
    error_log("Get Order Detail - Missing order ID");
    sendError('Thiếu ID đơn hàng');
    exit;
}

if (!is_numeric($orderId)) {
    error_log("Get Order Detail - Invalid order ID: " . $orderId);
    sendError('ID đơn hàng không hợp lệ');
    exit;
}

try {
    $db = Database::getInstance();

    // Check if user is admin
    $isAdmin = is_admin();
    
    // If admin, allow viewing all orders. Otherwise, only user's own orders
    if ($isAdmin) {
        $order = $db->select('orders', ['id' => $orderId], '*');
        error_log("Get Order Detail - Admin viewing order: {$orderId}");
    } else {
        $order = $db->select('orders', ['id' => $orderId, 'user_id' => $userId], '*');
        error_log("Get Order Detail - User {$userId} viewing order: {$orderId}");
    }
    
    error_log("Get Order Detail - Query result count: " . count($order));

    if (empty($order)) {
        error_log("Get Order Detail - Order not found for order_id: {$orderId}");
        sendError('Không tìm thấy đơn hàng hoặc đơn hàng không thuộc về bạn');
        exit;
    }

    $order = $order[0]; // Get first (and only) result
    error_log("Get Order Detail - Order found: " . json_encode($order));

    // Get order items
    $items = $db->select('order_items', ['order_id' => $orderId], '*');
    error_log("Get Order Detail - Items count: " . count($items));
    
    // Get order vouchers
    $vouchers = $db->select('order_vouchers', ['order_id' => $orderId], '*');
    error_log("Get Order Detail - Vouchers count: " . count($vouchers));

    // Attach items and vouchers to order
    $order['items'] = $items;
    $order['vouchers'] = $vouchers;

    sendSuccess(['order' => $order], 'Lấy chi tiết đơn hàng thành công');

} catch (Exception $e) {
    error_log("Get Order Detail error: " . $e->getMessage());
    error_log("Get Order Detail stack trace: " . $e->getTraceAsString());
    sendError('Lỗi hệ thống, không thể lấy chi tiết đơn hàng: ' . $e->getMessage());
}
?>
