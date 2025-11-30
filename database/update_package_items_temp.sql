-- =====================================================
-- FILE TẠM: Cập nhật bảng package_items
-- Mô tả: Thêm các trường product_id, quantity, price_type vào bảng package_items
-- Sử dụng: Import file này vào database đang chạy trên hosting
-- =====================================================

USE nangluongmattroi;

-- Thêm các cột mới vào bảng package_items
ALTER TABLE package_items 
ADD COLUMN product_id INT DEFAULT NULL COMMENT 'ID sản phẩm (nếu chọn từ danh sách sản phẩm)' AFTER package_id,
ADD COLUMN quantity INT DEFAULT 1 COMMENT 'Số lượng sản phẩm' AFTER item_description,
ADD COLUMN price_type ENUM('market_price', 'category_price') DEFAULT 'market_price' COMMENT 'Loại giá sử dụng' AFTER quantity;

-- Thêm foreign key và index
ALTER TABLE package_items 
ADD CONSTRAINT fk_package_items_product_id 
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
ADD INDEX idx_package_items_product_id (product_id);

-- Cập nhật comment cho các cột cũ
ALTER TABLE package_items 
MODIFY COLUMN item_name VARCHAR(255) NOT NULL COMMENT 'Tên item (dùng khi product_id NULL)';

