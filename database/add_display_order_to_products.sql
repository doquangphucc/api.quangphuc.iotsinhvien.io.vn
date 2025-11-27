-- =====================================================
-- THÊM TRƯỜNG display_order VÀO BẢNG products
-- File: add_display_order_to_products.sql
-- Description: Thêm cột display_order để cấu hình thứ tự hiển thị sản phẩm
-- Usage: Import file này vào database hiện có trên hosting
-- =====================================================

USE nangluongmattroi;

-- Thêm cột display_order vào bảng products
ALTER TABLE products 
ADD COLUMN display_order INT DEFAULT 0 COMMENT 'Thứ tự hiển thị sản phẩm' 
AFTER is_active;

-- Cập nhật giá trị mặc định: gán display_order = id cho các sản phẩm hiện có
-- Điều này đảm bảo thứ tự hiện tại được giữ nguyên
UPDATE products 
SET display_order = id 
WHERE display_order = 0 OR display_order IS NULL;

-- Thêm index để tối ưu hiệu suất khi sắp xếp
CREATE INDEX idx_products_display_order ON products(display_order);

-- =====================================================
-- HOÀN THÀNH
-- =====================================================

