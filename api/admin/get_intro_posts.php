<?php
// Get all intro posts
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check admin access
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
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

