-- Migration: Remove description column from product_categories
-- Date: 2025-10-26
-- Reason: Description field not used in admin form, removed per user request

-- Check if column exists before dropping (safe migration)
SET @dbname = DATABASE();
SET @tablename = 'product_categories';
SET @columnname = 'description';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) 
   FROM INFORMATION_SCHEMA.COLUMNS
   WHERE TABLE_SCHEMA = @dbname
   AND TABLE_NAME = @tablename
   AND COLUMN_NAME = @columnname
  ) > 0,
  'ALTER TABLE product_categories DROP COLUMN description;',
  'SELECT "Column does not exist, no action needed.";'
));

PREPARE alterStatement FROM @preparedStatement;
EXECUTE alterStatement;
DEALLOCATE PREPARE alterStatement;

