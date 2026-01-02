<?php
// Upload home post image
// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits for file uploads (10MB)
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/cors_helper.php';
require_once __DIR__ . '/../helpers/file_validator.php';

// Setup dynamic CORS (works with any domain)
setupCORS();
handlePreflight();

header('Content-Type: application/json; charset=utf-8');

// Check permission to create/edit home posts
if (!hasPermission($conn, 'home', 'create') && !hasPermission($conn, 'home', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền upload ảnh trang chủ']);
    exit;
}

// Handle image upload
if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh để upload']);
    exit;
}

$file = $_FILES['image'];

// SECURITY: Validate image using FileUploadValidator
$validation = FileUploadValidator::validateImage($file, ['max_size' => 10 * 1024 * 1024]);
if (!$validation['valid']) {
    echo json_encode(['success' => false, 'message' => $validation['error']]);
    exit;
}

// Create upload directory if not exists
$upload_dir = __DIR__ . '/../../assets/img/home/';
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể tạo thư mục upload. Vui lòng tạo thư mục assets/img/home/ thủ công và chmod 755 hoặc 777'
        ]);
        exit;
    }
}

// Check if directory is writable
if (!is_writable($upload_dir)) {
    echo json_encode([
        'success' => false,
        'message' => 'Thư mục upload không có quyền ghi. Vui lòng chmod 755 hoặc 777 cho thư mục assets/img/home/'
    ]);
    exit;
}

// SECURITY: Generate safe random filename
$filename = FileUploadValidator::generateSafeFilename($file['name'], 'home_');
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // SECURITY: Strip EXIF data for privacy
    FileUploadValidator::stripExifData($filepath, $validation['mime']);
    
    $image_path = '/assets/img/home/' . $filename;
    echo json_encode([
        'success' => true,
        'message' => 'Upload ảnh thành công',
        'filename' => $filename,
        'path' => $image_path,
        'url' => 'https://' . $_SERVER['HTTP_HOST'] . $image_path
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
}

