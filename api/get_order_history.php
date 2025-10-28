<?php
require_once 'connect.php';

requireAuth();



$userId = getCurrentUserId();

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

    // 3. Get all vouchers for those orders
    $voucherSql = "SELECT * FROM order_vouchers WHERE order_id IN ({$placeholders})";
    $voucherStmt = $db->query($voucherSql, $orderIds);
    $vouchers = $voucherStmt->fetchAll();
    
    // 4. Group items and vouchers by order_id
    $itemsByOrderId = [];
    foreach ($items as $item) {
        $itemsByOrderId[$item['order_id']][] = $item;
    }
    
    $vouchersByOrderId = [];
    foreach ($vouchers as $voucher) {
        $vouchersByOrderId[$voucher['order_id']][] = $voucher;
    }

    // 5. Attach items and vouchers to their respective orders
    foreach ($orders as &$order) {
        $order['items'] = $itemsByOrderId[$order['id']] ?? [];
        $order['vouchers'] = $vouchersByOrderId[$order['id']] ?? [];
    }
    unset($order); // Unset reference

    sendSuccess(['orders' => $orders]);

} catch (Exception $e) {
    error_log("Get Order History error: " . $e->getMessage());
    sendError('Lá»—i há»‡ thá»‘ng, khÃ´ng thá»ƒ láº¥y lá»‹ch sá»­ Ä‘Æ¡n hÃ ng.', 500);
}
?>
