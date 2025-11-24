<?php
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '6M');
ini_set('max_execution_time', 180);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!hasPermission($conn, 'promotions', 'create') && !hasPermission($conn, 'promotions', 'edit')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền upload ảnh khuyến mãi']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh hợp lệ']);
    exit;
}

$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
$max_size = 5 * 1024 * 1024;

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)']);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 5MB']);
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

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'promotion_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$filepath = $upload_dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Lỗi khi upload ảnh']);
    exit;
}

$relative_path = '/uploads/promotion_images/' . $filename;
echo json_encode([
    'success' => true,
    'message' => 'Upload ảnh thành công',
    'path' => $relative_path,
    'url' => (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'https') . '://' . $_SERVER['HTTP_HOST'] . $relative_path
]);

