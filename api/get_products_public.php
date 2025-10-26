<?php
// Public API to get products with category info
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_mysqli.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

try {
    $sql = "SELECT p.id, p.category_id, p.title, p.market_price, p.category_price, 
                   p.technical_description, p.image_url, p.is_active,
                   pc.name as category_name, pc.logo_url as category_logo 
            FROM products p 
            JOIN product_categories pc ON p.category_id = pc.id 
            WHERE p.is_active = 1 AND pc.is_active = 1";
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
    }
    
    $sql .= " ORDER BY pc.display_order, p.id";
    
    $stmt = $conn->prepare($sql);
    
    if ($category_id) {
        $stmt->bind_param("i", $category_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        // Fix image URL path for HTML pages in /html/ subdirectory
        $imageUrl = $row['image_url'];
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = '../' . $imageUrl;
        }
        
        $products[] = [
            'id' => (int)$row['id'],
            'category_id' => (int)$row['category_id'],
            'category_name' => $row['category_name'],
            'category_logo' => $row['category_logo'],
            'title' => $row['title'],
            'market_price' => floatval($row['market_price']),
            'category_price' => $row['category_price'] ? floatval($row['category_price']) : null,
            'technical_description' => $row['technical_description'],
            'image_url' => $imageUrl,
            'is_active' => (int)$row['is_active']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
