<?php
// Delete product
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

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Get product image URL before deleting
$stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Delete the product
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete image file if exists
    if ($product && !empty($product['image_url'])) {
        // Handle both absolute and relative paths
        $image_path = $product['image_url'];
        
        // Try relative path first (if starts with assets/)
        if (strpos($image_path, 'assets/') === 0) {
            $image_file = __DIR__ . '/../../' . $image_path;
        } else {
            // Try absolute path
            $image_file = __DIR__ . '/../../' . ltrim($image_path, '/');
        }
        
        if (file_exists($image_file)) {
            unlink($image_file);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Xóa sản phẩm thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

