-- =====================================================
-- Tăng độ dài cột voucher_code trong bảng orders
-- File: increase_voucher_code_length.sql
-- Description: Tăng độ dài cột voucher_code từ VARCHAR(50) lên VARCHAR(500) để hỗ trợ nhiều voucher codes
-- Usage: Import file này vào database hiện có
-- =====================================================

USE nangluongmattroi;

-- Tăng độ dài cột voucher_code trong bảng orders
ALTER TABLE orders 
MODIFY COLUMN voucher_code VARCHAR(500) DEFAULT NULL COMMENT 'Danh sách mã voucher (có thể nhiều mã, cách nhau bởi dấu phẩy)';

