# Hướng Dẫn Cấu Hình SMS OTP

## Tổng Quan

Hệ thống đã được tích hợp xác thực SMS OTP cho đăng ký tài khoản. Hệ thống hỗ trợ nhiều nhà cung cấp SMS:

- **BrandSMS** (brandsms.vn) - Khuyến nghị cho thị trường Việt Nam
- **ESMS** (esms.vn) - Phổ biến tại Việt Nam
- **Twilio** - Dịch vụ quốc tế
- **Test Mode** - Chế độ test không cần SMS thật (trả về OTP trong response)

## Cài Đặt Database

1. Import bảng mới vào database:
```sql
-- Chạy file database_schema.sql để tạo bảng phone_otp_codes
-- Hoặc chạy SQL sau:
```

Bảng `phone_otp_codes` đã được thêm vào `database_schema.sql`. Nếu database đã tồn tại, chạy:

```sql
USE nangluongmattroi;

CREATE TABLE IF NOT EXISTS phone_otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL COMMENT 'Số điện thoại cần xác thực',
    otp_code VARCHAR(6) NOT NULL COMMENT 'Mã OTP 6 chữ số',
    purpose ENUM('register', 'login', 'reset_password', 'change_phone') DEFAULT 'register',
    is_verified BOOLEAN DEFAULT FALSE,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 5,
    expires_at TIMESTAMP NOT NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_otp_code (otp_code),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_verified (is_verified)
);
```

## Cấu Hình SMS Provider

### 1. BrandSMS (Khuyến nghị)

1. Đăng ký tài khoản tại: https://brandsms.vn
2. Lấy API Key và Secret Key từ dashboard
3. Đăng ký Brand Name (ví dụ: HCECO)
4. Cập nhật file `api/sms_config.php`:

```php
define('SMS_PROVIDER', 'brandsms');
define('BRANDSMS_API_KEY', 'YOUR_API_KEY_HERE');
define('BRANDSMS_SECRET_KEY', 'YOUR_SECRET_KEY_HERE');
define('BRANDSMS_BRAND_NAME', 'HCECO');
```

### 2. ESMS

1. Đăng ký tài khoản tại: https://esms.vn
2. Lấy API Key và Secret Key
3. Cập nhật file `api/sms_config.php`:

```php
define('SMS_PROVIDER', 'esms');
define('ESMS_API_KEY', 'YOUR_API_KEY_HERE');
define('ESMS_SECRET_KEY', 'YOUR_SECRET_KEY_HERE');
define('ESMS_BRAND_NAME', 'HCECO');
```

### 3. Twilio

1. Đăng ký tài khoản tại: https://www.twilio.com
2. Lấy Account SID và Auth Token
3. Mua số điện thoại Twilio
4. Cập nhật file `api/sms_config.php`:

```php
define('SMS_PROVIDER', 'twilio');
define('TWILIO_ACCOUNT_SID', 'YOUR_ACCOUNT_SID');
define('TWILIO_AUTH_TOKEN', 'YOUR_AUTH_TOKEN');
define('TWILIO_PHONE_NUMBER', '+1234567890');
```

### 4. Test Mode (Không cần SMS thật)

Để test mà không cần cấu hình SMS provider:

```php
define('SMS_PROVIDER', 'custom'); // hoặc bất kỳ giá trị nào không phải 'brandsms', 'esms', 'twilio'
```

Trong test mode, OTP sẽ được log và trả về trong response để dễ test.

## Cấu Hình OTP

Các tham số OTP có thể tùy chỉnh trong `api/sms_config.php`:

```php
define('OTP_LENGTH', 6);              // Độ dài mã OTP (mặc định: 6)
define('OTP_EXPIRY_MINUTES', 10);     // Thời gian hết hạn (mặc định: 10 phút)
define('OTP_MAX_ATTEMPTS', 5);        // Số lần nhập sai tối đa (mặc định: 5)
define('OTP_RESEND_COOLDOWN', 60);    // Thời gian chờ giữa các lần gửi lại (mặc định: 60 giây)
```

## Flow Xác Thực

1. **User nhập số điện thoại** → Click "Gửi OTP"
2. **Hệ thống gửi SMS** → User nhận mã OTP 6 chữ số
3. **User nhập mã OTP** → Click "Xác thực"
4. **Hệ thống verify OTP** → Nếu đúng, đánh dấu số điện thoại đã xác thực
5. **User điền form đăng ký** → Submit (chỉ khi OTP đã verify)

## API Endpoints

### 1. Gửi OTP
```
POST /api/send_otp.php
Body: {
    "phone": "0988919868",
    "purpose": "register"
}
Response: {
    "success": true,
    "message": "Mã OTP đã được gửi",
    "data": {
        "expires_in": 600,
        "resend_after": 60
    }
}
```

### 2. Xác thực OTP
```
POST /api/verify_otp.php
Body: {
    "phone": "0988919868",
    "otp_code": "123456",
    "purpose": "register"
}
Response: {
    "success": true,
    "message": "Xác thực thành công",
    "data": {
        "verified": true,
        "phone": "0988919868",
        "purpose": "register"
    }
}
```

### 3. Đăng ký (đã cập nhật)
```
POST /api/register.php
Body: {
    "full_name": "Nguyễn Văn A",
    "username": "nguyenvana",
    "phone": "0988919868",
    "password": "password123",
    "confirm_password": "password123",
    "otp_verified": true
}
```

## Bảo Mật

- OTP chỉ có hiệu lực trong 10 phút
- Tối đa 5 lần nhập sai
- Cooldown 60 giây giữa các lần gửi lại
- OTP đã verify chỉ có hiệu lực trong 30 phút
- Tự động xóa OTP cũ sau khi verify hoặc hết hạn

## Troubleshooting

### SMS không gửi được

1. Kiểm tra API credentials trong `sms_config.php`
2. Kiểm tra log PHP: `error_log` sẽ ghi lại lỗi
3. Test với test mode trước
4. Kiểm tra số dư tài khoản SMS provider
5. Kiểm tra Brand Name đã được duyệt chưa

### OTP không verify được

1. Kiểm tra OTP chưa hết hạn (10 phút)
2. Kiểm tra chưa vượt quá số lần thử (5 lần)
3. Kiểm tra số điện thoại format đúng (0988919868)
4. Kiểm tra database connection

### Test Mode

Trong test mode, OTP sẽ được trả về trong response:
```json
{
    "success": true,
    "message": "Mã OTP đã được tạo (chế độ test)",
    "data": {
        "otp_code": "123456",
        "expires_in": 600
    }
}
```

## Lưu Ý

- **Production**: Không bao giờ trả về OTP trong response
- **Test Mode**: Chỉ dùng để development, không dùng production
- **Cost**: SMS có phí, nên cấu hình rate limiting phù hợp
- **Brand Name**: Phải đăng ký và được duyệt trước khi dùng

## Hỗ Trợ

Nếu gặp vấn đề, kiểm tra:
1. PHP error logs
2. Database connection
3. SMS provider dashboard
4. Network connectivity

