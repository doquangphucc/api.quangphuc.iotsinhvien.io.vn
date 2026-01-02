<?php
// Upload image or video for home posts
//
// This API allows uploading images (jpg, png, gif, webp) and videos (mp4, webm)
// up to 50MB each. Files are saved to dedicated folders and accessible via URL.

// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits for large files
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '60M');
ini_set('max_execution_time', 600);
ini_set('max_input_time', 600);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/../helpers/cors_helper.php';
require_once __DIR__ . '/../helpers/file_validator.php';

// Setup dynamic CORS
setupCORS();
handlePreflight();

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Không có file được gửi lên. Có thể file quá lớn hoặc chưa chọn file']);
    exit;
}

$file = $_FILES['file'];
$media_type = $_POST['media_type'] ?? 'image'; // 'image' or 'video'

// SECURITY: Validate file using FileUploadValidator
if ($media_type === 'video') {
    $validation = FileUploadValidator::validateVideo($file, ['max_size' => 50 * 1024 * 1024]);
} else {
    $validation = FileUploadValidator::validateImage($file, ['max_size' => 50 * 1024 * 1024]);
}

if (!$validation['valid']) {
    echo json_encode(['success' => false, 'message' => $validation['error']]);
    exit;
}

// Define upload directories
$base_dir = __DIR__ . '/../../uploads';
$image_dir = $base_dir . '/home_images';
$video_dir = $base_dir . '/home_videos';

// Create directories if they don't exist
if (!is_dir($base_dir)) {
    if (!mkdir($base_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo thư mục uploads']);
        exit;
    }
}
if (!is_dir($image_dir)) {
    if (!mkdir($image_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo thư mục home_images']);
        exit;
    }
}
if (!is_dir($video_dir)) {
    if (!mkdir($video_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo thư mục home_videos']);
        exit;
    }
}

// SECURITY: Generate safe random filename
$prefix = 'home_';
$filename = FileUploadValidator::generateSafeFilename($file['name'], $prefix);
$upload_dir = $media_type === 'image' ? $image_dir : $video_dir;
$upload_path = $upload_dir . '/' . $filename;

// Check if directory is writable
if (!is_writable($upload_dir)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Thư mục không có quyền ghi. Chạy: chmod 755 ' . basename($upload_dir) . '/'
    ]);
    exit;
}

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Lỗi khi lưu file. Kiểm tra permissions thư mục'
    ]);
    exit;
}

// SECURITY: Strip EXIF data for images
if ($media_type === 'image') {
    FileUploadValidator::stripExifData($upload_path, $validation['mime']);
}

// Generate URL
$protocol = 'https';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https';
}
$base_url = $protocol . '://' . $_SERVER['HTTP_HOST'];

// Get the directory structure
$upload_subpath = ($media_type === 'image' ? 'home_images' : 'home_videos');
$url_path = '/uploads/' . $upload_subpath . '/' . $filename;

// Handle different server configurations
$current_script = $_SERVER['SCRIPT_NAME'] ?? '';
$script_dir = dirname($current_script);
if ($script_dir !== '/api/admin' && $script_dir !== '/') {
    $parts = explode('/', trim($script_dir, '/'));
    array_pop($parts);
    array_pop($parts);
    $subdir = !empty($parts) ? '/' . implode('/', $parts) : '';
    $url_path = $subdir . $url_path;
}

$full_url = $base_url . $url_path;

echo json_encode([
    'success' => true,
    'message' => 'Upload thành công',
    'url' => $full_url,
    'filename' => $filename
]);

