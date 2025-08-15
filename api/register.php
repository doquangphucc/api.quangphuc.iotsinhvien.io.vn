<?php
require_once __DIR__.'/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    $data = $_POST;
}

$username = trim($data['username'] ?? '');
$display_name = trim($data['display_name'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($username) || empty($display_name) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
    exit;
}

// Validate username format
if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới (3-50 ký tự)']);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
    exit;
}

try {
    $conn = db_get_connection();
    
    // Check if username already exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại']);
        exit;
    }
    $stmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare('INSERT INTO users (username, display_name, password, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('sss', $username, $display_name, $hashedPassword);
    
    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Đăng ký thành công',
            'user_id' => $userId
        ]);
    } else {
        throw new Exception('Database insert failed');
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
}
?>
