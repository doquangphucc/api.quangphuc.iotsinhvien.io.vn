-- Cập nhật schema để cho phép updated_at NULL
-- Chạy các câu lệnh này để cập nhật database hiện tại

-- Sửa bảng tasks
ALTER TABLE tasks MODIFY COLUMN updated_at DATETIME DEFAULT NULL COMMENT 'Thời gian cập nhật lần cuối (NULL khi tạo mới)';
ALTER TABLE tasks MODIFY COLUMN scheduled_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến thực hiện (do user chọn)';
ALTER TABLE tasks MODIFY COLUMN scheduled_time TIME DEFAULT NULL COMMENT 'Giờ dự kiến thực hiện (do user chọn)';
ALTER TABLE tasks MODIFY COLUMN created_at DATETIME NOT NULL COMMENT 'Thời gian tạo record (chỉ set 1 lần)';

-- Sửa bảng wishes
ALTER TABLE wishes MODIFY COLUMN updated_at DATETIME DEFAULT NULL COMMENT 'Thời gian cập nhật lần cuối (NULL khi tạo mới)';
ALTER TABLE wishes MODIFY COLUMN target_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến mua (do user chọn)';
ALTER TABLE wishes MODIFY COLUMN created_at DATETIME NOT NULL COMMENT 'Thời gian tạo record (chỉ set 1 lần)';

-- Reset updated_at của các record hiện tại về NULL (tuỳ chọn)
-- UPDATE tasks SET updated_at = NULL WHERE created_at = updated_at;
-- UPDATE wishes SET updated_at = NULL WHERE created_at = updated_at;
