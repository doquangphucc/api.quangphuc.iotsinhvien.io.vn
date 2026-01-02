<?php
require_once 'connect.php';
require_once __DIR__ . '/helpers/audit_logger.php';

// Allow both GET and POST requests for logout
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// Audit log before destroying session
AuditLogger::logLogout();

// Destroy session
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

sendSuccess([], 'Đăng xuất thành công');
?>