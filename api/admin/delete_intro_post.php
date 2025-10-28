<?php
// Delete intro post
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (!hasPermission($conn, 'intro-posts', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bài giới thiệu']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Get intro post media files before deleting
$stmt = $conn->prepare("SELECT image_url, video_url FROM intro_posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM intro_posts WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete image file if exists
    if ($post && !empty($post['image_url'])) {
        $image_path = $post['image_url'];
        
        // Try uploads directory
        $image_file = __DIR__ . '/../../uploads/intro_images/' . basename($image_path);
        if (file_exists($image_file)) {
            unlink($image_file);
        }
    }
    
    // Delete video file if exists
    if ($post && !empty($post['video_url'])) {
        $video_path = $post['video_url'];
        
        // Try uploads directory
        $video_file = __DIR__ . '/../../uploads/intro_videos/' . basename($video_path);
        if (file_exists($video_file)) {
            unlink($video_file);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Xóa bài viết thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();

