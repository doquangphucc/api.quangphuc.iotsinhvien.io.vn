<?php
// Get all product categories
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../auth_helpers.php';

// Check admin access
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$sql = "SELECT * FROM product_categories ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode([
    'success' => true,
    'categories' => $categories
]);

$conn->close();
?>

