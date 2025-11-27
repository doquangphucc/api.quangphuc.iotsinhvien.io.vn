-- =====================================================
-- Thêm cột inverter_quantity và inverter_total_price vào bảng survey_results
-- File: add_inverter_quantity_to_survey_results.sql
-- Description: Thêm số lượng biến tần và tổng giá biến tần để hỗ trợ nhiều bộ biến tần
-- Usage: Import file này vào database hiện có
-- =====================================================

USE nangluongmattroi;

-- Thêm cột inverter_quantity
ALTER TABLE survey_results 
ADD COLUMN inverter_quantity INT NOT NULL DEFAULT 1 COMMENT 'Số lượng biến tần cần thiết'
AFTER inverter_price;

-- Thêm cột inverter_total_price
ALTER TABLE survey_results 
ADD COLUMN inverter_total_price DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Tổng giá biến tần (số lượng × đơn giá)'
AFTER inverter_quantity;

-- Cập nhật giá trị mặc định: inverter_total_price = inverter_price * inverter_quantity cho các record hiện có
UPDATE survey_results 
SET inverter_total_price = inverter_price * inverter_quantity 
WHERE inverter_total_price = 0 OR inverter_total_price IS NULL;

