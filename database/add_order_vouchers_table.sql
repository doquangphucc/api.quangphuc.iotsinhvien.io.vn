-- =====================================================
-- ADD ORDER_VOUCHERS TABLE
-- File: add_order_vouchers_table.sql
-- Description: Thêm bảng order_vouchers để hỗ trợ nhiều vouchers cho 1 đơn hàng
-- Usage: Import file này để thêm bảng order_vouchers
-- =====================================================

USE nangluongmattroi;

-- =====================================================
-- BẢNG ORDER_VOUCHERS (Vouchers đã dùng cho đơn hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS order_vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    voucher_id INT NOT NULL,
    voucher_code VARCHAR(50) NOT NULL COMMENT 'Mã voucher',
    discount_amount DECIMAL(15, 2) NOT NULL COMMENT 'Số tiền giảm',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id)
) COMMENT='Lưu trữ nhiều vouchers cho 1 đơn hàng';

CREATE INDEX idx_order_vouchers_order ON order_vouchers(order_id);
CREATE INDEX idx_order_vouchers_voucher ON order_vouchers(voucher_id);

SELECT 'Bảng order_vouchers đã được tạo thành công!' as message;
SELECT 'Giờ có thể sử dụng nhiều vouchers cho 1 đơn hàng' as info;

