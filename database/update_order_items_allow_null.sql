-- Migration: Allow NULL for product_id in order_items table
-- This is needed to support virtual items from survey packages

-- Temporarily disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Modify product_id to allow NULL
ALTER TABLE order_items 
MODIFY COLUMN product_id INT NULL COMMENT 'NULL for virtual items from survey';

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Verify the change
DESCRIBE order_items;

