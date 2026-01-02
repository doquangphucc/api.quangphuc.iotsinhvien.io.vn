<?php
// Delete survey product configuration
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Apply admin security (WAF, rate limiting)
applySecurityMiddleware([
    'csrf' => false,  // TODO: Enable after frontend update
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 30,
    'rate_limit_window' => 60,
    'audit' => true
]);

if (!hasPermission($conn, 'survey', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa khảo sát']);
    exit;
}

$data = json_decode(getRawRequestBody(), true);

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
