<?php
// Admin API to add an image to a product
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['image_url'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin product_id hoặc image_url']);
    exit;
}

$product_id = intval($input['product_id']);
$image_url = trim($input['image_url']);
$display_order = isset($input['display_order']) ? intval($input['display_order']) : 0;

if ($product_id <= 0 || empty($image_url)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ']);
    exit;
}

try {
    // Verify product exists
    $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $checkStmt->bind_param("i", $product_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
        exit;
    }
    $checkStmt->close();
    
    // If display_order is 0, get the max order and add 1
    if ($display_order <= 0) {
        $maxOrderStmt = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM product_images WHERE product_id = ?");
        $maxOrderStmt->bind_param("i", $product_id);
        $maxOrderStmt->execute();
        $maxOrderResult = $maxOrderStmt->get_result();
        $maxOrderRow = $maxOrderResult->fetch_assoc();
        $display_order = intval($maxOrderRow['next_order']);
        $maxOrderStmt->close();
    }
    
    // Insert image
    $insertStmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, display_order) VALUES (?, ?, ?)");
    $insertStmt->bind_param("isi", $product_id, $image_url, $display_order);
    
    if ($insertStmt->execute()) {
        $image_id = $conn->insert_id;
        
        // Fix image URL for response
        $imageUrl = $image_url;
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = '../' . $imageUrl;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Thêm ảnh thành công',
            'image' => [
                'id' => $image_id,
                'product_id' => $product_id,
                'image_url' => $imageUrl,
                'display_order' => $display_order
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Lỗi khi thêm ảnh vào database');
    }
    
    $insertStmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi thêm ảnh: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

