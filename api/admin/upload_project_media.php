<?php
// Upload image or video for projects
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

// Check for upload errors
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File vượt quá upload_max_filesize trong php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File vượt quá MAX_FILE_SIZE trong HTML form',
        UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
        UPLOAD_ERR_NO_FILE => 'Không có file được upload',
        UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
        UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
        UPLOAD_ERR_EXTENSION => 'Upload bị chặn bởi extension'
    ];
    $error_message = $error_messages[$_FILES['file']['error']] ?? 'Unknown error: ' . $_FILES['file']['error'];
    echo json_encode(['success' => false, 'message' => 'Lỗi upload: ' . $error_message]);
    exit;
}

$file = $_FILES['file'];
$media_type = $_POST['media_type'] ?? 'image'; // 'image' or 'video'

// Validate file size (50MB max)
if ($file['size'] > 50 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File quá lớn. Giới hạn 50MB']);
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
$image_dir = $base_dir . '/project_images';
$video_dir = $base_dir . '/project_videos';

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
$filename = uniqid('project_', true) . '_' . time() . '.' . $file_extension;
$upload_dir = $media_type === 'image' ? $image_dir : $video_dir;
$upload_path = $upload_dir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu file. Vui lòng thử lại']);
    exit;
}

// Generate URL
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
            . '://' . $_SERVER['HTTP_HOST'];

// Get the directory structure
$upload_subpath = ($media_type === 'image' ? 'project_images' : 'project_videos');
$url_path = '/uploads/' . $upload_subpath . '/' . $filename;

// Handle different server configurations (domain root or subdirectory)
$document_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
$current_script = $_SERVER['SCRIPT_NAME'] ?? '';

// Check if we're in a subdirectory
$script_dir = dirname($current_script);
if ($script_dir !== '/api/admin' && $script_dir !== '/') {
    // We're in a subdirectory, extract the base path
    $parts = explode('/', trim($script_dir, '/'));
    array_pop($parts); // Remove 'api'
    array_pop($parts); // Remove 'admin'
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

