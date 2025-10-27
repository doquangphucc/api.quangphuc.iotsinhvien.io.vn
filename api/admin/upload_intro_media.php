<?php
// Upload image or video for intro posts
//
// This API allows uploading images (jpg, png, gif, webp) and videos (mp4, webm)
// up to 10MB each. Files are saved to dedicated folders and accessible via URL.

// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '20M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Không có file được upload hoặc có lỗi xảy ra']);
    exit;
}

$file = $_FILES['file'];
$media_type = $_POST['media_type'] ?? 'image'; // 'image' or 'video'

// Validate file size (10MB max)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File quá lớn. Giới hạn 10MB']);
    exit;
}

// Validate file type
$allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowed_video_extensions = ['mp4', 'webm'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($media_type === 'image' && !in_array($file_extension, $allowed_image_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh: JPG, PNG, GIF, WEBP']);
    exit;
}

if ($media_type === 'video' && !in_array($file_extension, $allowed_video_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file video: MP4, WEBM']);
    exit;
}

// Define upload directories
$base_dir = __DIR__ . '/../../uploads';
$image_dir = $base_dir . '/intro_images';
$video_dir = $base_dir . '/intro_videos';

// Create directories if they don't exist
if (!is_dir($base_dir)) {
    mkdir($base_dir, 0755, true);
}
if (!is_dir($image_dir)) {
    mkdir($image_dir, 0755, true);
}
if (!is_dir($video_dir)) {
    mkdir($video_dir, 0755, true);
}

// Generate unique filename
$filename = uniqid('intro_', true) . '_' . time() . '.' . $file_extension;
$upload_dir = $media_type === 'image' ? $image_dir : $video_dir;
$upload_path = $upload_dir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu file. Vui lòng thử lại']);
    exit;
}

// Generate URL (adjust this based on your domain setup)
// For production, this should be: https://yourdomain.com/uploads/intro_images/filename.jpg
$url_path = '/uploads/' . ($media_type === 'image' ? 'intro_images' : 'intro_videos') . '/' . $filename;

// For local development with XAMPP/WAMP: 
// Adjust this based on your setup
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
            . '://' . $_SERVER['HTTP_HOST'];

// Remove any subdirectory if exists
$script_dir = dirname(dirname(__DIR__));
$project_root = dirname($script_dir);
$url_path = str_replace($project_root, '', $url_path);

$full_url = $base_url . str_replace('\\', '/', $url_path);

echo json_encode([
    'success' => true,
    'message' => 'Upload thành công',
    'url' => $full_url,
    'filename' => $filename
]);
