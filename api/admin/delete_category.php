<?php
// Delete product category
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

// Check delete permission
if (!hasPermission($conn, 'categories', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa danh mục']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Check if category has products
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Không thể xóa danh mục đang có sản phẩm']);
    exit;
}

// Get category logo URL before deleting
$stmt = $conn->prepare("SELECT logo_url FROM product_categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM product_categories WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete logo file if exists
    if ($category && !empty($category['logo_url'])) {
        $logo_path = $category['logo_url'];
        
        // Try relative path first (if starts with assets/)
        if (strpos($logo_path, 'assets/') === 0) {
            $logo_file = __DIR__ . '/../../' . $logo_path;
        } else {
            // Try absolute path
            $logo_file = __DIR__ . '/../../' . ltrim($logo_path, '/');
        }
        
        if (file_exists($logo_file)) {
            unlink($logo_file);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Xóa danh mục thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

