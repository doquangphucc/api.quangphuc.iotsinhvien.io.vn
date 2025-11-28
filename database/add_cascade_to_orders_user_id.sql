-- =====================================================
-- CẬP NHẬT FOREIGN KEY CHO orders.user_id
-- Thêm ON DELETE CASCADE để tự động xóa orders khi xóa user
-- =====================================================

USE nangluongmattroi;

-- Tìm và xóa foreign key cũ (nếu có)
SET @constraint_name = (
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'nangluongmattroi'
    AND TABLE_NAME = 'orders'
    AND COLUMN_NAME = 'user_id'
    AND REFERENCED_TABLE_NAME = 'users'
    LIMIT 1
);

SET @sql = IFNULL(CONCAT('ALTER TABLE orders DROP FOREIGN KEY ', @constraint_name), 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Thêm lại foreign key với ON DELETE CASCADE
ALTER TABLE orders 
ADD CONSTRAINT orders_user_id_fk 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

