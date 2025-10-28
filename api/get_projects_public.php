<?php
// Get all active projects (public access)
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$sql = "SELECT * FROM projects WHERE is_active = 1 ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$projects = [];
while ($row = $result->fetch_assoc()) {
    // Parse media_gallery JSON
    if (!empty($row['media_gallery'])) {
        $row['media_gallery'] = json_decode($row['media_gallery'], true) ?? [];
    } else {
        $row['media_gallery'] = [];
    }
    $projects[] = $row;
}

echo json_encode([
    'success' => true,
    'projects' => $projects
], JSON_UNESCAPED_UNICODE);

$conn->close();

