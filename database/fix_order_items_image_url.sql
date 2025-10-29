-- Script để kiểm tra và fix cột image_url trong bảng order_items
-- Chạy script này trong phpMyAdmin để đảm bảo cột image_url tồn tại và có kiểu dữ liệu đúng

-- Bước 1: Kiểm tra xem cột image_url có tồn tại không
-- DESCRIBE order_items;

-- Bước 2: Thêm cột image_url (ignore nếu đã có)
ALTER TABLE order_items ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) NULL AFTER price;

-- Nếu MySQL version < 8.0 (không hỗ trợ IF NOT EXISTS), chạy câu này:
-- ALTER TABLE order_items ADD COLUMN image_url VARCHAR(500) NULL AFTER price;

-- Bước 3: Nếu cột đã tồn tại nhưng kiểu dữ liệu sai (DATETIME), sửa lại
ALTER TABLE order_items MODIFY COLUMN image_url VARCHAR(500) NULL;

-- Bước 4: Kiểm tra lại cấu trúc
-- DESCRIBE order_items;

