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

// Check authentication - user must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check permission for contacts module
if (!hasPermission($conn, 'contacts', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa kênh liên hệ']);
    exit;
}

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }
    
    $sql = "DELETE FROM contact_channels WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Xóa kênh liên hệ thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy kênh liên hệ'
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi xóa kênh liên hệ: ' . $e->getMessage()
    ]);
}

$conn->close();

