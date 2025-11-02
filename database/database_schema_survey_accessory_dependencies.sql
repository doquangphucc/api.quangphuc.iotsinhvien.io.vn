-- =====================================================
-- FILE TẠM: TẠO BẢNG SURVEY_ACCESSORY_DEPENDENCIES
-- =====================================================
-- File này dùng để import riêng bảng survey_accessory_dependencies
-- Bảng này đã có trong database_schema.sql (file chính)
-- 
-- Cách sử dụng:
-- 1. Nếu import từ đầu: Chỉ cần import database_schema.sql (bảng đã có trong đó)
-- 2. Nếu database đã tồn tại: Import file này để thêm bảng mới
-- =====================================================

-- Kiểm tra và tạo bảng nếu chưa tồn tại
CREATE TABLE IF NOT EXISTS survey_accessory_dependencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    accessory_config_id INT NOT NULL COMMENT 'ID cấu hình phụ kiện (từ survey_product_configs)',
    dependent_product_id INT NOT NULL COMMENT 'ID sản phẩm phụ thuộc (ví dụ: inverter ID, pin ID, tấm pin ID...)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (accessory_config_id) REFERENCES survey_product_configs(id) ON DELETE CASCADE,
    FOREIGN KEY (dependent_product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_accessory_dependency (accessory_config_id, dependent_product_id)
) COMMENT='Mapping phụ kiện với sản phẩm phụ thuộc - Chỉ hiển thị phụ kiện khi sản phẩm phụ thuộc được chọn';

-- =====================================================
-- HOÀN TẤT
-- =====================================================
-- Sau khi import file này, bảng survey_accessory_dependencies đã sẵn sàng sử dụng
-- Bảng này đã được tích hợp vào database_schema.sql (file chính) để import từ đầu

