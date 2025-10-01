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
$requiredFields = ['full_name', 'username', 'phone', 'password', 'confirm_password'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiáº¿u cÃ¡c trÆ°á»ng báº¯t buá»™c: ' . implode(', ', $missingFields));
}

// Sanitize input
$fullName = sanitizeInput($input['full_name']);
$username = sanitizeInput($input['username']);
$phone = sanitizeInput($input['phone']);
$password = $input['password'];
$confirmPassword = $input['confirm_password'];

// Validate input
if (strlen($fullName) < 2) {
    sendError('Há» vÃ  tÃªn pháº£i cÃ³ Ã­t nháº¥t 2 kÃ½ tá»±');
}

if (strlen($username) < 3) {
    sendError('TÃªn Ä‘Äƒng nháº­p pháº£i cÃ³ Ã­t nháº¥t 3 kÃ½ tá»±');
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    sendError('TÃªn Ä‘Äƒng nháº­p chá»‰ Ä‘Æ°á»£c chá»©a chá»¯ cÃ¡i, sá»‘ vÃ  dáº¥u gáº¡ch dÆ°á»›i');
}

if (!preg_match('/^[0-9]{9,12}$/', $phone)) {
    sendError('Sá»‘ Ä‘iá»‡n thoáº¡i pháº£i tá»« 9-12 chá»¯ sá»‘');
}

if (strlen($password) < 6) {
    sendError('Máº­t kháº©u pháº£i cÃ³ Ã­t nháº¥t 6 kÃ½ tá»±');
}

if ($password !== $confirmPassword) {
    sendError('Máº­t kháº©u xÃ¡c nháº­n khÃ´ng khá»›p');
}

try {
    $db = Database::getInstance();
    
    // Check if username already exists
    $existingUser = $db->selectOne('users', ['username' => $username]);
    if ($existingUser) {
        sendError('TÃªn Ä‘Äƒng nháº­p Ä‘Ã£ tá»“n táº¡i');
    }
    
    // Check if phone already exists
    $existingPhone = $db->selectOne('users', ['phone' => $phone]);
    if ($existingPhone) {
        sendError('Sá»‘ Ä‘iá»‡n thoáº¡i Ä‘Ã£ Ä‘Æ°á»£c sá»­ dá»¥ng');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $userData = [
        'full_name' => $fullName,
        'username' => $username,
        'phone' => $phone,
        'password' => $hashedPassword
    ];
    
    $userId = $db->insert('users', $userData);
    
    if ($userId) {
        // Return user data without password
        $newUser = [
            'id' => $userId,
            'full_name' => $fullName,
            'username' => $username,
            'phone' => $phone
        ];
        
        sendSuccess(['user' => $newUser], 'ÄÄƒng kÃ½ thÃ nh cÃ´ng');
    } else {
        sendError('KhÃ´ng thá»ƒ táº¡o tÃ i khoáº£n', 500);
    }
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    
    // Check for specific database errors
    if ($e->getCode() == 23000) {
        if (strpos($e->getMessage(), 'username') !== false) {
            sendError('TÃªn Ä‘Äƒng nháº­p Ä‘Ã£ tá»“n táº¡i');
        } elseif (strpos($e->getMessage(), 'phone') !== false) {
            sendError('Sá»‘ Ä‘iá»‡n thoáº¡i Ä‘Ã£ Ä‘Æ°á»£c sá»­ dá»¥ng');
        }
    }
    
    sendError('Lá»—i há»‡ thá»‘ng, vui lÃ²ng thá»­ láº¡i sau', 500);
} catch (Exception $e) {
    error_log("Unexpected registration error: " . $e->getMessage());
    sendError('Lá»—i khÃ´ng xÃ¡c Ä‘á»‹nh', 500);
}
?>
