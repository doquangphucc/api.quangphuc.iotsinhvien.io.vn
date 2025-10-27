<?php
// Upload logo for dich_vu or categories
// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits for file uploads (5MB)
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

// Handle CORS properly
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Handle logo upload
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn logo để upload']);
    exit;
}

$file = $_FILES['logo'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
    exit;
}

// Validate file size
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Kích thước logo không được vượt quá 5MB']);
    exit;
}

// Create parent directory if not exists
$parent_dir = __DIR__ . '/../../assets/img';
if (!is_dir($parent_dir)) {
    if (!@mkdir($parent_dir, 0755, true) && !is_dir($parent_dir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể tạo thư mục assets/img. Vui lòng chmod 755 hoặc 777 cho thư mục assets/'
        ]);
        exit;
    }
}

// Create upload directory if not exists
$upload_dir = $parent_dir . '/logo/';
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể tạo thư mục upload. Vui lòng chmod 755 hoặc 777 cho thư mục assets/img/'
        ]);
        exit;
    }
}

// Check if directory is writable
if (!is_writable($upload_dir)) {
    echo json_encode([
        'success' => false,
        'message' => 'Thư mục không có quyền ghi. Vui lòng chạy: chmod -R 755 assets/img (qua SSH)'
    ]);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $logo_url = '../assets/img/logo/' . $filename;
    echo json_encode([
        'success' => true,
        'message' => 'Upload logo thành công',
        'filename' => $filename,
        'logo_url' => $logo_url
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi upload logo']);
}

