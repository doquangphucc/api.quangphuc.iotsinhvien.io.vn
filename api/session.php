<?php
// Configure session before starting
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters
    $sessionLifetime = 86400; // 24 hours
    
    // Configure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    
    // For development with localhost
    if (isset($_SERVER['HTTP_HOST']) && 
        (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
         strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
        ini_set('session.cookie_secure', 0); // Allow HTTP for localhost
    } else {
        ini_set('session.cookie_secure', 1); // Require HTTPS for production
    }
    
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'domain' => '', // Let browser handle domain
        'secure' => false, // Set to true in production with HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to get current user ID
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

// Helper function to require authentication
function requireAuth() {
    if (!isLoggedIn()) {
        sendError('Bạn cần đăng nhập để thực hiện hành động này.', 401);
    }
}
?>
