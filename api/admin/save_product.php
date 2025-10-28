<?php
// Add or update product
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$required_action = $id > 0 ? 'edit' : 'create';

if (!hasPermission($conn, 'products', $required_action)) {
    echo json_encode(['success' => false, 'message' => "Bạn không có quyền {$required_action} sản phẩm"]);
    exit;
}
$category_id = intval($data['category_id'] ?? 0);
$title = $data['title'] ?? '';
$market_price = floatval($data['market_price'] ?? 0);
$category_price = !empty($data['category_price']) ? floatval($data['category_price']) : null;
$technical_description = $data['technical_description'] ?? '';
$image_url = $data['image_url'] ?? '';
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Tiêu đề sản phẩm không được để trống']);
    exit;
}

if ($category_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn danh mục']);
    exit;
}

if ($market_price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Giá thị trường phải lớn hơn 0']);
    exit;
}

if ($id > 0) {
    // Update existing product
    $stmt = $conn->prepare("UPDATE products SET category_id = ?, title = ?, market_price = ?, category_price = ?, technical_description = ?, image_url = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("isddssii", $category_id, $title, $market_price, $category_price, $technical_description, $image_url, $is_active, $id);
} else {
    // Insert new product
    $stmt = $conn->prepare("INSERT INTO products (category_id, title, market_price, category_price, technical_description, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdssi", $category_id, $title, $market_price, $category_price, $technical_description, $image_url, $is_active);
}

if ($stmt->execute()) {
    $product_id = $id > 0 ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Lưu sản phẩm thành công',
        'product_id' => $product_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

