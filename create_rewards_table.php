<?php
require_once 'api/config.php';

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    
    // Tạo bảng lottery_rewards
    $sql = "CREATE TABLE IF NOT EXISTS lottery_rewards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reward_name VARCHAR(255) NOT NULL,
        reward_type VARCHAR(50) NOT NULL COMMENT 'discount, free_shipping, accessory, gift, no_prize',
        reward_value VARCHAR(100) DEFAULT NULL COMMENT 'Giá trị phần thưởng (%, tiền, mô tả)',
        reward_code VARCHAR(50) DEFAULT NULL COMMENT 'Mã voucher/gift code nếu có',
        status ENUM('pending', 'used', 'expired') DEFAULT 'pending',
        ticket_id INT DEFAULT NULL COMMENT 'ID của vé số đã sử dụng',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL DEFAULT NULL,
        used_at TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Bảng lottery_rewards đã được tạo thành công!\n";
    
    // Kiểm tra lại
    $stmt = $pdo->query('SHOW TABLES LIKE "lottery_rewards"');
    $result = $stmt->fetch();
    if ($result) {
        echo "Xác nhận: Bảng lottery_rewards tồn tại\n";
    }
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
?>
