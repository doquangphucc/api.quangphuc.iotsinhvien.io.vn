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
$requiredFields = ['full_name', 'username', 'phone', 'password', 'confirm_password'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiếu các trường bắt buộc: ' . implode(', ', $missingFields));
}

// Sanitize input
$fullName = sanitizeInput($input['full_name']);
$username = sanitizeInput($input['username']);
$phone = sanitizeInput($input['phone']);
$password = $input['password'];
$confirmPassword = $input['confirm_password'];

// Validate input
if (strlen($fullName) < 2) {
    sendError('Họ và tên phải có ít nhất 2 ký tự');
}

if (strlen($username) < 3) {
    sendError('Tên đăng nhập phải có ít nhất 3 ký tự');
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    sendError('Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới');
}

if (!preg_match('/^[0-9]{9,12}$/', $phone)) {
    sendError('Số điện thoại phải từ 9-12 chữ số');
}

if (strlen($password) < 6) {
    sendError('Mật khẩu phải có ít nhất 6 ký tự');
}

if ($password !== $confirmPassword) {
    sendError('Mật khẩu xác nhận không khớp');
}

try {
    $db = Database::getInstance();
    
    // Check if username already exists
    $existingUser = $db->selectOne('users', ['username' => $username]);
    if ($existingUser) {
        sendError('Tên đăng nhập đã tồn tại');
    }
    
    // Check if phone already exists
    $existingPhone = $db->selectOne('users', ['phone' => $phone]);
    if ($existingPhone) {
        sendError('Số điện thoại đã được sử dụng');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $userData = [
        'full_name' => $fullName,
        'username' => $username,
        'phone' => $phone,
        'password' => $hashedPassword
    ];
    
    $userId = $db->insert('users', $userData);
    
    if ($userId) {
        // Return user data without password
        $newUser = [
            'id' => $userId,
            'full_name' => $fullName,
            'username' => $username,
            'phone' => $phone
        ];
        
        sendSuccess(['user' => $newUser], 'Đăng ký thành công');
    } else {
        sendError('Không thể tạo tài khoản', 500);
    }
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    
    // Check for specific database errors
    if ($e->getCode() == 23000) {
        if (strpos($e->getMessage(), 'username') !== false) {
            sendError('Tên đăng nhập đã tồn tại');
        } elseif (strpos($e->getMessage(), 'phone') !== false) {
            sendError('Số điện thoại đã được sử dụng');
        }
    }
    
    sendError('Lỗi hệ thống, vui lòng thử lại sau', 500);
} catch (Exception $e) {
    error_log("Unexpected registration error: " . $e->getMessage());
    sendError('Lỗi không xác định', 500);
}
?>