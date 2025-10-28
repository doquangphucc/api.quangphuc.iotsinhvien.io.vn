<?php
// Delete dich_vu
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check admin access
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

// Get logo_url before deletion to delete file
$sql = "SELECT logo_url FROM dich_vu WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$logo_url = $row['logo_url'] ?? '';

// Delete from database
$sql = "DELETE FROM dich_vu WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Delete logo file if exists
    if ($logo_url) {
        $file_path = __DIR__ . '/..' . str_replace('../', '/', $logo_url);
        if (file_exists($file_path) && is_file($file_path)) {
            @unlink($file_path);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Xóa dịch vụ thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

