<?php
require_once 'connect.php';
require_once __DIR__ . '/helpers/rate_limiter.php';
require_once __DIR__ . '/helpers/audit_logger.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// SECURITY: Rate limiting - Max 5 login attempts per 5 minutes
$db = Database::getInstance();
$rateLimitResult = checkRateLimit($db->getConnection(), 'login', 5, 300, 900);
if (!$rateLimitResult['allowed']) {
    sendError('Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau ' . ceil($rateLimitResult['retry_after'] / 60) . ' phút', 429);
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
        // Audit log failed login
        AuditLogger::logLogin(false, $username, 'User not found');
        sendError('Tên đăng nhập hoặc mật khẩu không đúng');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Audit log failed login
        AuditLogger::logLogin(false, $username, 'Invalid password');
        sendError('Tên đăng nhập hoặc mật khẩu không đúng');
    }
    
    // SECURITY: Reset rate limit on successful login
    resetRateLimit($db->getConnection(), 'login');
    
    // SECURITY: Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    // Set session for logged in user
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = isset($user['is_admin']) && $user['is_admin'] ? true : false;
    
    // Audit log successful login
    AuditLogger::logLogin(true, $username);
    
    // Remove password from response
    unset($user['password']);
    
    // Convert is_admin to boolean explicitly
    $user['is_admin'] = isset($user['is_admin']) && ($user['is_admin'] == 1 || $user['is_admin'] === true) ? 1 : 0;
    
    // Return user data
    sendSuccess([
        'user' => $user
    ], 'Đăng nhập thành công');
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    sendError('Lỗi hệ thống, vui lòng thử lại sau', 500);
} catch (Exception $e) {
    error_log("Unexpected login error: " . $e->getMessage());
    sendError('Lỗi không xác định', 500);
}
?>
