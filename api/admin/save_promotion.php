<?php
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$id = isset($payload['id']) ? (int)$payload['id'] : 0;
$title = trim($payload['title'] ?? '');
$image_url = trim($payload['image_url'] ?? '');
$target_link = trim($payload['target_link'] ?? '');
$target_pages = $payload['target_pages'] ?? [];
$is_active = !empty($payload['is_active']) ? 1 : 0;

if ($id > 0) {
    if (!hasPermission($conn, 'promotions', 'edit')) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền sửa khuyến mãi']);
        exit;
    }
} else {
    if (!hasPermission($conn, 'promotions', 'create')) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền tạo khuyến mãi']);
        exit;
    }
}

if ($title === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tiêu đề khuyến mãi']);
    exit;
}

if ($target_link === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn trang đích khi click vào banner']);
    exit;
}

if (!is_array($target_pages)) {
    echo json_encode(['success' => false, 'message' => 'Danh sách trang hiển thị không hợp lệ']);
    exit;
}

$normalized_pages = [];
foreach ($target_pages as $page) {
    $page = trim((string)$page);
    if ($page !== '') {
        $normalized_pages[] = $page;
    }
}
$normalized_pages = array_values(array_unique($normalized_pages));

if (empty($normalized_pages)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ít nhất một trang để hiển thị khuyến mãi']);
    exit;
}

$pages_json = json_encode($normalized_pages, JSON_UNESCAPED_SLASHES);
$image_value = $image_url !== '' ? $image_url : null;

try {
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE promotions SET title = ?, image_url = ?, target_link = ?, target_pages = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssssii', $title, $image_value, $target_link, $pages_json, $is_active, $id);
        $stmt->execute();
        $message = 'Cập nhật khuyến mãi thành công';
    } else {
        $stmt = $conn->prepare("INSERT INTO promotions (title, image_url, target_link, target_pages, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param('ssssi', $title, $image_value, $target_link, $pages_json, $is_active);
        $stmt->execute();
        $id = $conn->insert_id;
        $message = 'Tạo khuyến mãi thành công';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'id' => $id
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể lưu khuyến mãi: ' . $e->getMessage()
    ]);
}

$conn->close();

