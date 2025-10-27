<?php
// Get all active intro posts (public access)
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$sql = "SELECT * FROM intro_posts WHERE is_active = 1 ORDER BY display_order ASC, id ASC";
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

