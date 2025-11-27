<?php
// Admin API to delete a product image
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

if (!isset($input['image_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin image_id']);
    exit;
}

$image_id = intval($input['image_id']);

if ($image_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID ảnh không hợp lệ']);
    exit;
}

try {
    // Get image info before deletion (to optionally delete file)
    $getStmt = $conn->prepare("SELECT image_url FROM product_images WHERE id = ?");
    $getStmt->bind_param("i", $image_id);
    $getStmt->execute();
    $getResult = $getStmt->get_result();
    
    if ($getResult->num_rows === 0) {
        $getStmt->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy ảnh']);
        exit;
    }
    
    $imageRow = $getResult->fetch_assoc();
    $getStmt->close();
    
    // Delete from database
    $deleteStmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
    $deleteStmt->bind_param("i", $image_id);
    
    if ($deleteStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Xóa ảnh thành công'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Lỗi khi xóa ảnh khỏi database');
    }
    
    $deleteStmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi xóa ảnh: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

