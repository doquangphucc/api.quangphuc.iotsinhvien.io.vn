<?php
// Get all active dich_vu (public access)
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$sql = "SELECT * FROM dich_vu WHERE is_active = 1 ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

echo json_encode([
    'success' => true,
    'services' => $services
]);

$conn->close();

