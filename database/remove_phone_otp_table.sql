-- =====================================================
-- HC ECO SYSTEM - REMOVE PHONE OTP TABLE
-- File: remove_phone_otp_table.sql
-- Description: Xóa bảng phone_otp_codes (nếu đã import trước đó)
-- Usage: Import file này vào phpMyAdmin để xóa bảng
-- =====================================================

USE nangluongmattroi;

-- =====================================================
-- XÓA BẢNG PHONE_OTP_CODES
-- =====================================================
DROP TABLE IF EXISTS phone_otp_codes;

-- =====================================================
-- HOÀN THÀNH
-- =====================================================
SELECT 'Bảng phone_otp_codes đã được xóa thành công!' as message;

