-- =====================================================
-- HC ECO SYSTEM - ADD PHONE OTP TABLE
-- File: add_phone_otp_table_no_use.sql
-- Description: Tạo bảng phone_otp_codes cho xác thực SMS OTP
-- Usage: Import file này vào phpMyAdmin (chọn database nangluongmattroi trước)
-- =====================================================

-- =====================================================
-- BẢNG PHONE_OTP_CODES (Mã OTP xác thực số điện thoại)
-- =====================================================
CREATE TABLE IF NOT EXISTS phone_otp_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL COMMENT 'Số điện thoại cần xác thực',
    otp_code VARCHAR(6) NOT NULL COMMENT 'Mã OTP 6 chữ số',
    purpose ENUM('register', 'login', 'reset_password', 'change_phone') DEFAULT 'register' COMMENT 'Mục đích sử dụng OTP',
    is_verified BOOLEAN DEFAULT FALSE COMMENT 'Đã xác thực thành công',
    attempts INT DEFAULT 0 COMMENT 'Số lần nhập sai',
    max_attempts INT DEFAULT 5 COMMENT 'Số lần nhập sai tối đa',
    expires_at TIMESTAMP NOT NULL COMMENT 'Thời gian hết hạn (thường là 5-10 phút)',
    verified_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Thời gian xác thực thành công',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_otp_code (otp_code),
    INDEX idx_expires_at (expires_at),
    INDEX idx_is_verified (is_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Lưu mã OTP để xác thực số điện thoại';

-- =====================================================
-- HOÀN THÀNH
-- =====================================================
SELECT 'Bảng phone_otp_codes đã được tạo thành công!' as message;

