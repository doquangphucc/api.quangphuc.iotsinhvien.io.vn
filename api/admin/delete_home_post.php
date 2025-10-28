<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check permission to delete home posts
if (!hasPermission($conn, 'home', 'delete')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bài đăng trang chủ']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID bài đăng không hợp lệ']);
    exit;
}

try {
    // Get image URL before deleting to clean up file
    $stmt = $conn->prepare("SELECT image_url FROM home_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    $stmt->close();
    
    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài đăng']);
        exit;
    }
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM home_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Try to delete the image file
        if (!empty($post['image_url']) && strpos($post['image_url'], '/assets/img/home/') !== false) {
            $file_path = __DIR__ . '/../../' . ltrim($post['image_url'], '/');
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa bài đăng thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa bài đăng: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}

$conn->close();

