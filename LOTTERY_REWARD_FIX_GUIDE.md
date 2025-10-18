# Hướng Dẫn Fix Lỗi Lưu Phần Thưởng Lottery

## Vấn Đề Đã Được Fix

### 1. **Lỗi Flow Logic** ❌ → ✅
**Trước đây:**
- API `saveReward()` được gọi TRƯỚC `useTicket()`
- Dẫn đến `ticket_id` luôn là `null` khi lưu reward
- Database không liên kết được reward với ticket đã sử dụng

**Đã fix:**
- Thay đổi flow: Gọi `useTicket()` TRƯỚC để lấy `ticket_id`
- Sau đó truyền `ticket_id` vào `saveReward(prize, ticketId)`
- Đảm bảo mỗi reward được liên kết với ticket đã sử dụng

### 2. **Cải Thiện Code** ✨
- Clean up code trong `save_lottery_reward.php`
- Loại bỏ các debug logs không cần thiết
- Cải thiện error handling
- Thêm validation tốt hơn cho input

## Files Đã Thay Đổi

### 1. `assets/js/lottery.js`
```javascript
// Thay đổi flow trong showResult()
async showResult(prize) {
    // Sử dụng vé trước để lấy ticket_id
    const ticketId = await this.useTicket();
    
    // Lưu phần thưởng vào database với ticket_id
    await this.saveReward(prize, ticketId);
}

// Cập nhật saveReward() để nhận ticketId
async saveReward(prize, ticketId) {
    // ... code
    ticket_id: ticketId, // Sử dụng ticket_id từ useTicket()
}

// Cập nhật useTicket() để return ticket_id
async useTicket() {
    // ... code
    return result.data.ticket_id;
}
```

### 2. `api/save_lottery_reward.php`
- Clean up debug logs
- Cải thiện error handling
- Thêm logging cho debugging
- Validate input tốt hơn

## Cách Test

### Bước 1: Kiểm Tra Database
```bash
# Truy cập URL:
http://your-domain/api/verify_reward_table.php
```

Script này sẽ kiểm tra:
- ✅ Bảng `lottery_rewards` có tồn tại không
- ✅ Cấu trúc bảng có đúng không (tất cả các cột)
- ✅ Số lượng records hiện tại
- ✅ Dữ liệu mẫu (nếu có)

### Bước 2: Test Flow Lottery

1. **Đăng nhập vào tài khoản** có lottery tickets
2. **Truy cập trang vòng quay**: `/html/vong-quay-may-man.html`
3. **Click "Quay Ngay!"**
4. **Quan sát:**
   - Vòng quay hoạt động bình thường
   - Modal hiển thị phần thưởng
   - Check console (F12) - không có error

### Bước 3: Kiểm Tra Database

Sau khi quay, vào phpMyAdmin và chạy:

```sql
SELECT * FROM lottery_rewards 
WHERE user_id = [YOUR_USER_ID] 
ORDER BY created_at DESC 
LIMIT 5;
```

**Kỳ vọng:**
- ✅ Record mới được tạo
- ✅ `ticket_id` có giá trị (KHÔNG null)
- ✅ `reward_name`, `reward_type`, `reward_value` đúng
- ✅ `reward_code` được generate (nếu là discount/gift)
- ✅ `status` = 'pending'
- ✅ `expires_at` = created_at + 30 days

### Bước 4: Kiểm Tra Lottery Tickets

```sql
SELECT * FROM lottery_tickets 
WHERE user_id = [YOUR_USER_ID] 
AND status = 'used'
ORDER BY created_at DESC 
LIMIT 5;
```

**Kỳ vọng:**
- ✅ Ticket vừa dùng có `status` = 'used'
- ✅ `id` của ticket này khớp với `ticket_id` trong lottery_rewards

## Debug Logs

Nếu có lỗi, check log files:

### PHP Error Log
```bash
# Linux/Mac
tail -f /var/log/apache2/error.log
# hoặc
tail -f /var/log/nginx/error.log

# Windows (XAMPP)
# Xem file: xampp/apache/logs/error.log
```

**Tìm các dòng log:**
```
Saving reward for user_id: X, ticket_id: Y, reward: Z
Reward saved successfully with ID: N
```

### Browser Console
Mở DevTools (F12) → Console tab

**Tìm các log:**
```javascript
'Phần thưởng đã được lưu:', {reward_id: X, reward_code: 'ABC123'}
```

**Nếu có lỗi:**
```javascript
'Error saving reward:', error_message
'Lỗi khi lưu phần thưởng:', error_message
```

## Troubleshooting

### Lỗi: "Bảng lottery_rewards CHƯA TỒN TẠI"

**Giải pháp:**
1. Mở phpMyAdmin
2. Chọn database `nangluongmattroi`
3. Import file: `database/rewards_table.sql`

### Lỗi: "Foreign key constraint fails"

**Nguyên nhân:** User ID hoặc Ticket ID không tồn tại

**Giải pháp:**
```sql
-- Kiểm tra user có tồn tại
SELECT * FROM users WHERE id = [YOUR_USER_ID];

-- Kiểm tra ticket có tồn tại
SELECT * FROM lottery_tickets WHERE id = [TICKET_ID];
```

### Lỗi: "ticket_id is null" trong database

**Nguyên nhân:** Có thể do:
1. File JS chưa được refresh (clear cache)
2. API `use_lottery_ticket.php` không return đúng format

**Giải pháp:**
1. Hard refresh browser: `Ctrl + Shift + R` (hoặc `Cmd + Shift + R`)
2. Clear browser cache
3. Check API response:
```javascript
// Trong browser console
fetch('/api/use_lottery_ticket.php', {
    method: 'POST',
    credentials: 'include'
}).then(r => r.json()).then(console.log);
```

### Không có vé để quay

**Giải pháp:** Tạo vé test trong database:
```sql
INSERT INTO lottery_tickets (user_id, ticket_type, status, created_at) 
VALUES ([YOUR_USER_ID], 'bonus', 'active', NOW());
```

## Kết Quả Mong Đợi

Sau khi fix:
- ✅ User quay vòng quay thành công
- ✅ Phần thưởng được lưu vào database
- ✅ `ticket_id` được liên kết đúng
- ✅ Reward code được generate tự động
- ✅ Expire date được set đúng (30 ngày)
- ✅ User có thể xem rewards trong trang "Phần Thưởng Của Tôi"

## Files Cần Commit

```
modified:   assets/js/lottery.js
modified:   api/save_lottery_reward.php
new file:   api/verify_reward_table.php
new file:   LOTTERY_REWARD_FIX_GUIDE.md
```

## Ghi Chú

- Cache buster đã được enable trong `cache-buster.js`, nhưng để chắc chắn nên hard refresh
- Database schema đã được chuẩn hóa theo file `database/rewards_table.sql`
- Tất cả các file test/debug có thể xóa sau khi test xong:
  - `api/debug_save_reward.php`
  - `api/test_database.php`
  - `create_rewards_table.php`
  - `test_database.php`

---

**Tác giả:** AI Assistant  
**Ngày:** 2025-10-18  
**Version:** 1.0

