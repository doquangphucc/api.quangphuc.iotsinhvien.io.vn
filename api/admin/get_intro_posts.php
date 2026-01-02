<?php
// Get all intro posts
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/security_middleware.php';

// Apply admin security (WAF, rate limiting, no CSRF for GET)
applySecurityMiddleware([
    'csrf' => false,
    'waf' => true,
    'rate_limit' => true,
    'rate_limit_requests' => 60,
    'rate_limit_window' => 60
]);

// Check admin access
if (!hasPermission($conn, 'intro-posts', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem bài giới thiệu']);
    exit;
}

$sql = "SELECT * FROM intro_posts ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

echo json_encode([
    'success' => true,
    'posts' => $posts
]);

$conn->close();

