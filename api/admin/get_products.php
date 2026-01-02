<?php
// Get all products with category info
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

if (!hasPermission($conn, 'products', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem sản phẩm']);
    exit;
}

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$sql = "SELECT p.*, pc.name as category_name 
        FROM products p 
        LEFT JOIN product_categories pc ON p.category_id = pc.id";

if ($category_id > 0) {
    $sql .= " WHERE p.category_id = " . $category_id;
}

$sql .= " ORDER BY p.category_id ASC, p.display_order ASC, p.id ASC";

$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    'success' => true,
    'products' => $products
]);

$conn->close();
?>

