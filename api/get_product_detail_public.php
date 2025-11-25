<?php
// Public API to get product detail by ID
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_mysqli.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID sản phẩm không hợp lệ'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    $sql = "SELECT p.id, p.category_id, p.title, p.market_price, p.category_price, 
                   p.technical_description, p.image_url, p.panel_power_watt, 
                   p.inverter_power_watt, p.battery_capacity_kwh, p.cabinet_power_kw, 
                   p.is_active, p.created_at, p.updated_at,
                   pc.name as category_name, pc.logo_url as category_logo 
            FROM products p 
            LEFT JOIN product_categories pc ON p.category_id = pc.id 
            WHERE p.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm'
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    $row = $result->fetch_assoc();
    
    // Fix image URL path for HTML pages in /html/ subdirectory
    $imageUrl = $row['image_url'];
    if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
        $imageUrl = '../' . $imageUrl;
    }
    
    $categoryLogo = $row['category_logo'];
    if ($categoryLogo && !str_starts_with($categoryLogo, 'http')) {
        $categoryLogo = '../' . $categoryLogo;
    }
    
    $product = [
        'id' => (int)$row['id'],
        'category_id' => (int)$row['category_id'],
        'category_name' => $row['category_name'],
        'category_logo' => $categoryLogo,
        'title' => $row['title'],
        'market_price' => floatval($row['market_price']),
        'category_price' => $row['category_price'] ? floatval($row['category_price']) : null,
        'technical_description' => $row['technical_description'],
        'image_url' => $imageUrl,
        'panel_power_watt' => $row['panel_power_watt'] ? (int)$row['panel_power_watt'] : null,
        'inverter_power_watt' => $row['inverter_power_watt'] ? (int)$row['inverter_power_watt'] : null,
        'battery_capacity_kwh' => $row['battery_capacity_kwh'] ? floatval($row['battery_capacity_kwh']) : null,
        'cabinet_power_kw' => $row['cabinet_power_kw'] ? floatval($row['cabinet_power_kw']) : null,
        'is_active' => (int)$row['is_active'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
    
    echo json_encode([
        'success' => true,
        'product' => $product
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy chi tiết sản phẩm: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

