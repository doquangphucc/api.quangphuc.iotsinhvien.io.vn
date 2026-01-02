<?php
// Get all reward templates
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

if (!hasPermission($conn, 'rewards', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem phần thưởng']);
    exit;
}

$sql = "SELECT * FROM reward_templates ORDER BY reward_type ASC, id ASC";
$result = $conn->query($sql);

$templates = [];
while ($row = $result->fetch_assoc()) {
    $templates[] = $row;
}

echo json_encode([
    'success' => true,
    'templates' => $templates
]);

$conn->close();
?>

