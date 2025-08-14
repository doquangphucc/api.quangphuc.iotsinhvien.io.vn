# Project dongthoigian

Website + API PHP đơn giản để lưu các việc muốn làm và muốn mua.

## Cấu trúc chính
- `index.html` giao diện trang chủ + modal đăng ký.
- `api/config.php` cấu hình & hàm tiện ích.
- `api/connect.php` kiểm tra kết nối DB (trả JSON).
- `api/register.php` đăng ký tài khoản (POST JSON: username, password, phone).
- `database_schema.sql` script tạo bảng.
- `.env.example` (tùy chọn) ghi chú thông số DB (không bắt buộc trên hosting shared).
- `.htaccess` (thêm) bảo vệ và thiết lập charset, tắt liệt kê thư mục.

## Triển khai lên hosting (App/CPanel)
1. Tạo database `dongthoigian` và user `dongthoigiancanhan` (grant ALL hoặc ít nhất SELECT/INSERT/UPDATE).
2. Import file `database_schema.sql` bằng phpMyAdmin.
3. Sửa thông tin trong `api/config.php` nếu host / user / pass khác thực tế (trên nhiều hosting host là `localhost`).
4. Upload toàn bộ thư mục lên public_html (hoặc subfolder). Đảm bảo cấu trúc: `public_html/index.html` và `public_html/api/...`.
5. Truy cập trang và mở modal đăng ký (nút "Đăng ký").

## Gọi API
- Kiểm tra kết nối: `GET /api/connect.php`
- Đăng ký: `POST /api/register.php`
	- Header: `Content-Type: application/json`
	- Body: `{ "username": "abc", "password": "1234", "phone": "0987654321" }`

Phản hồi (ví dụ thành công):
```json
{ "status": "success", "message": "Đăng ký thành công", "id": 1 }
```

Lỗi trùng SĐT:
```json
{ "status": "error", "code": "PHONE_EXISTS", "message": "Số điện thoại đã tồn tại" }
```

## Bảo mật cơ bản
- Mật khẩu được hash bằng `password_hash` BCRYPT.
- Có thể đổi sang `PASSWORD_DEFAULT` để tự động cập nhật thuật toán trong tương lai.
- Gợi ý: tách file chứa hằng số DB ra `config.local.php` và `require` nếu tồn tại để không commit mật khẩu thật.

## Ghi chú
Đã bỏ các file Node/Express vì hosting PHP không cần. Nếu muốn dùng lại Node, khôi phục `package.json` và server riêng.

