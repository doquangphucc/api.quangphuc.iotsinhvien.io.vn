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
$is_active = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
$image_url = $_POST['image_url'] ?? '';
$video_url = $_POST['video_url'] ?? '';
$delete_image = isset($_POST['delete_image']) && $_POST['delete_image'] === '1';
$delete_video = isset($_POST['delete_video']) && $_POST['delete_video'] === '1';

// Handle display_order - get from POST or keep existing value when editing
$display_order = 0;
if (isset($_POST['display_order']) && $_POST['display_order'] !== '') {
    $display_order = intval($_POST['display_order']);
} elseif ($id > 0) {
    // When editing, if display_order not provided, keep the existing value
    $stmt = $conn->prepare("SELECT display_order FROM intro_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $display_order = $row['display_order'];
    }
    $stmt->close();
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Tiêu đề không được để trống']);
    exit;
}

// Initialize old URLs if editing
$old_image_url = '';
$old_video_url = '';
if ($id > 0) {
    // Get current URLs to delete old files if replaced
    $stmt = $conn->prepare("SELECT image_url, video_url FROM intro_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $old_image_url = $row['image_url'];
        $old_video_url = $row['video_url'];
    }
    $stmt->close();
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
        
        // Check if upload directory is writable
        if (!is_writable($upload_dir)) {
            echo json_encode(['success' => false, 'message' => 'Thư mục upload không có quyền ghi. Vui lòng chmod 755 cho uploads/intro_images/']);
            exit;
        }
        
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
            echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh. Kiểm tra quyền ghi file']);
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
        
        // Check if upload directory is writable
        if (!is_writable($upload_dir)) {
            echo json_encode(['success' => false, 'message' => 'Thư mục upload không có quyền ghi. Vui lòng chmod 755 cho uploads/intro_videos/']);
            exit;
        }
        
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
            echo json_encode(['success' => false, 'message' => 'Lỗi khi upload video. Kiểm tra quyền ghi file']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'File video không hợp lệ hoặc quá lớn (tối đa 50MB)']);
        exit;
    }
}

// Handle delete requests
if ($delete_image) {
    $image_url = '';
    if (!empty($old_image_url) && strpos($old_image_url, '/uploads/intro_images/') !== false) {
        $file_to_delete = __DIR__ . '/../../' . ltrim($old_image_url, '/');
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
}

if ($delete_video) {
    $video_url = '';
    if (!empty($old_video_url) && strpos($old_video_url, '/uploads/intro_videos/') !== false) {
        $file_to_delete = __DIR__ . '/../../' . ltrim($old_video_url, '/');
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
    }
}

// Use old URLs if no new file uploaded and not deleted
if (empty($image_url) && !empty($old_image_url) && !$delete_image && $id > 0) {
    $image_url = $old_image_url;
}
if (empty($video_url) && !empty($old_video_url) && !$delete_video && $id > 0) {
    $video_url = $old_video_url;
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

