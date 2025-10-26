<?php
// Add or update product category
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
$name = $data['name'] ?? '';
$logo_url = $data['logo_url'] ?? '';
$description = $data['description'] ?? '';
$display_order = intval($data['display_order'] ?? 0);
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
    exit;
}

if ($id > 0) {
    // Update existing category
    $stmt = $conn->prepare("UPDATE product_categories SET name = ?, logo_url = ?, description = ?, display_order = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssiii", $name, $logo_url, $description, $display_order, $is_active, $id);
} else {
    // Insert new category
    $stmt = $conn->prepare("INSERT INTO product_categories (name, logo_url, description, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $name, $logo_url, $description, $display_order, $is_active);
}

if ($stmt->execute()) {
    $category_id = $id > 0 ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Lưu danh mục thành công',
        'category_id' => $category_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

