-- =====================================================
-- ADD PRODUCT_IMAGES TABLE
-- File: add_product_images_table.sql
-- Description: Tạo bảng product_images để lưu nhiều ảnh cho mỗi sản phẩm
-- Usage: Import file này để thêm bảng product_images vào database hiện có
-- =====================================================

USE nangluongmattroi;

-- =====================================================
-- BẢNG PRODUCT_IMAGES (Ảnh sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL COMMENT 'ID sản phẩm',
    image_url VARCHAR(500) NOT NULL COMMENT 'Đường dẫn ảnh',
    display_order INT DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_images_product_id (product_id),
    INDEX idx_product_images_display_order (display_order)
) COMMENT='Lưu nhiều ảnh cho mỗi sản phẩm';

