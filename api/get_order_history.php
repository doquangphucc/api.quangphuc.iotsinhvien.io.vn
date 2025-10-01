<?php
require_once 'connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    sendError('Bạn cần đăng nhập để xem lịch sử đơn hàng.', 401);
}

$userId = (int)$_SESSION['user_id'];

try {
    $db = Database::getInstance();

    // 1. Get all orders for the user
    $orders = $db->select('orders', ['user_id' => $userId], '*', 'ORDER BY created_at DESC');

    if (empty($orders)) {
        sendSuccess(['orders' => []]);
        exit;
    }

    // 2. Get all items for those orders
    $orderIds = array_map(function($order) { return $order['id']; }, $orders);
    
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $sql = "SELECT * FROM order_items WHERE order_id IN ({$placeholders})";
    
    $stmt = $db->query($sql, $orderIds);
    $items = $stmt->fetchAll();

    // 3. Group items by order_id
    $itemsByOrderId = [];
    foreach ($items as $item) {
        $itemsByOrderId[$item['order_id']][] = $item;
    }

    // 4. Attach items to their respective orders
    foreach ($orders as &$order) {
        $order['items'] = $itemsByOrderId[$order['id']] ?? [];
    }
    unset($order); // Unset reference

    sendSuccess(['orders' => $orders]);

} catch (Exception $e) {
    error_log("Get Order History error: " . $e->getMessage());
    sendError('Lỗi hệ thống, không thể lấy lịch sử đơn hàng.', 500);
}
?>