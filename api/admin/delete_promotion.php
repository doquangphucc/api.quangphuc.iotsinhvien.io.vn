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

if (!hasPermission($conn, 'promotions', 'delete')) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa khuyến mãi']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$id = isset($payload['id']) ? (int)$payload['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID khuyến mãi không hợp lệ']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Đã xóa khuyến mãi'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Không thể xóa khuyến mãi: ' . $e->getMessage()
    ]);
}

$conn->close();

