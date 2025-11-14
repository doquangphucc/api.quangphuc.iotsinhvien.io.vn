-- Temporary import file for wheel_prizes
USE nangluongmattroi;

-- Create wheel_prizes table if not exists
CREATE TABLE IF NOT EXISTS wheel_prizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prize_name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_wheel_prizes_active ON wheel_prizes(is_active);

-- Seed sample data
INSERT INTO wheel_prizes (prize_name, is_active) VALUES
('Voucher 500K', 1),
('Pin dự phòng mini', 1),
('Giảm 15%', 1),
('Combo vệ sinh hệ pin', 1),
('Chúc may mắn lần sau', 1),
('Voucher 1 Triệu', 1);
