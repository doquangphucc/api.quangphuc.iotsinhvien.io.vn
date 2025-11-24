<?php
/**
 * API: Gửi mã OTP qua SMS
 * POST /api/send_otp.php
 * Body: { "phone": "0988919868", "purpose": "register" }
 */

require_once 'connect.php';
require_once 'sms_config.php';

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
$requiredFields = ['phone'];
$missingFields = validateRequired($input, $requiredFields);

if (!empty($missingFields)) {
    sendError('Thiếu các trường bắt buộc: ' . implode(', ', $missingFields));
}

// Sanitize input
$phone = sanitizeInput($input['phone']);
$purpose = isset($input['purpose']) ? sanitizeInput($input['purpose']) : 'register';

// Validate phone number
if (!preg_match('/^0[0-9]{9}$/', $phone)) {
    sendError('Số điện thoại phải có đúng 10 số và bắt đầu bằng số 0');
}

// Validate purpose
$allowedPurposes = ['register', 'login', 'reset_password', 'change_phone'];
if (!in_array($purpose, $allowedPurposes)) {
    sendError('Mục đích không hợp lệ');
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Kiểm tra cooldown (tránh spam)
    $cooldownTime = date('Y-m-d H:i:s', time() - OTP_RESEND_COOLDOWN);
    $stmt = $pdo->prepare("
        SELECT id, created_at 
        FROM phone_otp_codes 
        WHERE phone = ? 
        AND purpose = ? 
        AND created_at > ? 
        AND is_verified = 0
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$phone, $purpose, $cooldownTime]);
    $recentOTP = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($recentOTP) {
        $secondsLeft = OTP_RESEND_COOLDOWN - (time() - strtotime($recentOTP['created_at']));
        sendError("Vui lòng đợi {$secondsLeft} giây trước khi gửi lại mã OTP", 429);
    }
    
    // Tạo mã OTP mới
    $otpCode = generateOTPCode(OTP_LENGTH);
    $expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));
    
    // Lưu OTP vào database
    $stmt = $pdo->prepare("
        INSERT INTO phone_otp_codes (phone, otp_code, purpose, expires_at, max_attempts) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$phone, $otpCode, $purpose, $expiresAt, OTP_MAX_ATTEMPTS]);
    
    // Gửi SMS
    $smsResult = sendSMSOTP($phone, $otpCode, $purpose);
    
    if (!$smsResult['success']) {
        // Nếu gửi SMS thất bại, vẫn trả về success trong test mode
        // Nhưng log lại để debug
        error_log("SMS sending failed for phone: {$phone}, OTP: {$otpCode}");
        
        // Trong test mode, trả về OTP để dễ test
        if (SMS_PROVIDER === 'custom' || isset($smsResult['otp_code'])) {
            sendSuccess([
                'message' => 'Mã OTP đã được tạo (chế độ test)',
                'otp_code' => $otpCode, // Chỉ trả về trong test mode
                'expires_in' => OTP_EXPIRY_MINUTES * 60
            ], 'Mã OTP đã được gửi');
        } else {
            sendError($smsResult['message'] ?? 'Không thể gửi SMS. Vui lòng thử lại sau.', 500);
        }
    }
    
    // Xóa các OTP cũ đã hết hạn hoặc đã verify
    $pdo->exec("
        DELETE FROM phone_otp_codes 
        WHERE (expires_at < NOW() OR is_verified = 1) 
        AND phone = '{$phone}' 
        AND purpose = '{$purpose}'
    ");
    
    // Trả về thành công (không trả về OTP trong production)
    sendSuccess([
        'message' => 'Mã OTP đã được gửi đến số điện thoại của bạn',
        'expires_in' => OTP_EXPIRY_MINUTES * 60,
        'resend_after' => OTP_RESEND_COOLDOWN
    ], 'Mã OTP đã được gửi');
    
} catch (PDOException $e) {
    error_log("Send OTP error: " . $e->getMessage());
    sendError('Lỗi hệ thống, vui lòng thử lại sau', 500);
} catch (Exception $e) {
    error_log("Unexpected send OTP error: " . $e->getMessage());
    sendError('Lỗi không xác định', 500);
}
?>

