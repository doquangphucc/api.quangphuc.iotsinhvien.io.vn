<?php
// Get all reward templates
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
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

