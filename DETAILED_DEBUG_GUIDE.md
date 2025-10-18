# HƯỚNG DẪN DEBUG LỖI 500 LOTTERY REWARD

## 🔍 Phân Tích Vấn Đề

Từ ảnh bạn gửi, tôi thấy:
- ✅ Vòng quay hoạt động bình thường
- ✅ Modal hiển thị phần thưởng "Tặng kèm phụ kiện"
- ❌ **Lỗi 500 Internal Server Error** khi gọi `save_lottery_reward.php`
- ❌ **HTTP error: 500** trong console

## 🎯 Nguyên Nhân Có Thể

### 1. **Session/Authentication lỗi** (Khả năng cao nhất)
- User chưa đăng nhập đúng cách
- Session bị mất hoặc không hoạt động
- Cookie không được gửi kèm request

### 2. **Database connection lỗi**
- Bảng `lottery_rewards` chưa được tạo đúng
- Foreign key constraint fails
- Database connection timeout

### 3. **Input data lỗi**
- JSON data không đúng format
- Missing required fields
- Invalid data types

## 🛠️ CÁCH DEBUG CHI TIẾT

### Bước 1: Kiểm Tra Database

**Truy cập URL này để test database:**
```
https://api.quangphuc.iotsinhvien.io.vn/api/quick_test.php
```

**Kỳ vọng:**
- ✅ Database connected
- ✅ lottery_rewards table exists
- ✅ Test insert SUCCESS

### Bước 2: Kiểm Tra Session & Authentication

**Truy cập URL này để debug session:**
```
https://api.quangphuc.iotsinhvien.io.vn/api/debug_save_reward_detailed.php
```

**Kỳ vọng:**
- ✅ Session user_id: [SOME_ID]
- ✅ JSON decoded successfully
- ✅ Database connected
- ✅ lottery_rewards table exists
- ✅ Test insert SUCCESS

### Bước 3: Kiểm Tra User Login

**Đảm bảo bạn đã đăng nhập:**
1. **Vào trang login:** `/html/login.html`
2. **Đăng nhập với:** `testuser` / `123456`
3. **Kiểm tra:** Có thấy tên user ở góc phải không?

### Bước 4: Kiểm Tra Console Network

**Mở DevTools (F12) → Network tab:**
1. **Quay vòng quay**
2. **Tìm request** `save_lottery_reward.php`
3. **Click vào request** để xem chi tiết
4. **Check:**
   - Request Headers (có Cookie không?)
   - Request Payload (JSON data đúng không?)
   - Response (lỗi gì?)

## 🔧 CÁCH FIX CỤ THỂ

### Fix 1: Session Problem

**Nếu session không hoạt động:**

1. **Clear browser cache:** `Ctrl + Shift + R`
2. **Đăng nhập lại**
3. **Kiểm tra cookie:** DevTools → Application → Cookies
4. **Test lại vòng quay**

### Fix 2: Database Problem

**Nếu database có vấn đề:**

1. **Kiểm tra phpMyAdmin:**
```sql
-- Kiểm tra bảng có tồn tại không
SHOW TABLES LIKE 'lottery_rewards';

-- Kiểm tra cấu trúc bảng
DESCRIBE lottery_rewards;

-- Kiểm tra user test
SELECT * FROM users WHERE username = 'testuser';

-- Kiểm tra lottery tickets
SELECT * FROM lottery_tickets WHERE user_id = 1;
```

2. **Nếu thiếu gì, import lại:**
   - File: `database/complete_database.sql`
   - Chọn database: `nangluongmattroi`
   - Click "Go"

### Fix 3: Code Problem

**Nếu code có vấn đề, tôi sẽ tạo version đơn giản hơn:**

```php
// Version đơn giản không cần session
<?php
require_once 'config.php';

// Mock user_id = 1 for testing
$userId = 1;

$input = json_decode(file_get_contents('php://input'), true);

$rewardData = [
    'user_id' => $userId,
    'ticket_id' => $input['ticket_id'] ?? null,
    'reward_name' => $input['reward_name'] ?? 'Test Reward',
    'reward_type' => $input['reward_type'] ?? 'gift',
    'reward_value' => $input['reward_value'] ?? null,
    'reward_code' => 'TEST' . rand(1000, 9999),
    'reward_image' => null,
    'status' => 'pending',
    'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
    'notes' => null
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $fields = array_keys($rewardData);
    $fieldList = implode(',', $fields);
    $paramList = ':' . implode(', :', $fields);
    
    $sql = "INSERT INTO lottery_rewards ({$fieldList}) VALUES ({$paramList})";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($rewardData);
    
    $rewardId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'data' => ['reward_id' => $rewardId],
        'message' => 'Phần thưởng đã được lưu thành công!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
?>
```

## 📋 CHECKLIST DEBUG

- [ ] **Truy cập** `quick_test.php` - Database OK?
- [ ] **Truy cập** `debug_save_reward_detailed.php` - Session OK?
- [ ] **Đăng nhập** với `testuser` / `123456`
- [ ] **Clear cache** browser và test lại
- [ ] **Check Network tab** trong DevTools
- [ ] **Kiểm tra phpMyAdmin** - Bảng lottery_rewards có đúng không?

## 🚨 NẾU VẪN LỖI

**Gửi cho tôi kết quả của:**
1. **`quick_test.php`** - Screenshot hoặc copy text
2. **`debug_save_reward_detailed.php`** - Screenshot hoặc copy text
3. **Network tab** - Screenshot request `save_lottery_reward.php`
4. **Console log** - Copy text đầy đủ

## 🎯 KỲ VỌNG SAU KHI FIX

- ✅ Truy cập debug scripts thành công
- ✅ Database test insert OK
- ✅ Session user_id có giá trị
- ✅ Quay vòng quay không có lỗi 500
- ✅ Phần thưởng được lưu vào database
- ✅ Console không có error

---

**Tác giả:** AI Assistant  
**Ngày:** 2025-10-18  
**Version:** 1.2 - Detailed Debug Guide

