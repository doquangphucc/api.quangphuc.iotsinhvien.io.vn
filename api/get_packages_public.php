<?php
// Public API to get packages with category info
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_mysqli.php';

try {
    // Get packages grouped by category
    $sql = "SELECT p.*, pc.name as category_name 
            FROM packages p 
            JOIN package_categories pc ON p.category_id = pc.id 
            WHERE p.is_active = 1 AND pc.is_active = 1 
            ORDER BY pc.display_order, p.display_order";
    
    $result = $conn->query($sql);
    $packages = [];
    
    while ($row = $result->fetch_assoc()) {
        $package_id = (int)$row['id'];
        
        // Get items for this package
        $itemsSql = "SELECT * FROM package_items WHERE package_id = ? ORDER BY display_order";
        $itemsStmt = $conn->prepare($itemsSql);
        $itemsStmt->bind_param("i", $package_id);
        $itemsStmt->execute();
        $itemsResult = $itemsStmt->get_result();
        
        $items = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $items[] = [
                'name' => $item['item_name'],
                'description' => $item['item_description']
            ];
        }
        $itemsStmt->close();
        
        $packages[] = [
            'id' => $package_id,
            'category_id' => (int)$row['category_id'],
            'category_name' => $row['category_name'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => floatval($row['price']),
            'savings_per_month' => $row['savings_per_month'],
            'payback_period' => $row['payback_period'],
            'badge_text' => $row['badge_text'],
            'badge_color' => $row['badge_color'],
            'items' => $items
        ];
    }
    
    echo json_encode([
        'success' => true,
        'packages' => $packages
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy gói sản phẩm: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
