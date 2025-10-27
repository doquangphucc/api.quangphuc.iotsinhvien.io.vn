<?php
// Add or update intro post with image/video upload

// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits for file uploads (up to 50MB)
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '60M');
ini_set('max_execution_time', 600);
ini_set('max_input_time', 600);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Get POST data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$display_order = intval($_POST['display_order'] ?? 0);
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
$image_url = $_POST['image_url'] ?? '';
$video_url = $_POST['video_url'] ?? '';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Tiêu đề không được để trống']);
    exit;
}

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
    $max_size = 50 * 1024 * 1024; // 50MB
    
    if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        $upload_dir = __DIR__ . '/../../uploads/intro_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'intro_image_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Delete old image if exists
            if ($id > 0 && !empty($image_url) && strpos($image_url, '/uploads/intro_images/') !== false) {
                $old_file = __DIR__ . '/../../' . ltrim($image_url, '/');
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            $image_url = '/uploads/intro_images/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File ảnh không hợp lệ hoặc quá lớn (tối đa 50MB)']);
        exit;
    }
}

// Handle video upload
if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['video'];
    $allowed_types = ['video/mp4', 'video/webm'];
    $max_size = 50 * 1024 * 1024; // 50MB
    
    if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        $upload_dir = __DIR__ . '/../../uploads/intro_videos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'intro_video_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Delete old video if exists
            if ($id > 0 && !empty($video_url) && strpos($video_url, '/uploads/intro_videos/') !== false) {
                $old_file = __DIR__ . '/../../' . ltrim($video_url, '/');
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            $video_url = '/uploads/intro_videos/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi upload video']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File video không hợp lệ hoặc quá lớn (tối đa 50MB)']);
        exit;
    }
}

// Save to database
if ($id > 0) {
    // Update existing post
    $stmt = $conn->prepare("UPDATE intro_posts SET title = ?, description = ?, image_url = ?, video_url = ?, display_order = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssssiii", $title, $description, $image_url, $video_url, $display_order, $is_active, $id);
} else {
    // Insert new post
    $stmt = $conn->prepare("INSERT INTO intro_posts (title, description, image_url, video_url, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $title, $description, $image_url, $video_url, $display_order, $is_active);
}

if ($stmt->execute()) {
    $post_id = $id > 0 ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Lưu bài viết thành công',
        'post_id' => $post_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();

