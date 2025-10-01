<?php
require_once 'connect.php';

// Test endpoint to check session status
$response = [
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_data' => $_SESSION,
    'cookie_data' => $_COOKIE,
    'server_info' => [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'HTTPS' => $_SERVER['HTTPS'] ?? 'off',
        'HTTP_X_FORWARDED_PROTO' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'none',
        'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'unknown',
        'HTTP_ORIGIN' => $_SERVER['HTTP_ORIGIN'] ?? 'none'
    ],
    'cookie_params' => session_get_cookie_params(),
    'php_version' => phpversion(),
    'session_save_path' => session_save_path(),
    'is_logged_in' => isLoggedIn(),
    'current_user_id' => getCurrentUserId()
];

sendSuccess($response, 'Session test');
?>

