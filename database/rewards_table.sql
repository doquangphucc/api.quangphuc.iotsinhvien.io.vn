-- Tạo bảng để lưu phần thưởng từ vòng quay may mắn
CREATE TABLE IF NOT EXISTS lottery_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_name VARCHAR(255) NOT NULL,
    reward_type VARCHAR(50) NOT NULL COMMENT 'voucher, discount, gift, etc',
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

-- Thêm một số mẫu dữ liệu test (có thể xóa sau)
-- INSERT INTO lottery_rewards (user_id, reward_name, reward_type, reward_value, reward_code, status) 
-- VALUES 
-- (1, 'Giảm giá 10%', 'discount', '10%', 'LUCKY10', 'pending'),
-- (1, 'Voucher 50.000đ', 'voucher', '50000', 'VOUCHER50K', 'pending');

