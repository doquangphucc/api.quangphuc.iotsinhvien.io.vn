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

session_start();
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$category_id = intval($data['category_id'] ?? 0);
$name = $data['name'] ?? '';
$brand = $data['brand'] ?? '';
$model = $data['model'] ?? '';
$price = floatval($data['price'] ?? 0);
$price_installation = !empty($data['price_installation']) ? floatval($data['price_installation']) : null;
$description = $data['description'] ?? '';
$specifications = $data['specifications'] ?? '';
$image_url = $data['image_url'] ?? '';
$is_available = isset($data['is_available']) ? ($data['is_available'] ? 1 : 0) : 1;

if (empty($name) || $category_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Tên sản phẩm và danh mục không được để trống']);
    exit;
}

if ($id > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, brand = ?, model = ?, price = ?, price_installation = ?, description = ?, specifications = ?, image_url = ?, is_available = ? WHERE id = ?");
    $stmt->bind_param("isssddssiii", $category_id, $name, $brand, $model, $price, $price_installation, $description, $specifications, $image_url, $is_available, $id);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO products (category_id, name, brand, model, price, price_installation, description, specifications, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssddsssi", $category_id, $name, $brand, $model, $price, $price_installation, $description, $specifications, $image_url, $is_available);
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

