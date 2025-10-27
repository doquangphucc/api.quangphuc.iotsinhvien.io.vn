<?php
// Turn off output buffering and error display
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../connect.php';
    require_once __DIR__ . '/../session.php';
    require_once __DIR__ . '/../auth_helpers.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
        exit;
    }

    requireAuth();
    
    // Check if user is admin
    if (!is_admin()) {
        ob_clean();
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()]);
        exit;
    }

    $id = isset($input['id']) && $input['id'] > 0 ? (int)$input['id'] : null;
    $reward_name = $input['reward_name'] ?? '';
    $reward_type = $input['reward_type'] ?? '';
    $reward_value = !empty($input['reward_value']) ? (float)$input['reward_value'] : null;
    $reward_description = $input['reward_description'] ?? '';
    $reward_quantity = !empty($input['reward_quantity']) ? (int)$input['reward_quantity'] : null;
    $is_active = isset($input['is_active']) ? (bool)$input['is_active'] : true;

    if (empty($reward_name) || empty($reward_type)) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Tên và loại phần thưởng không được để trống.']);
        exit;
    }

    // Validate reward_type (must match ENUM in database)
    $valid_reward_types = ['voucher', 'cash', 'gift'];
    if (!in_array($reward_type, $valid_reward_types)) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Loại phần thưởng không hợp lệ. Chỉ chấp nhận: voucher, cash, gift.']);
        exit;
    }

    // Validate reward_value based on type
    if ($reward_type === 'voucher' && ($reward_value === null || $reward_value <= 0)) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Giá trị voucher phải là số dương.']);
        exit;
    }
    if ($reward_type === 'cash' && ($reward_value === null || $reward_value <= 0)) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Giá trị tiền mặt phải là số dương.']);
        exit;
    }

    // Validate reward_quantity for gift type
    if ($reward_type === 'gift' && ($reward_quantity === null || $reward_quantity <= 0)) {
        ob_clean();
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Số lượng quà tặng phải là số dương.']);
        exit;
    } elseif ($reward_type !== 'gift') {
        $reward_quantity = null; // Ensure quantity is null for non-gift types
    }

    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Check if PDO connection is valid
    if ($pdo === null) {
        throw new Exception('Database connection is null');
    }

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
        $template_id = $id;
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
        $template_id = $pdo->lastInsertId();
        $message = 'Thêm mẫu phần thưởng thành công.';
    }

    ob_clean();
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'id' => $template_id
    ]);

} catch (Exception $e) {
    error_log("Error in save_reward_template: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
} catch (Error $e) {
    error_log("PHP Error in save_reward_template: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Lỗi PHP: ' . $e->getMessage()]);
}
?>
