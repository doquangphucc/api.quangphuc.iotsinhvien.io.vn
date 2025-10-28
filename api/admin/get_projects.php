<?php
// Get all projects
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

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

