<?php
// API để lưu phần thưởng từ vòng quay may mắn
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'session.php';
require_once 'db_mysqli.php';

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ chấp nhận phương thức POST'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng đăng nhập để lưu phần thưởng'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy dữ liệu từ request
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dữ liệu không hợp lệ'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Validate dữ liệu đầu vào
$reward_name = isset($input['reward_name']) ? trim($input['reward_name']) : '';
$reward_type = isset($input['reward_type']) ? trim($input['reward_type']) : 'gift';
$reward_value = isset($input['reward_value']) ? trim($input['reward_value']) : null;
$reward_code = isset($input['reward_code']) ? trim($input['reward_code']) : null;
$reward_image = isset($input['reward_image']) ? trim($input['reward_image']) : null;
$ticket_id = isset($input['ticket_id']) ? intval($input['ticket_id']) : null;
$expires_days = isset($input['expires_days']) ? intval($input['expires_days']) : 30; // Mặc định hết hạn sau 30 ngày

if (empty($reward_name)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Tên phần thưởng không được để trống'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    $conn = DatabaseConnection::getInstance()->getConnection();
    
    // Nếu có ticket_id, kiểm tra xem vé này có thuộc về user không
    if ($ticket_id) {
        $check_ticket_sql = "SELECT id, status FROM lottery_tickets WHERE id = ? AND user_id = ?";
        $stmt_check = $conn->prepare($check_ticket_sql);
        $stmt_check->bind_param("ii", $ticket_id, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Vé số không tồn tại hoặc không thuộc về bạn'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $ticket = $result_check->fetch_assoc();
        
        // Kiểm tra xem vé đã được sử dụng chưa
        if ($ticket['status'] === 'used') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Vé số này đã được sử dụng rồi'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $stmt_check->close();
    }
    
    // Tạo mã phần thưởng ngẫu nhiên nếu chưa có
    if (empty($reward_code)) {
        $reward_code = 'REWARD' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }
    
    // Tính ngày hết hạn
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_days} days"));
    
    // Thêm phần thưởng vào database
    $sql = "INSERT INTO lottery_rewards 
            (user_id, reward_name, reward_type, reward_value, reward_code, reward_image, ticket_id, expires_at, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isssssis",
        $user_id,
        $reward_name,
        $reward_type,
        $reward_value,
        $reward_code,
        $reward_image,
        $ticket_id,
        $expires_at
    );
    
    if ($stmt->execute()) {
        $reward_id = $stmt->insert_id;
        
        // Nếu có ticket_id, cập nhật trạng thái vé thành 'used'
        if ($ticket_id) {
            $update_ticket_sql = "UPDATE lottery_tickets SET status = 'used', used_at = NOW() WHERE id = ?";
            $stmt_update = $conn->prepare($update_ticket_sql);
            $stmt_update->bind_param("i", $ticket_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
        
        $stmt->close();
        
        // Lấy thông tin phần thưởng vừa tạo
        $get_reward_sql = "SELECT * FROM lottery_rewards WHERE id = ?";
        $stmt_get = $conn->prepare($get_reward_sql);
        $stmt_get->bind_param("i", $reward_id);
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        $reward = $result->fetch_assoc();
        $stmt_get->close();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Lưu phần thưởng thành công!',
            'data' => $reward
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Không thể lưu phần thưởng: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

