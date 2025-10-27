<?php
// Add or update reward template
// Turn off output buffering to capture any errors
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../session.php';
    require_once __DIR__ . '/../db_mysqli.php';
    require_once __DIR__ . '/../auth_helpers.php';

    if (!is_admin()) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()]);
        exit;
    }

    $id = isset($data['id']) ? intval($data['id']) : 0;
    $reward_name = $data['reward_name'] ?? '';
    $reward_type = $data['reward_type'] ?? '';
    $reward_value = !empty($data['reward_value']) ? floatval($data['reward_value']) : null;
    $reward_description = $data['reward_description'] ?? '';
    $reward_quantity = !empty($data['reward_quantity']) ? intval($data['reward_quantity']) : null;
    $is_active = isset($data['is_active']) ? ($data['is_active'] ? 1 : 0) : 1;

    if (empty($reward_name) || empty($reward_type)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Tên và loại phần thưởng không được để trống']);
        exit;
    }

    // Prepare SQL based on insert or update
    if ($id > 0) {
        // Update existing reward template
        $stmt = $conn->prepare("UPDATE reward_templates SET reward_name = ?, reward_type = ?, reward_value = ?, reward_description = ?, reward_quantity = ?, is_active = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssdsisii", $reward_name, $reward_type, $reward_value, $reward_description, $reward_quantity, $is_active, $id);
    } else {
        // Insert new reward template
        $stmt = $conn->prepare("INSERT INTO reward_templates (reward_name, reward_type, reward_value, reward_description, reward_quantity, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssdsii", $reward_name, $reward_type, $reward_value, $reward_description, $reward_quantity, $is_active);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $template_id = $id > 0 ? $id : $conn->insert_id;
    $stmt->close();
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Lưu mẫu phần thưởng thành công',
        'template_id' => $template_id
    ]);

} catch (Exception $e) {
    ob_clean();
    error_log("Error in save_reward_template: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>