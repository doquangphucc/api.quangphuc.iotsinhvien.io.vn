<?php
/**
 * API: Xóa phần thưởng vòng quay admin
 */

require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
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

$payload = json_decode(getRawRequestBody(), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = isset($payload['id']) ? intval($payload['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID phần thưởng không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!hasPermission($conn, 'wheel', 'delete')) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn không có quyền xóa phần thưởng vòng quay'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $conn->prepare('DELETE FROM wheel_prizes WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy phần thưởng cần xóa'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa phần thưởng'
        ], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Error deleting wheel prize: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Không thể xóa phần thưởng'
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>

