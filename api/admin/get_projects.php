<?php
// Get all projects
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
if (!hasPermission($conn, 'projects', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem dự án']);
    exit;
}

$sql = "SELECT * FROM projects ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

echo json_encode([
    'success' => true,
    'projects' => $projects
]);

$conn->close();

