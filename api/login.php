<?php
require_once 'connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dữ liệu JSON không hợp lệ');
}

// Validate required fields
$requiredFields = ['username', 'password'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiếu các trường bắt buộc: ' . implode(', ', $missingFields));
}

// Sanitize input
$username = sanitizeInput($input['username']);
$password = $input['password'];

// Validate input
if (strlen($username) < 3) {
    sendError('Tên đăng nhập không hợp lệ');
}

if (strlen($password) < 6) {
    sendError('Mật khẩu không hợp lệ');
}

try {
    $db = Database::getInstance();
    
    // Find user by username
    $user = $db->selectOne('users', ['username' => $username]);
    
    if (!$user) {
        sendError('Tên đăng nhập hoặc mật khẩu không đúng');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendError('Tên đăng nhập hoặc mật khẩu không đúng');
    }
    
    // Set session for logged in user
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    // Debug logging
    error_log("Login Success - Session ID: " . session_id());
    error_log("Login Success - User ID set: " . $_SESSION['user_id']);
    error_log("Login Success - Session Data: " . print_r($_SESSION, true));
    
    // Remove password from response
    unset($user['password']);
    
    // Return user data with session info for debugging
    sendSuccess([
        'user' => $user,
        'session_id' => session_id(),
        'debug' => [
            'session_name' => session_name(),
            'cookie_params' => session_get_cookie_params()
        ]
    ], 'Đăng nhập thành công');
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    sendError('Lỗi hệ thống, vui lòng thử lại sau', 500);
} catch (Exception $e) {
    error_log("Unexpected login error: " . $e->getMessage());
    sendError('Lỗi không xác định', 500);
}
?>
