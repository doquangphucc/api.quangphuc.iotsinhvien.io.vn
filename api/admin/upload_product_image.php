<?php
// Upload product image
// Disable PHP errors/warnings display to prevent breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Increase upload limits for file uploads (2MB)
ini_set('upload_max_filesize', '2M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

// Handle CORS properly for same-origin with credentials
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'https://hceco.io.vn' || empty($origin)) {
    header('Access-Control-Allow-Origin: https://hceco.io.vn');
    header('Access-Control-Allow-Credentials: true');
}
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

// Handle image upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh để upload']);
    exit;
}

$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
    exit;
}

// Validate file size
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 5MB']);
    exit;
}

// Create upload directory if not exists
$upload_dir = __DIR__ . '/../../assets/img/products/';
if (!is_dir($upload_dir)) {
    if (!@mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Không thể tạo thư mục upload. Vui lòng tạo thư mục assets/img/products/ thủ công và chmod 755 hoặc 777'
        ]);
        exit;
    }
}

// Check if directory is writable
if (!is_writable($upload_dir)) {
    echo json_encode([
        'success' => false,
        'message' => 'Thư mục upload không có quyền ghi. Vui lòng chmod 755 hoặc 777 cho thư mục assets/img/products/'
    ]);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $image_path = '/assets/img/products/' . $filename;
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

