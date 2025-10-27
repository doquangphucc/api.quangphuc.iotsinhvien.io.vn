<?php
// Turn off output buffering and error display
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

requireAuth();
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dữ liệu JSON không hợp lệ: ' . json_last_error_msg());
}

$id = isset($input['id']) && $input['id'] > 0 ? (int)$input['id'] : null;
$reward_name = $input['reward_name'] ?? '';
$reward_type = $input['reward_type'] ?? '';
$reward_value = !empty($input['reward_value']) ? (float)$input['reward_value'] : null;
$reward_description = $input['reward_description'] ?? '';
$reward_quantity = !empty($input['reward_quantity']) ? (int)$input['reward_quantity'] : null;
$is_active = isset($input['is_active']) ? (bool)$input['is_active'] : true;

if (empty($reward_name) || empty($reward_type)) {
    sendError('Tên và loại phần thưởng không được để trống.');
}

// Validate reward_type
$valid_reward_types = ['voucher', 'cash', 'gift'];
if (!in_array($reward_type, $valid_reward_types)) {
    sendError('Loại phần thưởng không hợp lệ.');
}

// Validate reward_value based on type
if ($reward_type === 'voucher' && ($reward_value === null || $reward_value <= 0)) {
    sendError('Giá trị voucher phải là số dương.');
}
if ($reward_type === 'cash' && ($reward_value === null || $reward_value <= 0)) {
    sendError('Giá trị tiền mặt phải là số dương.');
}

// Validate reward_quantity for gift type
if ($reward_type === 'gift' && ($reward_quantity === null || $reward_quantity <= 0)) {
    sendError('Số lượng quà tặng phải là số dương.');
} elseif ($reward_type !== 'gift') {
    $reward_quantity = null; // Ensure quantity is null for non-gift types
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    if ($id) {
        // Update existing reward template
        $sql = "UPDATE reward_templates SET 
                    reward_name = ?, 
                    reward_type = ?, 
                    reward_value = ?, 
                    reward_description = ?, 
                    reward_quantity = ?,
                    is_active = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $reward_name, 
            $reward_type, 
            $reward_value, 
            $reward_description, 
            $reward_quantity,
            $is_active, 
            $id
        ]);
        $message = 'Cập nhật mẫu phần thưởng thành công.';
    } else {
        // Insert new reward template
        $sql = "INSERT INTO reward_templates (reward_name, reward_type, reward_value, reward_description, reward_quantity, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $reward_name, 
            $reward_type, 
            $reward_value, 
            $reward_description, 
            $reward_quantity,
            $is_active
        ]);
        $id = $pdo->lastInsertId();
        $message = 'Thêm mẫu phần thưởng thành công.';
    }

    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    sendSuccess($message, ['id' => $id]);

} catch (Exception $e) {
    error_log("Error saving reward template: " . $e->getMessage());
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    sendError('Lỗi khi lưu mẫu phần thưởng: ' . $e->getMessage());
}
?>
