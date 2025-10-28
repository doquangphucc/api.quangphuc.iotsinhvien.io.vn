<?php
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
    exit();
}

if (!hasPermission($conn, 'packages', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa gói sản phẩm']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// package_items will be deleted automatically due to CASCADE
$stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Xóa gói sản phẩm thành công'], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
?>

