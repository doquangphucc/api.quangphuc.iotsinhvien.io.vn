<?php
/**
 * API: Xác thực mã OTP
 * POST /api/verify_otp.php
 * Body: { "phone": "0988919868", "otp_code": "123456", "purpose": "register" }
 */

require_once 'connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Phương thức không được hỗ trợ', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Dữ liệu JSON không hợp lệ');
}

// Validate required fields
$requiredFields = ['phone', 'otp_code'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiếu các trường bắt buộc: ' . implode(', ', $missingFields));
}

// Sanitize input
$phone = sanitizeInput($input['phone']);
$otpCode = sanitizeInput($input['otp_code']);
$purpose = isset($input['purpose']) ? sanitizeInput($input['purpose']) : 'register';

// Validate phone number
if (!preg_match('/^0[0-9]{9}$/', $phone)) {
    sendError('Số điện thoại không hợp lệ');
}

// Validate OTP code (6 chữ số)
if (!preg_match('/^[0-9]{6}$/', $otpCode)) {
    sendError('Mã OTP phải có đúng 6 chữ số');
}

// Validate purpose
$allowedPurposes = ['register', 'login', 'reset_password', 'change_phone'];
if (!in_array($purpose, $allowedPurposes)) {
    sendError('Mục đích không hợp lệ');
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Tìm OTP code chưa verify, chưa hết hạn, và chưa vượt quá số lần thử
    $stmt = $pdo->prepare("
        SELECT id, otp_code, attempts, max_attempts, expires_at, is_verified
        FROM phone_otp_codes 
        WHERE phone = ? 
        AND purpose = ? 
        AND is_verified = 0
        AND expires_at > NOW()
        AND attempts < max_attempts
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$phone, $purpose]);
    $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$otpRecord) {
        sendError('Mã OTP không hợp lệ hoặc đã hết hạn. Vui lòng gửi lại mã mới.');
    }
    
    // Kiểm tra số lần thử
    if ($otpRecord['attempts'] >= $otpRecord['max_attempts']) {
        sendError('Bạn đã nhập sai quá nhiều lần. Vui lòng gửi lại mã OTP mới.');
    }
    
    // Tăng số lần thử
    $newAttempts = $otpRecord['attempts'] + 1;
    $updateStmt = $pdo->prepare("
        UPDATE phone_otp_codes 
        SET attempts = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$newAttempts, $otpRecord['id']]);
    
    // Kiểm tra mã OTP
    if ($otpRecord['otp_code'] !== $otpCode) {
        $remainingAttempts = $otpRecord['max_attempts'] - $newAttempts;
        
        if ($remainingAttempts <= 0) {
            sendError('Bạn đã nhập sai quá nhiều lần. Vui lòng gửi lại mã OTP mới.');
        } else {
            sendError("Mã OTP không đúng. Bạn còn {$remainingAttempts} lần thử.");
        }
    }
    
    // Xác thực thành công - đánh dấu đã verify
    $verifyStmt = $pdo->prepare("
        UPDATE phone_otp_codes 
        SET is_verified = 1, verified_at = NOW() 
        WHERE id = ?
    ");
    $verifyStmt->execute([$otpRecord['id']]);
    
    // Xóa các OTP cũ của số điện thoại này (đã verify hoặc hết hạn)
    $pdo->exec("
        DELETE FROM phone_otp_codes 
        WHERE phone = '{$phone}' 
        AND purpose = '{$purpose}' 
        AND (id != {$otpRecord['id']} OR expires_at < NOW())
    ");
    
    sendSuccess([
        'verified' => true,
        'phone' => $phone,
        'purpose' => $purpose
    ], 'Xác thực thành công');
    
} catch (PDOException $e) {
    error_log("Verify OTP error: " . $e->getMessage());
    sendError('Lỗi hệ thống, vui lòng thử lại sau', 500);
} catch (Exception $e) {
    error_log("Unexpected verify OTP error: " . $e->getMessage());
    sendError('Lỗi không xác định', 500);
}
?>

