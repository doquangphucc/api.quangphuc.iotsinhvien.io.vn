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
    $sql = "SELECT p.*, pc.name as category_name, pc.logo_url as category_logo 
            FROM products p 
            JOIN product_categories pc ON p.category_id = pc.id 
            WHERE p.is_available = 1 AND pc.is_active = 1";
    
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
    } else {
        $stmt = $conn->prepare($sql);
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
        $products[] = [
            'id' => (int)$row['id'],
            'category_id' => (int)$row['category_id'],
            'category_name' => $row['category_name'],
            'category_logo' => $row['category_logo'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'model' => $row['model'],
            'power_rating' => $row['power_rating'],
            'voltage' => $row['voltage'],
            'price' => floatval($row['price']),
            'price_installation' => $row['price_installation'] ? floatval($row['price_installation']) : null,
            'price_unit' => $row['price_unit'],
            'description' => $row['description'],
            'specifications' => $row['specifications'],
            'image_url' => $row['image_url'],
            'stock_quantity' => (int)$row['stock_quantity']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách sản phẩm: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

