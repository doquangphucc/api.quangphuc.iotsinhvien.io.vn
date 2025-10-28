<?php
// Get all product categories
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check view permission
if (!hasPermission($conn, 'categories', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem danh mục']);
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