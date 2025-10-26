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

// Get packages with category info and items
$sql = "SELECT p.*, pc.name as category_name 
        FROM packages p
        LEFT JOIN package_categories pc ON p.category_id = pc.id
        ORDER BY p.category_id, p.display_order ASC, p.id ASC";

$result = $conn->query($sql);

$packages = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Get package items
        $package_id = $row['id'];
        $items_sql = "SELECT * FROM package_items WHERE package_id = ? ORDER BY display_order ASC";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("i", $package_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $items = [];
        while ($item_row = $items_result->fetch_assoc()) {
            $items[] = [
                'id' => (int)$item_row['id'],
                'item_name' => $item_row['item_name'],
                'item_description' => $item_row['item_description'],
                'display_order' => (int)$item_row['display_order']
            ];
        }
        $items_stmt->close();
        
        $packages[] = [
            'id' => (int)$row['id'],
            'category_id' => (int)$row['category_id'],
            'category_name' => $row['category_name'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => floatval($row['price']),
            'badge_text' => $row['badge_text'],
            'badge_color' => $row['badge_color'],
            'savings_per_month' => $row['savings_per_month'],
            'payback_period' => $row['payback_period'],
            'display_order' => (int)$row['display_order'],
            'is_active' => (int)$row['is_active'],
            'items' => $items
        ];
    }
}

echo json_encode([
    'success' => true,
    'packages' => $packages
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>

