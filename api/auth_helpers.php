<?php
// Authentication helper functions
// Include this file only when authentication is needed

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        sendError('Vui lòng đăng nhập để tiếp tục', 401);
    }
}

function getCurrentUserId() {
    if (!isset($_SESSION['user_id'])) {
        sendError('Vui lòng đăng nhập để tiếp tục', 401);
    }
    return $_SESSION['user_id'];
}

function is_admin() {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user && $user['is_admin'];
}
?>
