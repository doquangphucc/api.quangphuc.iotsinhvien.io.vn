<?php
/**
 * SMS Service Configuration
 * Hỗ trợ nhiều provider: BrandSMS, ESMS, Twilio, v.v.
 */

// Chọn provider: 'brandsms', 'esms', 'twilio', 'custom'
// Mặc định: 'custom' (test mode - không cần SMS thật, OTP sẽ được log và trả về trong response)
// Để dùng SMS thật, đổi thành 'brandsms', 'esms', hoặc 'twilio' và cấu hình credentials bên dưới
define('SMS_PROVIDER', 'custom'); // Thay đổi theo provider bạn sử dụng

// =====================================================
// BRAND SMS CONFIGURATION (brandsms.vn)
// =====================================================
define('BRANDSMS_API_KEY', 'YOUR_API_KEY_HERE');
define('BRANDSMS_SECRET_KEY', 'YOUR_SECRET_KEY_HERE');
define('BRANDSMS_BRAND_NAME', 'HCECO'); // Tên brand đã đăng ký
define('BRANDSMS_API_URL', 'https://api.brandsms.vn/api/send');

// =====================================================
// ESMS CONFIGURATION (esms.vn)
// =====================================================
define('ESMS_API_KEY', 'YOUR_API_KEY_HERE');
define('ESMS_SECRET_KEY', 'YOUR_SECRET_KEY_HERE');
define('ESMS_BRAND_NAME', 'HCECO');
define('ESMS_API_URL', 'https://rest.esms.vn/MainService.svc/json/SendMultipleMessage_V4_post_json');

// =====================================================
// TWILIO CONFIGURATION
// =====================================================
define('TWILIO_ACCOUNT_SID', 'YOUR_ACCOUNT_SID');
define('TWILIO_AUTH_TOKEN', 'YOUR_AUTH_TOKEN');
define('TWILIO_PHONE_NUMBER', '+1234567890'); // Số điện thoại Twilio

// =====================================================
// OTP SETTINGS
// =====================================================
define('OTP_LENGTH', 6); // Độ dài mã OTP
define('OTP_EXPIRY_MINUTES', 10); // Thời gian hết hạn (phút)
define('OTP_MAX_ATTEMPTS', 5); // Số lần nhập sai tối đa
define('OTP_RESEND_COOLDOWN', 60); // Thời gian chờ giữa các lần gửi lại (giây)

/**
 * Gửi SMS OTP
 * @param string $phone Số điện thoại (format: 0988919868)
 * @param string $otpCode Mã OTP 6 chữ số
 * @param string $purpose Mục đích: 'register', 'login', 'reset_password', 'change_phone'
 * @return array ['success' => bool, 'message' => string]
 */
function sendSMSOTP($phone, $otpCode, $purpose = 'register') {
    $provider = SMS_PROVIDER;
    
    // Format phone number (đảm bảo có mã quốc gia nếu cần)
    $formattedPhone = formatPhoneForSMS($phone);
    
    // Tạo nội dung SMS
    $message = generateOTPMessage($otpCode, $purpose);
    
    try {
        switch ($provider) {
            case 'brandsms':
                return sendViaBrandSMS($formattedPhone, $message);
            
            case 'esms':
                return sendViaESMS($formattedPhone, $message);
            
            case 'twilio':
                return sendViaTwilio($formattedPhone, $message);
            
            default:
                // Fallback: Log và trả về success (để test không cần SMS thật)
                error_log("SMS OTP (TEST MODE): Phone: {$phone}, OTP: {$otpCode}, Message: {$message}");
                return [
                    'success' => true,
                    'message' => 'OTP sent (test mode)',
                    'otp_code' => $otpCode // Trả về OTP trong test mode để dễ test
                ];
        }
    } catch (Exception $e) {
        error_log("SMS sending error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Không thể gửi SMS. Vui lòng thử lại sau.'
        ];
    }
}

/**
 * Format số điện thoại cho SMS
 */
function formatPhoneForSMS($phone) {
    // Loại bỏ khoảng trắng và ký tự đặc biệt
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Nếu bắt đầu bằng 0, thay bằng +84
    if (substr($phone, 0, 1) === '0') {
        $phone = '+84' . substr($phone, 1);
    } elseif (substr($phone, 0, 2) !== '84') {
        $phone = '+84' . $phone;
    } else {
        $phone = '+' . $phone;
    }
    
    return $phone;
}

/**
 * Tạo nội dung tin nhắn OTP
 */
function generateOTPMessage($otpCode, $purpose) {
    $messages = [
        'register' => "Ma xac thuc dang ky HC Eco System cua ban la: {$otpCode}. Ma co hieu luc trong 10 phut. Khong chia se ma nay voi ai.",
        'login' => "Ma xac thuc dang nhap HC Eco System cua ban la: {$otpCode}. Ma co hieu luc trong 10 phut.",
        'reset_password' => "Ma xac thuc dat lai mat khau HC Eco System cua ban la: {$otpCode}. Ma co hieu luc trong 10 phut.",
        'change_phone' => "Ma xac thuc doi so dien thoai HC Eco System cua ban la: {$otpCode}. Ma co hieu luc trong 10 phut."
    ];
    
    return $messages[$purpose] ?? $messages['register'];
}

/**
 * Gửi SMS qua BrandSMS
 */
function sendViaBrandSMS($phone, $message) {
    $apiKey = BRANDSMS_API_KEY;
    $secretKey = BRANDSMS_SECRET_KEY;
    $brandName = BRANDSMS_BRAND_NAME;
    $apiUrl = BRANDSMS_API_URL;
    
    // Tạo signature
    $timestamp = time();
    $signature = md5($apiKey . $secretKey . $timestamp);
    
    $data = [
        'ApiKey' => $apiKey,
        'SecretKey' => $secretKey,
        'Phone' => $phone,
        'Content' => $message,
        'BrandName' => $brandName,
        'TimeStamp' => $timestamp,
        'Signature' => $signature
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['CodeResult']) && $result['CodeResult'] === '100') {
            return ['success' => true, 'message' => 'SMS sent successfully'];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to send SMS'];
}

/**
 * Gửi SMS qua ESMS
 */
function sendViaESMS($phone, $message) {
    $apiKey = ESMS_API_KEY;
    $secretKey = ESMS_SECRET_KEY;
    $brandName = ESMS_BRAND_NAME;
    $apiUrl = ESMS_API_URL;
    
    // ESMS yêu cầu format đặc biệt
    $phone = str_replace('+84', '84', $phone);
    
    $data = [
        'ApiKey' => $apiKey,
        'SecretKey' => $secretKey,
        'Phone' => $phone,
        'Content' => $message,
        'Brandname' => $brandName,
        'SmsType' => 2 // 2 = Brandname
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['CodeResult']) && $result['CodeResult'] === '100') {
            return ['success' => true, 'message' => 'SMS sent successfully'];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to send SMS'];
}

/**
 * Gửi SMS qua Twilio
 */
function sendViaTwilio($phone, $message) {
    $accountSid = TWILIO_ACCOUNT_SID;
    $authToken = TWILIO_AUTH_TOKEN;
    $fromNumber = TWILIO_PHONE_NUMBER;
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
    
    $data = [
        'From' => $fromNumber,
        'To' => $phone,
        'Body' => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "{$accountSid}:{$authToken}");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201) {
        return ['success' => true, 'message' => 'SMS sent successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to send SMS'];
}

/**
 * Tạo mã OTP ngẫu nhiên
 */
function generateOTPCode($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}
?>

