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
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/audit_logger.php';

if (!hasPermission($conn, 'products', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa sản phẩm']);
    exit;
}

$data = json_decode(getRawRequestBody(), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Get product info before deleting (for audit log)
$stmt = $conn->prepare("SELECT id, title, category_id, image_url, market_price FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
    exit;
}

// Delete the product
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Audit log
    AuditLogger::logDelete('product', (string)$id, $product, "Xóa sản phẩm: " . $product['title']);
    
    // Delete image file if exists
    if (!empty($product['image_url'])) {
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

