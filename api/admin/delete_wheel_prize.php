<?php
/**
 * API: Xóa phần thưởng vòng quay admin
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';
require_once __DIR__ . '/permission_helper.php';

$payload = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = isset($payload['id']) ? intval($payload['id']) : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID phần thưởng không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!hasPermission($conn, 'wheel', 'delete')) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn không có quyền xóa phần thưởng vòng quay'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $conn->prepare('DELETE FROM wheel_prizes WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy phần thưởng cần xóa'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa phần thưởng'
        ], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log('Error deleting wheel prize: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Không thể xóa phần thưởng'
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>

