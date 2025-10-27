<?php
// Delete survey product configuration
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    exit;
}

$sql = "DELETE FROM survey_product_configs WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Đã xóa cấu hình khảo sát'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
