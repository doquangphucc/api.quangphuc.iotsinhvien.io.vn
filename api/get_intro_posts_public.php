<?php
// Get all active intro posts (public access)
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$sql = "SELECT * FROM intro_posts WHERE is_active = 1 ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$posts = [];
while ($row = $result->fetch_assoc()) {
    // Parse media_gallery JSON
    if (!empty($row['media_gallery'])) {
        $row['media_gallery'] = json_decode($row['media_gallery'], true) ?? [];
    } else {
        $row['media_gallery'] = [];
    }
    $posts[] = $row;
}

echo json_encode([
    'success' => true,
    'posts' => $posts
]);

$conn->close();

