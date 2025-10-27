<?php
// Public API to get survey products for survey page
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
    $sql = "SELECT 
                p.id,
                p.title,
                p.market_price,
                p.category_price,
                p.technical_description,
                p.image_url,
                spc.survey_category,
                spc.phase_type,
                spc.price_type,
                spc.is_active,
                spc.display_order
            FROM products p
            INNER JOIN survey_product_configs spc ON p.id = spc.product_id
            WHERE p.is_active = 1 
              AND spc.is_active = 1
            ORDER BY spc.survey_category ASC, spc.phase_type ASC, spc.display_order ASC";
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        // Determine which price to use
        $price = ($row['price_type'] === 'market_price') ? $row['market_price'] : $row['category_price'];
        
        // Fix image URL for frontend (from api/ folder needs ../)
        $imageUrl = $row['image_url'];
        // Paths in database are like: assets/img/products/xxx.png
        // Need to prepend ../ for HTML files to work
        if ($imageUrl && !str_starts_with($imageUrl, 'http') && !str_starts_with($imageUrl, '../')) {
            // Don't add ../ here - let frontend handle it or use absolute path
            // Actually, database already has correct paths for html/ folder
        }
        
        $products[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'price' => floatval($price),
            'market_price' => floatval($row['market_price']),
            'category_price' => floatval($row['category_price']),
            'technical_description' => $row['technical_description'],
            'image_url' => $imageUrl,
            'survey_category' => $row['survey_category'],
            'phase_type' => $row['phase_type'],
            'display_order' => (int)$row['display_order']
        ];
    }
    
    // Group products by category
    $grouped = [
        'solar_panel' => [],
        'inverter' => [
            '1_phase' => [],
            '3_phase' => []
        ],
        'battery' => [],
        'electrical_cabinet' => [
            '1_phase' => [],
            '3_phase' => []
        ],
        'accessory' => []
    ];
    
    foreach ($products as $product) {
        $cat = $product['survey_category'];
        $phase = $product['phase_type'];
        
        if ($cat === 'inverter' || $cat === 'electrical_cabinet') {
            if ($phase === '1_phase') {
                $grouped[$cat]['1_phase'][] = $product;
            } elseif ($phase === '3_phase') {
                $grouped[$cat]['3_phase'][] = $product;
            }
        } else {
            $grouped[$cat][] = $product;
        }
    }
    
    echo json_encode([
        'success' => true,
        'products' => $grouped
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
