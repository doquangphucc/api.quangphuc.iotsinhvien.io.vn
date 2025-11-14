<?php
/**
 * API: Thêm/Sửa phần thưởng vòng quay admin
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
$action = $id > 0 ? 'edit' : 'create';

if (!hasPermission($conn, 'wheel', $action)) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn không có quyền ' . ($action === 'edit' ? 'sửa' : 'tạo') . ' phần thưởng vòng quay'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$prize_name = trim($payload['prize_name'] ?? '');
$is_active = !empty($payload['is_active']) ? 1 : 0;

if ($prize_name === '') {
    echo json_encode(['success' => false, 'message' => 'Tên phần thưởng không được để trống'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE wheel_prizes 
            SET prize_name = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?");
        $stmt->bind_param(
            'sii',
            $prize_name,
            $is_active,
            $id
        );
    } else {
        $stmt = $conn->prepare("INSERT INTO wheel_prizes (prize_name, is_active) VALUES (?, ?)");
        $stmt->bind_param(
            'si',
            $prize_name,
            $is_active
        );
    }

    $stmt->execute();
    $newId = $id > 0 ? $id : $conn->insert_id;
    $stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'Lưu phần thưởng thành công',
        'id' => (int) $newId
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('Error saving wheel prize: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Không thể lưu phần thưởng'
    ], JSON_UNESCAPED_UNICODE);
}

mysqli_close($conn);
?>

