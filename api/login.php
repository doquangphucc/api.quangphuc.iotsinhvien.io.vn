<?php
require_once 'connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('PhÆ°Æ¡ng thá»©c khÃ´ng Ä‘Æ°á»£c há»— trá»£', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dá»¯ liá»‡u JSON khÃ´ng há»£p lá»‡');
}

// Validate required fields
$requiredFields = ['username', 'password'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiáº¿u cÃ¡c trÆ°á»ng báº¯t buá»™c: ' . implode(', ', $missingFields));
}

// Sanitize input
$username = sanitizeInput($input['username']);
$password = $input['password'];

// Validate input
if (strlen($username) < 3) {
    sendError('TÃªn Ä‘Äƒng nháº­p khÃ´ng há»£p lá»‡');
}

if (strlen($password) < 6) {
    sendError('Máº­t kháº©u khÃ´ng há»£p lá»‡');
}

try {
    $db = Database::getInstance();
    
    // Find user by username
    $user = $db->selectOne('users', ['username' => $username]);
    
    if (!$user) {
        sendError('TÃªn Ä‘Äƒng nháº­p hoáº·c máº­t kháº©u khÃ´ng Ä‘Ãºng');
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendError('TÃªn Ä‘Äƒng nháº­p hoáº·c máº­t kháº©u khÃ´ng Ä‘Ãºng');
    }
    
    // Set session for logged in user
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    
    // Debug logging
    error_log("Login Success - Session ID: " . session_id());
    error_log("Login Success - User ID set: " . $_SESSION['user_id']);
    error_log("Login Success - Session Data: " . print_r($_SESSION, true));
    
    // Remove password from response
    unset($user['password']);
    
    // Return user data with session info for debugging
    sendSuccess([
        'user' => $user,
        'session_id' => session_id(),
        'debug' => [
            'session_name' => session_name(),
            'cookie_params' => session_get_cookie_params()
        ]
    ], 'ÄÄƒng nháº­p thÃ nh cÃ´ng');
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    sendError('Lá»—i há»‡ thá»‘ng, vui lÃ²ng thá»­ láº¡i sau', 500);
} catch (Exception $e) {
    error_log("Unexpected login error: " . $e->getMessage());
    sendError('Lá»—i khÃ´ng xÃ¡c Ä‘á»‹nh', 500);
}
?>
