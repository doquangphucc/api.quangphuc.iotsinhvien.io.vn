<?php
// Admin API to delete a product image
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Apply admin security (WAF, rate limiting)
applySecurityMiddleware([
    'csrf' => false,  // TODO: Enable after frontend update
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 30,
    'rate_limit_window' => 60,
    'audit' => true
]);

if (!hasPermission($conn, 'products', 'delete')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa ảnh sản phẩm']);
    exit;
}

// Get JSON input
$input = json_decode(getRawRequestBody(), true);

if (!isset($input['image_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin image_id']);
    exit;
}

$image_id = intval($input['image_id']);

if ($image_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID ảnh không hợp lệ']);
    exit;
}

try {
    // Get image info before deletion (to optionally delete file)
    $getStmt = $conn->prepare("SELECT image_url FROM product_images WHERE id = ?");
    $getStmt->bind_param("i", $image_id);
    $getStmt->execute();
    $getResult = $getStmt->get_result();
    
    if ($getResult->num_rows === 0) {
        $getStmt->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy ảnh']);
        exit;
    }
    
    $imageRow = $getResult->fetch_assoc();
    $getStmt->close();
    
    // Delete from database
    $deleteStmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
    $deleteStmt->bind_param("i", $image_id);
    
    if ($deleteStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Xóa ảnh thành công'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Lỗi khi xóa ảnh khỏi database');
    }
    
    $deleteStmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi xóa ảnh: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

