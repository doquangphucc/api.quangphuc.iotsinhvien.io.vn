<?php
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('max_execution_time', 180);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';
require_once __DIR__ . '/../helpers/cors_helper.php';
require_once __DIR__ . '/../helpers/file_validator.php';

// Setup dynamic CORS
setupCORS();
handlePreflight();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasPermission($conn, 'promotions', 'create') && !hasPermission($conn, 'promotions', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền upload ảnh khuyến mãi']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh hợp lệ']);
    exit;
}

$file = $_FILES['image'];

// SECURITY: Validate image using FileUploadValidator
$validation = FileUploadValidator::validateImage($file, ['max_size' => 10 * 1024 * 1024]);
if (!$validation['valid']) {
    echo json_encode(['success' => false, 'message' => $validation['error']]);
    exit;
}

$upload_dir = __DIR__ . '/../../uploads/promotion_images/';
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        echo json_encode(['success' => false, 'message' => 'Không thể tạo thư mục upload']);
        exit;
    }
}

if (!is_writable($upload_dir)) {
    echo json_encode(['success' => false, 'message' => 'Thư mục upload không có quyền ghi. Vui lòng chmod 755/777 cho uploads/promotion_images']);
    exit;
}

// SECURITY: Generate safe random filename
$filename = FileUploadValidator::generateSafeFilename($file['name'], 'promotion_');
$filepath = $upload_dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
    exit;
}

// SECURITY: Strip EXIF data for privacy
FileUploadValidator::stripExifData($filepath, $validation['mime']);

$relative_path = '/uploads/promotion_images/' . $filename;
echo json_encode([
    'success' => true,
    'message' => 'Upload ảnh thành công',
    'path' => $relative_path,
    'url' => (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https') . '://' . $_SERVER['HTTP_HOST'] . $relative_path
]);

