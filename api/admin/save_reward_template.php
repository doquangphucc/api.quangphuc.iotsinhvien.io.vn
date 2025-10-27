<?php
// Suppress warnings to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) && $data['id'] > 0 ? intval($data['id']) : 0;
$reward_name = $data['reward_name'] ?? '';
$reward_type = $data['reward_type'] ?? '';
$reward_value = !empty($data['reward_value']) ? floatval($data['reward_value']) : null;
$reward_description = $data['reward_description'] ?? '';
$reward_quantity = !empty($data['reward_quantity']) ? intval($data['reward_quantity']) : null;
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

if (empty($reward_name) || empty($reward_type)) {
    echo json_encode(['success' => false, 'message' => 'Tên và loại phần thưởng không được để trống'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate reward_type
$valid_reward_types = ['voucher', 'cash', 'gift'];
if (!in_array($reward_type, $valid_reward_types)) {
    echo json_encode(['success' => false, 'message' => 'Loại phần thưởng không hợp lệ. Chỉ chấp nhận: voucher, cash, gift.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($id > 0) {
        // Update existing reward template
        $stmt = $conn->prepare("UPDATE reward_templates SET reward_name = ?, reward_type = ?, reward_value = ?, reward_description = ?, reward_quantity = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("ssdssii", $reward_name, $reward_type, $reward_value, $reward_description, $reward_quantity, $is_active, $id);
    } else {
        // Insert new reward template
        $stmt = $conn->prepare("INSERT INTO reward_templates (reward_name, reward_type, reward_value, reward_description, reward_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $reward_name, $reward_type, $reward_value, $reward_description, $reward_quantity, $is_active);
    }
    
    $stmt->execute();
    $template_id = $id > 0 ? $id : $conn->insert_id;
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Lưu mẫu phần thưởng thành công',
        'id' => $template_id
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error saving reward template: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
