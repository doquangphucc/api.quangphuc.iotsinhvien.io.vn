<?php
require_once 'connect.php';

requireAuth();



$userId = getCurrentUserId();

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
