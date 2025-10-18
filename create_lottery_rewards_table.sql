-- Script tạo bảng lottery_rewards
-- Chạy script này trong phpMyAdmin

USE nangluongmattroi;

-- Xóa bảng cũ nếu tồn tại (cẩn thận!)
-- DROP TABLE IF EXISTS lottery_rewards;

-- Tạo bảng lottery_rewards
CREATE TABLE IF NOT EXISTS lottery_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_name VARCHAR(255) NOT NULL,
    reward_type VARCHAR(50) NOT NULL COMMENT 'discount, free_shipping, accessory, gift, no_prize',
    reward_value VARCHAR(100) DEFAULT NULL COMMENT 'Giá trị phần thưởng (%, tiền, mô tả)',
    reward_code VARCHAR(50) DEFAULT NULL COMMENT 'Mã voucher/gift code nếu có',
    reward_image VARCHAR(255) DEFAULT NULL COMMENT 'Hình ảnh phần thưởng',
    status ENUM('pending', 'used', 'expired') DEFAULT 'pending',
    ticket_id INT DEFAULT NULL COMMENT 'ID của vé số đã sử dụng',
    won_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES lottery_tickets(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_won_at (won_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kiểm tra bảng đã được tạo
SELECT 'lottery_rewards table created successfully' as message;

-- Hiển thị cấu trúc bảng
DESCRIBE lottery_rewards;
