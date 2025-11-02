<?php
/**
 * API: Get current user info (public - không yêu cầu đăng nhập)
 * Nếu đã đăng nhập, trả về username và phone để tự động điền form
 * Nếu chưa đăng nhập, trả về null
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/db_mysqli.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Kiểm tra xem user đã đăng nhập chưa
    if (!isLoggedIn()) {
        // Chưa đăng nhập - trả về null
        echo json_encode([
            'success' => true,
            'logged_in' => false,
            'user' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // User đã đăng nhập - lấy thông tin
    $userId = getCurrentUserId();
    
    $sql = "SELECT id, username, full_name, phone 
            FROM users 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'logged_in' => false,
            'user' => null
        ], JSON_UNESCAPED_UNICODE);
        $stmt->close();
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Xử lý full_name: ưu tiên full_name, nếu không có thì dùng username
    $fullName = !empty($user['full_name']) ? $user['full_name'] : ($user['username'] ?? '');
    
    // Xử lý phone: lấy phone nếu có, không thì để rỗng
    $phone = !empty($user['phone']) ? $user['phone'] : '';
    
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'full_name' => $fullName,
            'phone' => $phone
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'message' => 'Lỗi khi lấy thông tin user: ' . $e->getMessage(),
        'user' => null
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

