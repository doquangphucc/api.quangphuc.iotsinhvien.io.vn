<?php
// Get all orders
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
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
    
    $orders[] = $row;
}

echo json_encode([
    'success' => true,
    'orders' => $orders
]);

$conn->close();
?>

