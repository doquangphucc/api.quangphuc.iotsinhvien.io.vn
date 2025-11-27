<?php
// Public API to get all images for a product
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db_mysqli.php';

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID sản phẩm không hợp lệ'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    $sql = "SELECT id, product_id, image_url, display_order, created_at 
            FROM product_images 
            WHERE product_id = ? 
            ORDER BY display_order ASC, id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        // Fix image URL path for HTML pages in /html/ subdirectory
        $imageUrl = $row['image_url'];
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = '../' . $imageUrl;
        }
        
        $images[] = [
            'id' => (int)$row['id'],
            'product_id' => (int)$row['product_id'],
            'image_url' => $imageUrl,
            'display_order' => (int)$row['display_order'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'images' => $images,
        'count' => count($images)
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh sách ảnh: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

