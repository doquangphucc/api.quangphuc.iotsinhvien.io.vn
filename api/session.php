<?php
// Configure session before starting
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters
    $sessionLifetime = 86400; // 24 hours
    
    // Set explicit session name to avoid conflicts
    session_name('HCECO_SESSION');
    
    // Determine if we're on HTTPS
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    );
    
    // For localhost development
    $isLocalhost = (
        isset($_SERVER['HTTP_HOST']) && 
        (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
         strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)
    );
    
    // Set secure flag: false for localhost, true for HTTPS production
    $isSecure = $isHttps && !$isLocalhost;
    
    // Configure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', $isSecure ? 1 : 0);
    
    // Try to set session save path to avoid isolation issues
    // If hosting has session isolation enabled, this might be ignored
    $sessionPath = sys_get_temp_dir();
    if (is_writable($sessionPath)) {
        ini_set('session.save_path', $sessionPath);
    }
    
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'domain' => '', // Empty string lets browser handle it
        'secure' => $isSecure,
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
        sendError('Báº¡n cáº§n Ä‘Äƒng nháº­p Ä‘á»ƒ thá»±c hiá»‡n hÃ nh Ä‘á»™ng nÃ y.', 401);
    }
}
?>

