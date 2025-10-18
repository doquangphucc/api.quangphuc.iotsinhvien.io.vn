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
?>
