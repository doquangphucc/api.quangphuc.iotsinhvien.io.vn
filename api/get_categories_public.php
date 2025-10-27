<?php
// Public API to get product categories
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
    $sql = "SELECT id, name, logo_url, display_order, is_active FROM product_categories WHERE is_active = 1 ORDER BY display_order";
    $result = $conn->query($sql);
    $categories = [];
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'logo_url' => $row['logo_url'],
            'display_order' => (int)$row['display_order'],
            'is_active' => (int)$row['is_active']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi lấy danh mục: ' . $e->getMessage()
    ]);
}

$conn->close();
