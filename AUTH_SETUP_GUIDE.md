# Hướng dẫn Setup Database và Auth System

## 1. Thiết lập Database

### Bước 1: Import database
```sql
-- Chạy file database/database.sql trong MySQL/phpMyAdmin để tạo database và tables
-- Hoặc thực hiện các bước sau:

-- Tạo database
CREATE DATABASE IF NOT EXISTS hceco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hceco;

-- Import file database/database.sql
```

### Bước 2: Cấu hình kết nối database
Cập nhật thông tin kết nối trong `api/config.php`:
```php
const DB_HOST = 'localhost';  // Server MySQL
const DB_NAME = 'hceco';      // Tên database
const DB_USER = 'root';       // Username MySQL
const DB_PASS = '';           // Password MySQL (để trống nếu không có)
```

## 2. Cấu trúc Auth System đã hoàn thành

### Frontend Components:
- ✅ **Auth Buttons**: Float buttons ở top-right mọi trang
- ✅ **Login Page**: `login.html` với form đăng nhập
- ✅ **Register Page**: `register.html` với form đăng ký đầy đủ
- ✅ **Auth JavaScript**: `assets/js/auth.js` quản lý trạng thái
- ✅ **CSS Styles**: Auth buttons và form styling

### Backend APIs:
- ✅ **Database Connection**: `api/connect.php` - Class Database với PDO
- ✅ **Register API**: `api/register.php` - Đăng ký tài khoản
- ✅ **Login API**: `api/login.php` - Đăng nhập

### Database Tables:
- ✅ **users**: Lưu thông tin người dùng (id, full_name, username, phone, password)
- ✅ **products**: Lưu thông tin sản phẩm với đầy đủ chi tiết

## 3. Tính năng Auth System

### Đăng ký (Register):
- Họ và tên (bắt buộc, tối thiểu 2 ký tự)
- Username (bắt buộc, tối thiểu 3 ký tự, chỉ a-z, 0-9, _)
- Số điện thoại (bắt buộc, 9-12 chữ số, duy nhất)
- Mật khẩu (bắt buộc, tối thiểu 6 ký tự)
- Xác nhận mật khẩu (phải khớp)

### Đăng nhập (Login):
- Username (bắt buộc)
- Password (bắt buộc)

### Auth UI States:
- **Chưa đăng nhập**: Hiển thị 2 buttons "Đăng nhập" và "Đăng ký"
- **Đã đăng nhập**: Hiển thị card với tên người dùng và nút "Đăng xuất"

## 4. Test hệ thống

### Kiểm tra Database:
1. Mở phpMyAdmin hoặc MySQL client
2. Chạy: `SELECT * FROM users;` để xem bảng users
3. Chạy: `SELECT * FROM products;` để xem dữ liệu sản phẩm mẫu

### Kiểm tra API:
1. **Test Register API**:
```bash
curl -X POST http://localhost/WebNLMT/api/register.php \
-H "Content-Type: application/json" \
-d '{
  "full_name": "Nguyễn Văn A",
  "username": "nguyenvana",
  "phone": "0123456789",
  "password": "123456",
  "confirm_password": "123456"
}'
```

2. **Test Login API**:
```bash
curl -X POST http://localhost/WebNLMT/api/login.php \
-H "Content-Type: application/json" \
-d '{
  "username": "nguyenvana",
  "password": "123456"
}'
```

### Kiểm tra Frontend:
1. Mở trang chủ `index.html`
2. Kiểm tra auth buttons ở top-right
3. Click "Đăng ký" → Test form đăng ký
4. Click "Đăng nhập" → Test form đăng nhập
5. Sau khi đăng nhập thành công, kiểm tra hiển thị tên user

## 5. Troubleshooting

### Lỗi kết nối database:
- Kiểm tra MySQL service đã chạy
- Kiểm tra thông tin trong `api/config.php`
- Kiểm tra database `hceco` đã được tạo

### Lỗi CORS:
- Đảm bảo website chạy qua HTTP server (không mở file trực tiếp)
- Sử dụng XAMPP, WAMP, hoặc PHP built-in server

### Lỗi JavaScript:
- Mở Developer Tools (F12) kiểm tra Console
- Kiểm tra Network tab để xem API calls

## 6. Bảo mật

### Đã implement:
- Password hashing với PHP `password_hash()`
- Input validation và sanitization
- SQL injection prevention với PDO Prepared Statements
- CORS headers cho API security

### Khuyến nghị thêm:
- Implement JWT tokens cho session management
- Rate limiting cho API calls
- HTTPS trong production
- Input validation phía frontend