-- Migration script to update existing database schema

-- Add scheduled_date and scheduled_time columns to tasks table
ALTER TABLE tasks 
ADD COLUMN scheduled_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến thực hiện (do user chọn)' AFTER user_id,
ADD COLUMN scheduled_time TIME DEFAULT NULL COMMENT 'Giờ dự kiến thực hiện (do user chọn)' AFTER scheduled_date;

-- Add index for new scheduled_date column in tasks
ALTER TABLE tasks ADD INDEX idx_scheduled_date (scheduled_date);

-- Add scheduled_date and scheduled_time columns to wishes table
ALTER TABLE wishes 
ADD COLUMN scheduled_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến mua (do user chọn)' AFTER user_id,
ADD COLUMN scheduled_time TIME DEFAULT NULL COMMENT 'Giờ dự kiến mua (do user chọn)' AFTER scheduled_date;

-- Add index for new scheduled_date column in wishes
ALTER TABLE wishes ADD INDEX idx_scheduled_date (scheduled_date);