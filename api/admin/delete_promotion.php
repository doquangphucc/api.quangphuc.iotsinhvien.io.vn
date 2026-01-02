<?php
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

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasPermission($conn, 'promotions', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa khuyến mãi']);
    exit;
}

$payload = json_decode(getRawRequestBody(), true);
$id = isset($payload['id']) ? (int)$payload['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID khuyến mãi không hợp lệ']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa khuyến mãi'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể xóa khuyến mãi: ' . $e->getMessage()
    ]);
}

$conn->close();

