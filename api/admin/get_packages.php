<?php
// Suppress warnings to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!hasPermission($conn, 'packages', 'view')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xem gói sản phẩm']);
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
        // Get package items with product info
        $package_id = $row['id'];
        $items_sql = "SELECT pi.*, p.title as product_title, p.image_url as product_image_url, 
                             p.market_price as product_market_price, p.category_price as product_category_price,
                             pc.name as product_category_name
                      FROM package_items pi
                      LEFT JOIN products p ON pi.product_id = p.id
                      LEFT JOIN product_categories pc ON p.category_id = pc.id
                      WHERE pi.package_id = ? ORDER BY pi.display_order ASC";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("i", $package_id);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $items = [];
        while ($item_row = $items_result->fetch_assoc()) {
            $item_data = [
                'id' => (int)$item_row['id'],
                'product_id' => $item_row['product_id'] ? (int)$item_row['product_id'] : null,
                'item_name' => $item_row['item_name'],
                'item_description' => $item_row['item_description'],
                'quantity' => (int)($item_row['quantity'] ?? 1),
                'price_type' => $item_row['price_type'] ?? 'market_price',
                'display_order' => (int)$item_row['display_order']
            ];
            
            // Add product info if product_id exists
            if ($item_row['product_id']) {
                $item_data['product'] = [
                    'id' => (int)$item_row['product_id'],
                    'title' => $item_row['product_title'],
                    'image_url' => $item_row['product_image_url'],
                    'market_price' => floatval($item_row['product_market_price'] ?? 0),
                    'category_price' => $item_row['product_category_price'] ? floatval($item_row['product_category_price']) : null,
                    'category_name' => $item_row['product_category_name']
                ];
                
                // Calculate unit price and total price
                $unit_price = $item_data['price_type'] === 'category_price' && $item_data['product']['category_price'] 
                    ? $item_data['product']['category_price'] 
                    : $item_data['product']['market_price'];
                $item_data['unit_price'] = $unit_price;
                $item_data['total_price'] = $unit_price * $item_data['quantity'];
            }
            
            $items[] = $item_data;
        }
        $items_stmt->close();
        
        // Parse highlights JSON
        $highlights = [];
        if (!empty($row['highlights'])) {
            $decoded_highlights = json_decode($row['highlights'], true);
            if (is_array($decoded_highlights)) {
                $highlights = $decoded_highlights;
            }
        }
        
        $packages[] = [
            'id' => (int)$row['id'],
            'category_id' => (int)$row['category_id'],
            'category_name' => $row['category_name'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => floatval($row['price']),
            'badge_text' => $row['badge_text'],
            'badge_color' => $row['badge_color'],
            'highlights' => $highlights,
            'savings_per_month' => $row['savings_per_month'], // Backward compatibility
            'payback_period' => $row['payback_period'], // Backward compatibility
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

