-- =====================================================
-- PATCH: Thêm bảng promotions để cấu hình khuyến mãi
-- Mục đích: Dễ import nhanh trên hosting hiện tại
-- Hướng dẫn: chạy file này SAU khi đã import database_schema.sql
-- =====================================================

USE nangluongmattroi;

-- Tạo bảng promotions nếu chưa có
CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề khuyến mãi hiển thị trong trang quản trị',
    image_url VARCHAR(500) DEFAULT NULL COMMENT 'Ảnh banner khuyến mãi',
    target_link VARCHAR(500) DEFAULT NULL COMMENT 'Trang đích khi người dùng click',
    target_pages TEXT NOT NULL COMMENT 'JSON array các trang sẽ hiển thị banner (VD: ["index.html","html/pricing.html"])',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Bật/tắt hiển thị',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT='Quản lý các banner khuyến mãi hiển thị nổi ở giữa trang';

-- Thêm dữ liệu mẫu (chưa có ảnh để admin cập nhật sau)
INSERT INTO promotions (title, image_url, target_link, target_pages, is_active, created_at, updated_at)
VALUES (
    'Ưu đãi Năng Lượng Sạch', 
    NULL,
    'html/pricing.html',
    '["index.html","html/pricing.html"]',
    1,
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at);

SELECT 'Patch promotions_patch.sql executed successfully' AS status;

