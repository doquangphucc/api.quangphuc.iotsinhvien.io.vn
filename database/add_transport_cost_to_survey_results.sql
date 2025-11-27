-- =====================================================
-- THÊM TRƯỜNG transport_cost VÀO BẢNG survey_results
-- File: add_transport_cost_to_survey_results.sql
-- Description: Thêm cột transport_cost để lưu chi phí vận chuyển thiết bị
-- Usage: Import file này vào database hiện có trên hosting
-- =====================================================

USE nangluongmattroi;

-- Thêm cột transport_cost vào bảng survey_results
ALTER TABLE survey_results 
ADD COLUMN transport_cost DECIMAL(15, 2) DEFAULT 0 COMMENT 'Chi phí vận chuyển thiết bị' 
AFTER labor_cost;

-- =====================================================
-- HOÀN THÀNH
-- =====================================================

