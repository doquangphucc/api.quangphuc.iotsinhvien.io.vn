# Hệ Thống Phần Thưởng Vòng Quay May Mắn

## 📋 Tổng Quan

Hệ thống này cho phép lưu trữ và quản lý các phần thưởng mà người dùng nhận được từ vòng quay may mắn. Các phần thưởng sẽ được lưu vào database và người dùng có thể xem lại danh sách phần thưởng của mình.

## 🗄️ Cấu Trúc Database

### Bảng `lottery_rewards`

```sql
CREATE TABLE lottery_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_name VARCHAR(255) NOT NULL,
    reward_type VARCHAR(50) NOT NULL,
    reward_value VARCHAR(100) DEFAULT NULL,
    reward_code VARCHAR(50) DEFAULT NULL,
    reward_image VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'used', 'expired') DEFAULT 'pending',
    ticket_id INT DEFAULT NULL,
    won_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES lottery_tickets(id) ON DELETE SET NULL
);
```

## 🚀 Hướng Dẫn Cài Đặt

### Bước 1: Tạo Bảng Database

```bash
# Chạy script SQL để tạo bảng
mysql -u root -p your_database < database/rewards_table.sql
```

Hoặc import trực tiếp trong phpMyAdmin/MySQL Workbench.

### Bước 2: Kiểm Tra API

Các API đã được tạo:

1. **`api/save_lottery_reward.php`** - Lưu phần thưởng vào database
2. **`api/get_lottery_rewards.php`** - Lấy danh sách phần thưởng của user

### Bước 3: Kiểm Tra Trang Web

Các trang đã được tạo/cập nhật:

1. **`html/my-rewards.html`** - Trang hiển thị phần thưởng của user
2. **`html/user_profile.html`** - Đã thêm link đến trang phần thưởng
3. **`html/vong-quay-may-man.html`** - Đã tích hợp lưu phần thưởng
4. **`assets/js/lottery.js`** - Đã cập nhật logic lưu phần thưởng

## 📡 API Endpoints

### 1. Lưu Phần Thưởng

**POST** `/api/save_lottery_reward.php`

**Request Body:**
```json
{
    "reward_name": "Giảm 10%",
    "reward_type": "discount",
    "reward_value": "10%",
    "reward_code": null,
    "ticket_id": null,
    "expires_days": 30
}
```

**Response:**
```json
{
    "success": true,
    "message": "Lưu phần thưởng thành công!",
    "data": {
        "id": 1,
        "user_id": 1,
        "reward_name": "Giảm 10%",
        "reward_code": "REWARD12345678",
        "expires_at": "2025-01-17 10:00:00",
        ...
    }
}
```

### 2. Lấy Danh Sách Phần Thưởng

**GET** `/api/get_lottery_rewards.php?status=all&limit=50&offset=0`

**Parameters:**
- `status`: all | pending | used | expired
- `limit`: Số lượng kết quả (mặc định 50)
- `offset`: Vị trí bắt đầu (mặc định 0)
- `order_by`: won_at | reward_name | reward_type | expires_at | status
- `order_dir`: ASC | DESC

**Response:**
```json
{
    "success": true,
    "message": "Lấy danh sách phần thưởng thành công",
    "data": {
        "rewards": [...],
        "pagination": {
            "total": 10,
            "limit": 50,
            "offset": 0,
            "has_more": false
        },
        "stats": {
            "total_rewards": 10,
            "pending_count": 5,
            "used_count": 3,
            "expired_count": 2
        }
    }
}
```

## 🎨 Tính Năng

### 1. Tự Động Lưu Phần Thưởng
- Khi user quay vòng quay, phần thưởng sẽ tự động được lưu vào database
- Mỗi phần thưởng có mã code duy nhất (REWARD + 8 ký tự ngẫu nhiên)
- Tự động set thời gian hết hạn (mặc định 30 ngày)

### 2. Quản Lý Phần Thưởng
- Xem danh sách tất cả phần thưởng
- Lọc theo trạng thái: Chưa dùng, Đã sử dụng, Hết hạn
- Hiển thị thông tin chi tiết: mã code, thời gian nhận, thời gian hết hạn
- Cảnh báo khi phần thưởng sắp hết hạn (còn 3 ngày)

### 3. Trạng Thái Phần Thưởng
- **pending**: Chưa sử dụng
- **used**: Đã sử dụng
- **expired**: Hết hạn

### 4. Thống Kê
- Tổng số phần thưởng
- Số phần thưởng chưa dùng
- Số phần thưởng đã sử dụng
- Số phần thưởng hết hạn

## 🔗 Liên Kết

Từ trang **User Profile**, người dùng có thể:
1. Nhấn vào **"Vòng quay"** → Đi đến trang vòng quay may mắn
2. Nhấn vào **"Phần thưởng"** → Xem danh sách phần thưởng đã nhận

## 🎯 Loại Phần Thưởng

Hệ thống hỗ trợ các loại phần thưởng:

1. **discount** - Giảm giá (10%, 20%, 50%)
2. **free_shipping** - Miễn phí vận chuyển
3. **accessory** - Tặng kèm phụ kiện
4. **voucher** - Voucher giảm giá
5. **gift** - Quà tặng chung
6. **no_prize** - Chúc may mắn lần sau

## 🔒 Bảo Mật

- Chỉ user đã đăng nhập mới có thể xem và lưu phần thưởng
- Mỗi user chỉ có thể xem phần thưởng của chính mình
- API kiểm tra quyền sở hữu vé số trước khi lưu phần thưởng

## 📱 Responsive Design

Trang phần thưởng được thiết kế responsive, hoạt động tốt trên:
- Desktop
- Tablet
- Mobile

## 🌙 Dark Mode

Tất cả các trang đều hỗ trợ Dark Mode với các màu sắc phù hợp.

## 📝 Lưu Ý

1. Phần thưởng tự động chuyển sang trạng thái "expired" khi hết hạn
2. Mã code phần thưởng là duy nhất và được tạo tự động
3. Có thể mở rộng để thêm chức năng sử dụng phần thưởng trong giỏ hàng
4. Có thể thêm thông báo email khi nhận được phần thưởng

## 🚧 Cải Tiến Tương Lai

- [ ] Tích hợp sử dụng phần thưởng vào giỏ hàng
- [ ] Gửi email thông báo khi nhận phần thưởng
- [ ] Thông báo khi phần thưởng sắp hết hạn
- [ ] Lịch sử sử dụng phần thưởng chi tiết
- [ ] Cho phép chuyển phần thưởng cho người khác
- [ ] QR code để sử dụng phần thưởng tại cửa hàng

## 📞 Hỗ Trợ

Nếu có vấn đề hoặc câu hỏi, vui lòng liên hệ team phát triển.

