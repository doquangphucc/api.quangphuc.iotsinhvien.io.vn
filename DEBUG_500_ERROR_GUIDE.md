# HƯỚNG DẪN DEBUG LỖI 500 - LOTTERY REWARD

## 🔍 Phân Tích Lỗi

Từ ảnh bạn gửi, tôi thấy:
- ✅ Vòng quay hoạt động bình thường
- ✅ Modal hiển thị phần thưởng "Miễn phí vận chuyển"
- ❌ **Lỗi 500 Internal Server Error** khi gọi `save_lottery_reward.php`
- ❌ **HTTP error: 500** trong console

## 🎯 Nguyên Nhân Có Thể

### 1. **Bảng `lottery_rewards` chưa được tạo** (Khả năng cao nhất)
- Database được import lại nhưng thiếu bảng `lottery_rewards`
- File `database/rewards_table.sql` chưa được import

### 2. **Database connection lỗi**
- Config database không đúng
- Database server không hoạt động

### 3. **Session/Authentication lỗi**
- User chưa đăng nhập đúng cách
- Session bị mất

## 🛠️ CÁCH FIX

### Bước 1: Kiểm Tra Database

**Mở phpMyAdmin và chạy:**

```sql
-- Kiểm tra bảng có tồn tại không
SHOW TABLES LIKE 'lottery_rewards';

-- Nếu không có kết quả, chạy script tạo bảng:
```

**Import file SQL này vào phpMyAdmin:**
```sql
-- File: create_lottery_rewards_table.sql
USE nangluongmattroi;

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
```

### Bước 2: Kiểm Tra Các Bảng Khác

```sql
-- Kiểm tra bảng users
SELECT COUNT(*) FROM users;

-- Kiểm tra bảng lottery_tickets  
SELECT COUNT(*) FROM lottery_tickets;

-- Kiểm tra user hiện tại có tickets không
SELECT * FROM lottery_tickets WHERE user_id = [YOUR_USER_ID];
```

### Bước 3: Test API Trực Tiếp

**Truy cập URL này để test:**
```
https://api.quangphuc.iotsinhvien.io.vn/api/basic_test.php
```

**Nếu không truy cập được, có thể server chưa sync code mới.**

### Bước 4: Tạo Test Data

**Nếu không có lottery tickets, tạo test ticket:**

```sql
-- Tạo test ticket cho user
INSERT INTO lottery_tickets (user_id, ticket_type, status, created_at) 
VALUES ([YOUR_USER_ID], 'bonus', 'active', NOW());
```

## 🔧 DEBUG CHI TIẾT

### Kiểm Tra Log Server

**Nếu có quyền truy cập server:**
```bash
# Xem error log
tail -f /var/log/nginx/error.log
# hoặc
tail -f /var/log/apache2/error.log
```

**Tìm các dòng lỗi liên quan đến:**
- `save_lottery_reward.php`
- `lottery_rewards`
- `PDOException`
- `MySQL`

### Test Database Connection

**Tạo file test đơn giản:**

```php
<?php
// test_db.php
require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Database OK";
    
    // Test lottery_rewards table
    $stmt = $pdo->query("SHOW TABLES LIKE 'lottery_rewards'");
    if ($stmt->fetch()) {
        echo " - lottery_rewards table exists";
    } else {
        echo " - lottery_rewards table MISSING!";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage();
}
?>
```

## 📋 CHECKLIST FIX

- [ ] **Import bảng `lottery_rewards`** vào phpMyAdmin
- [ ] **Kiểm tra user có lottery tickets** không
- [ ] **Tạo test ticket** nếu cần
- [ ] **Clear browser cache** và test lại
- [ ] **Kiểm tra console** (F12) có lỗi JavaScript không
- [ ] **Test API trực tiếp** bằng Postman/curl

## 🎯 KỲ VỌNG SAU KHI FIX

1. ✅ Truy cập `basic_test.php` thành công
2. ✅ Bảng `lottery_rewards` tồn tại với đầy đủ cột
3. ✅ User có ít nhất 1 lottery ticket
4. ✅ Quay vòng quay không có lỗi 500
5. ✅ Console không có error
6. ✅ Phần thưởng được lưu vào database với `ticket_id` đúng

## 🚨 NẾU VẪN LỖI

**Gửi cho tôi:**
1. **Kết quả của `basic_test.php`**
2. **Screenshot phpMyAdmin** hiển thị cấu trúc bảng `lottery_rewards`
3. **Console log** đầy đủ (F12 → Console)
4. **Error log từ server** (nếu có quyền truy cập)

---

**Tác giả:** AI Assistant  
**Ngày:** 2025-10-18  
**Version:** 1.1 - Debug Guide
