<?php
// Add or update reward template
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../db_mysqli.php';
require_once __DIR__ . '/../auth_helpers.php';

if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = isset($data['id']) ? intval($data['id']) : 0;
$reward_name = $data['reward_name'] ?? '';
$reward_type = $data['reward_type'] ?? '';
$reward_value = !empty($data['reward_value']) ? floatval($data['reward_value']) : null;
$reward_description = $data['reward_description'] ?? '';
$reward_quantity = !empty($data['reward_quantity']) ? intval($data['reward_quantity']) : null;
$is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

if (empty($reward_name) || empty($reward_type)) {
    echo json_encode(['success' => false, 'message' => 'Tên và loại phần thưởng không được để trống']);
    exit;
}

if ($id > 0) {
    // Update
    $stmt = $conn->prepare("UPDATE reward_templates SET reward_name = ?, reward_type = ?, reward_value = ?, reward_description = ?, reward_quantity = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssdsisi", $reward_name, $reward_type, $reward_value, $reward_description, $reward_quantity, $is_active, $id);
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO reward_templates (reward_name, reward_type, reward_value, reward_description, reward_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdissi", $reward_name, $reward_type, $reward_value, $reward_description, $reward_quantity, $is_active);
}

if ($stmt->execute()) {
    $template_id = $id > 0 ? $id : $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'Lưu mẫu phần thưởng thành công',
        'template_id' => $template_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

