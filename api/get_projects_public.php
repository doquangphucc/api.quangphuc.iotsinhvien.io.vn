<?php
// Get all active projects (public access)
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$sql = "SELECT * FROM projects WHERE is_active = 1 ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$projects = [];
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

echo json_encode([
    'success' => true,
    'projects' => $projects
], JSON_UNESCAPED_UNICODE);

$conn->close();

