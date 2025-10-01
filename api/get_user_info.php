<?php
require_once 'connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    sendError('Bạn cần đăng nhập.', 401);
}

$userId = (int)$_SESSION['user_id'];

try {
    $db = Database::getInstance();
    $user = $db->selectOne('users', ['id' => $userId], 'id, username, full_name, phone');
    
    if (!$user) {
        sendError('Không tìm thấy người dùng.', 404);
    }
    
    sendSuccess(['user' => $user]);
    
} catch (Exception $e) {
    error_log("Get User Info error: " . $e->getMessage());
    sendError('Lỗi hệ thống.', 500);
}
?>