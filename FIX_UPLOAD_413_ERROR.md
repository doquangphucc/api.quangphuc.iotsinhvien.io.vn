# Khắc Phục Lỗi 413 Upload (Request Entity Too Large)

## Vấn đề
Khi upload ảnh/video cho bài viết giới thiệu, gặp lỗi 413 "Request Entity Too Large" ngay cả với file nhỏ.

## Nguyên nhân
Server Nginx đang giới hạn `client_max_body_size` ở 10MB, và các cấu hình PHP cũng bị giới hạn.

## Giải pháp

### Cách 1: Dùng Script Tự Động (Khuyến nghị)

1. **Upload script lên server:**
   ```bash
   scp fix_nginx_upload_limits.sh user@server:/tmp/
   ```

2. **SSH vào server và chạy script:**
   ```bash
   ssh user@server
   cd /tmp
   chmod +x fix_nginx_upload_limits.sh
   ./fix_nginx_upload_limits.sh
   ```

### Cách 2: Sửa Thủ Công

1. **SSH vào server:**
   ```bash
   ssh user@your-server
   ```

2. **Mở file cấu hình Nginx:**
   ```bash
   sudo nano /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn
   ```

3. **Tìm và sửa các dòng sau:**

   **a) Tăng `client_max_body_size` trong location `/api/`:**
   ```nginx
   location /api/ {
       ...
       client_max_body_size 100M;  # Đổi từ 10M thành 100M
       ...
   }
   ```

   **b) Tăng giới hạn PHP trong `fastcgi_param PHP_VALUE`:**
   ```nginx
   fastcgi_param PHP_VALUE "session.cookie_lifetime=86400
                            session.cookie_httponly=1
                            session.cookie_samesite=Lax
                            upload_max_filesize=50M
                            post_max_size=60M
                            max_execution_time=600
                            max_input_time=600
                            memory_limit=256M";
   ```

   **c) Tăng timeouts:**
   ```nginx
   fastcgi_read_timeout 300;
   fastcgi_connect_timeout 300;
   fastcgi_send_timeout 300;
   ```

4. **Kiểm tra cấu hình Nginx:**
   ```bash
   sudo nginx -t
   ```

5. **Reload Nginx:**
   ```bash
   sudo systemctl reload nginx
   ```

### Cách 3: Kiểm Tra và Sửa php.ini (Nếu cần)

1. **Tìm file php.ini:**
   ```bash
   php -i | grep "Loaded Configuration File"
   ```

2. **Sửa file php.ini:**
   ```bash
   sudo nano /etc/php/8.3/fpm/php.ini
   ```

3. **Tìm và sửa các dòng:**
   ```ini
   upload_max_filesize = 50M
   post_max_size = 60M
   max_execution_time = 600
   max_input_time = 600
   memory_limit = 256M
   ```

4. **Restart PHP-FPM:**
   ```bash
   sudo systemctl restart php8.3-fpm  # Đổi version nếu khác
   ```

## Kiểm Tra Sau Khi Sửa

### 1. Kiểm tra Nginx:
```bash
curl -I https://api.quangphuc.iotsinhvien.io.vn
```

### 2. Kiểm tra PHP upload limits:
Tạo file `test_upload_limits.php`:
```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
?>
```

Truy cập: `https://api.quangphuc.iotsinhvien.io.vn/test_upload_limits.php`

### 3. Test upload:
Thử upload ảnh/video trong admin panel.

## Rollback (Nếu có vấn đề)

Nếu sau khi sửa có lỗi, restore từ backup:
```bash
sudo cp /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn.backup_* /etc/nginx/sites-available/api.quangphuc.iotsinhvien.io.vn
sudo nginx -t
sudo systemctl reload nginx
```

## Thông Tin Cấu Hình Sau Khi Sửa

- **Nginx `client_max_body_size`:** 100M
- **PHP `upload_max_filesize`:** 50M
- **PHP `post_max_size`:** 60M
- **PHP `max_execution_time`:** 600 giây
- **PHP `memory_limit`:** 256M
- **FastCGI timeouts:** 300 giây

Các giới hạn này cho phép upload file lên đến 50MB.

## Lưu Ý

1. **Disk space:** Đảm bảo server có đủ dung lượng lưu trữ file
2. **Backup:** Luôn backup cấu hình trước khi sửa
3. **Security:** Không tăng giới hạn quá cao (có thể bị abuse)

## Liên Hệ

Nếu vẫn gặp lỗi sau khi áp dụng các thay đổi, vui lòng cung cấp:
- Thông báo lỗi đầy đủ
- Kích thước file đang upload
- Log Nginx (`/var/log/nginx/error.log`)
