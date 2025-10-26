<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$sql = "SELECT * FROM package_categories ORDER BY display_order ASC, id ASC";
$result = $conn->query($sql);

$categories = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'badge_text' => $row['badge_text'],
            'badge_color' => $row['badge_color'],
            'display_order' => (int)$row['display_order'],
            'is_active' => (int)$row['is_active']
        ];
    }
}

echo json_encode([
    'success' => true,
    'categories' => $categories
]);

$conn->close();
?>

