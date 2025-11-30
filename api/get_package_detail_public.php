<?php
// Public API to get single package detail by ID
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_mysqli.php';

$package_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($package_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID gói không hợp lệ'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Get package with category info
    $sql = "SELECT p.*, pc.name as category_name, pc.logo_url as category_logo_url, 
                   pc.badge_text as category_badge_text, pc.badge_color as category_badge_color
            FROM packages p 
            JOIN package_categories pc ON p.category_id = pc.id 
            WHERE p.id = ? AND p.is_active = 1 AND pc.is_active = 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy gói sản phẩm'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $row = $result->fetch_assoc();
    $stmt->close();
    
    // Get items for this package with product info
    $itemsSql = "SELECT pi.*, p.title as product_title, p.image_url as product_image_url, 
                        p.market_price as product_market_price, p.category_price as product_category_price,
                        pc.name as product_category_name
                 FROM package_items pi
                 LEFT JOIN products p ON pi.product_id = p.id
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE pi.package_id = ? ORDER BY pi.display_order";
    $itemsStmt = $conn->prepare($itemsSql);
    $itemsStmt->bind_param("i", $package_id);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    $calculatedTotal = 0;
    
    while ($item = $itemsResult->fetch_assoc()) {
        $item_data = [
            'id' => (int)$item['id'],
            'product_id' => $item['product_id'] ? (int)$item['product_id'] : null,
            'name' => $item['item_name'] ?: $item['product_title'],
            'description' => $item['item_description'],
            'quantity' => (int)($item['quantity'] ?? 1),
            'price_type' => $item['price_type'] ?? 'market_price'
        ];
        
        // Add product info if product_id exists
        if ($item['product_id']) {
            $item_data['product'] = [
                'id' => (int)$item['product_id'],
                'title' => $item['product_title'],
                'image_url' => $item['product_image_url'],
                'market_price' => floatval($item['product_market_price'] ?? 0),
                'category_price' => $item['product_category_price'] ? floatval($item['product_category_price']) : null,
                'category_name' => $item['product_category_name']
            ];
            
            // Calculate unit price and total price
            $unit_price = $item_data['price_type'] === 'category_price' && $item_data['product']['category_price'] 
                ? $item_data['product']['category_price'] 
                : $item_data['product']['market_price'];
            $item_data['unit_price'] = $unit_price;
            $item_data['total_price'] = $unit_price * $item_data['quantity'];
            $calculatedTotal += $item_data['total_price'];
        }
        
        $items[] = $item_data;
    }
    $itemsStmt->close();
    
    // Parse highlights JSON
    $highlights = [];
    if (!empty($row['highlights'])) {
        $decoded_highlights = json_decode($row['highlights'], true);
        if (is_array($decoded_highlights)) {
            $highlights = $decoded_highlights;
        }
    }
    
    $package = [
        'id' => (int)$row['id'],
        'category_id' => (int)$row['category_id'],
        'category_name' => $row['category_name'],
        'category_logo_url' => $row['category_logo_url'],
        'category_badge_text' => $row['category_badge_text'],
        'category_badge_color' => $row['category_badge_color'],
        'name' => $row['name'],
        'description' => $row['description'],
        'price' => floatval($row['price']),
        'calculated_total' => $calculatedTotal, // Total calculated from items
        'badge_text' => $row['badge_text'],
        'badge_color' => $row['badge_color'],
        'highlights' => $highlights,
        'savings_per_month' => $row['savings_per_month'],
        'payback_period' => $row['payback_period'],
        'items' => $items
    ];
    
    echo json_encode([
        'success' => true,
        'package' => $package
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy chi tiết gói: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();

