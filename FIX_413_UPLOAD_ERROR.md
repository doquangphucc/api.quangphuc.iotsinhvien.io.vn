# Fix 413 Error - File Upload Too Large

## Vấn đề
Khi upload file 1.53MB vẫn bị lỗi 413 (Request Entity Too Large).

## Nguyên nhân
Lỗi 413 thường do cấu hình Nginx hoặc PHP.ini giới hạn kích thước upload.

## Giải pháp

### 1. Cấu hình Nginx (Áp dụng trên server)

File: `/etc/nginx/sites-available/hcecosystem` (hoặc file config của bạn)

Thêm/cập nhật trong block `location /api/`:

```nginx
location /api/ {
    # Increase client body size for API endpoints
    client_max_body_size 10M;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        
        # Increase FastCGI buffers for large file uploads
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        
        # Increase timeouts for file uploads
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        
        # PHP upload limits
        fastcgi_param PHP_VALUE "upload_max_filesize=5M
                                 post_max_size=10M
                                 max_execution_time=300
                                 max_input_time=300";
    }
}
```

### 2. Kiểm tra và áp dụng cấu hình

```bash
# Kiểm tra syntax
sudo nginx -t

# Nếu OK, reload nginx
sudo systemctl reload nginx

# Hoặc restart
sudo systemctl restart nginx
```

### 3. Kiểm tra PHP.ini (Nếu vẫn lỗi)

File: `/etc/php/8.3/fpm/php.ini` (hoặc version tương ứng)

```ini
upload_max_filesize = 5M
post_max_size = 10M
max_execution_time = 300
max_input_time = 300
```

Sau đó restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

### 4. Kiểm tra cấu hình hiện tại

Tạo file test: `api/admin/test_upload.php` (đã có sẵn trong code)

Truy cập: `https://api.quangphuc.iotsinhvien.io.vn/api/admin/test_upload.php`

File này sẽ hiển thị cấu hình upload hiện tại của server.

### 5. Kiểm tra PHP-FPM pool config

File: `/etc/php/8.3/fpm/pool.d/www.conf`

Đảm bảo có:
```ini
php_admin_value[upload_max_filesize] = 5M
php_admin_value[post_max_size] = 10M
```

Restart PHP-FPM sau khi thay đổi:
```bash
sudo systemctl restart php8.3-fpm
```

## Debug

Nếu vẫn lỗi 413 với file 1.53MB, kiểm tra:

1. **Check nginx logs:**
```bash
sudo tail -f /var/log/nginx/error.log
```

2. **Check PHP-FPM logs:**
```bash
sudo tail -f /var/log/php8.3-fpm.log
```

3. **Kiểm tra tất cả limits:**
```bash
php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time"
```

## Kết quả mong đợi

- Có thể upload file đến 5MB
- Thông báo lỗi rõ ràng nếu file quá lớn
- Button "Lưu" hiển thị trạng thái loading khi đang upload

## Lưu ý

- Thay đổi cấu hình Nginx/PHP yêu cầu quyền sudo
- Nên backup config trước khi thay đổi
- Reload nginx không làm gián đoạn service hiện tại
- Restart PHP-FPM sẽ disconnect các connection hiện tại

