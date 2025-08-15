-- Cập nhật schema để updated_at NOT NULL
-- Chỉnh logic: created_at và updated_at đều được set khi tạo mới, chỉ updated_at thay đổi khi edit

-- Sửa bảng tasks
ALTER TABLE tasks MODIFY COLUMN updated_at DATETIME NOT NULL COMMENT 'Thời gian cập nhật lần cuối (cùng lúc với created_at khi tạo mới)';
ALTER TABLE tasks MODIFY COLUMN scheduled_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến thực hiện (do user chọn)';
ALTER TABLE tasks MODIFY COLUMN scheduled_time TIME DEFAULT NULL COMMENT 'Giờ dự kiến thực hiện (do user chọn)';
ALTER TABLE tasks MODIFY COLUMN created_at DATETIME NOT NULL COMMENT 'Thời gian tạo record (chỉ set 1 lần)';

-- Sửa bảng wishes
ALTER TABLE wishes MODIFY COLUMN updated_at DATETIME NOT NULL COMMENT 'Thời gian cập nhật lần cuối (cùng lúc với created_at khi tạo mới)';
ALTER TABLE wishes MODIFY COLUMN target_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến mua (do user chọn)';
ALTER TABLE wishes MODIFY COLUMN created_at DATETIME NOT NULL COMMENT 'Thời gian tạo record (chỉ set 1 lần)';

-- Cập nhật updated_at cho các record có updated_at = NULL (nếu có)
UPDATE tasks SET updated_at = created_at WHERE updated_at IS NULL;
UPDATE wishes SET updated_at = created_at WHERE updated_at IS NULL;
