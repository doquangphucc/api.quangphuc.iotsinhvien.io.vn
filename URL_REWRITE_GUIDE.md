# Cấu Hình URL Rewrite - HC Eco System

Tài liệu này hướng dẫn cách cấu hình để ẩn đuôi `.html` khỏi URL và làm cho website có URL đẹp hơn.

## 📋 Mục Tiêu

- ✅ Ẩn đuôi `.html` khỏi URL
- ✅ SEO friendly URLs
- ✅ Tự động redirect từ URL có `.html` sang URL không có đuôi
- ✅ Hỗ trợ cả thư mục root và `/html/`

## 🔧 Cấu Hình Theo Server

### 1️⃣ Apache Server (Sử dụng .htaccess)

File `.htaccess` đã được tạo sẵn trong thư mục root.

**Yêu cầu:**
- Module `mod_rewrite` phải được enable

**Kiểm tra và enable mod_rewrite:**
```bash
# Kiểm tra module có enable không
apache2ctl -M | grep rewrite

# Enable nếu chưa có
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

**Cấu hình Apache virtual host:**
```apache
<VirtualHost *:80>
    ServerName api.quangphuc.iotsinhvien.io.vn
    DocumentRoot /var/www/api.quangphuc.iotsinhvien.io.vn
    
    <Directory /var/www/api.quangphuc.iotsinhvien.io.vn>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 2️⃣ Nginx Server (Khuyến nghị)

File `nginx.conf.example` đã được tạo với cấu hình đầy đủ.

**Các bước cài đặt:**

1. **Copy cấu hình vào sites-available:**
```bash
sudo nano /etc/nginx/sites-available/hcecosystem
# Paste nội dung từ nginx.conf.example
```

2. **Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/hcecosystem /etc/nginx/sites-enabled/
```

3. **Test cấu hình:**
```bash
sudo nginx -t
```

4. **Reload Nginx:**
```bash
sudo systemctl reload nginx
```

5. **Kiểm tra status:**
```bash
sudo systemctl status nginx
```

### 3️⃣ Hosting Panel (Plesk/cPanel)

Nếu bạn sử dụng hosting panel:

**Plesk:**
1. Vào **Domains** → Chọn domain
2. Click **Apache & Nginx Settings**
3. Thêm cấu hình vào phần **Additional directives for HTTP**
4. Apply changes

**cPanel:**
1. Vào **File Manager**
2. Upload file `.htaccess` vào thư mục root
3. Đảm bảo file `.htaccess` được phép hoạt động

## 📝 Cách SửỤng Sau Khi Cấu Hình

### URL Trước (Có đuôi .html):
```
❌ https://api.quangphuc.iotsinhvien.io.vn/html/login.html
❌ https://api.quangphuc.iotsinhvien.io.vn/html/pricing.html
❌ https://api.quangphuc.iotsinhvien.io.vn/index.html
```

### URL Sau (Không có đuôi):
```
✅ https://api.quangphuc.iotsinhvien.io.vn/login
✅ https://api.quangphuc.iotsinhvien.io.vn/pricing
✅ https://api.quangphuc.iotsinhvien.io.vn/
```

## 🔄 Cập Nhật Links Trong Code

### Cách 1: Giữ nguyên code hiện tại
Server sẽ tự động xử lý, không cần sửa code. URL có `.html` sẽ tự động redirect sang URL không có đuôi.

### Cách 2: Cập nhật tất cả links (Khuyến nghị cho SEO tốt hơn)

**Tìm và thay thế:**
```javascript
// Tìm
href="login.html"
href="pricing.html"

// Thay thế
href="/login"
href="/pricing"
```

**Script tự động (chạy trong terminal):**
```bash
# Backup trước khi sửa
cp -r html/ html_backup/

# Tìm và thay thế .html trong các link
find . -name "*.html" -type f -exec sed -i 's/href="\([^"]*\)\.html"/href="\/\1"/g' {} \;
find . -name "*.js" -type f -exec sed -i "s/href = '\([^']*\)\.html'/href = '\/\1'/g" {} \;
```

## 🧪 Testing & Troubleshooting

### Test URL Rewrite:
```bash
# Test 1: URL không có đuôi
curl -I https://api.quangphuc.iotsinhvien.io.vn/login

# Test 2: URL có đuôi (phải redirect 301)
curl -I https://api.quangphuc.iotsinhvien.io.vn/login.html

# Test 3: File không tồn tại
curl -I https://api.quangphuc.iotsinhvien.io.vn/notfound
```

### Debug Nginx:
```bash
# Xem error log
sudo tail -f /var/log/nginx/error.log

# Xem access log
sudo tail -f /var/log/nginx/access.log

# Test specific configuration
sudo nginx -T | grep -A 20 "server_name api.quangphuc"
```

### Debug Apache:
```bash
# Xem error log
sudo tail -f /var/log/apache2/error.log

# Test rewrite rules
sudo a2enmod rewrite
sudo apache2ctl -t -D DUMP_VHOSTS
```

### Common Issues:

**❌ 404 Not Found:**
- Kiểm tra file có tồn tại không
- Kiểm tra permissions: `chmod 644 *.html`
- Kiểm tra đường dẫn trong cấu hình

**❌ 500 Internal Server Error:**
- Syntax error trong .htaccess
- Module chưa được enable
- Check error logs

**❌ Redirect Loop:**
- Xung đột trong rewrite rules
- Kiểm tra lại cấu hình

## 🔒 Security Features

Cả 2 file cấu hình đã bao gồm:

- ✅ **Security Headers**: X-Frame-Options, X-Content-Type-Options, X-XSS-Protection
- ✅ **Directory Listing**: Tắt autoindex
- ✅ **Sensitive Files**: Chặn truy cập .htaccess, .git, .env, etc.
- ✅ **GZIP Compression**: Nén file để tải nhanh hơn
- ✅ **Browser Caching**: Cache static assets

## 📊 Performance Optimization

- ✅ **Asset Caching**: 1 year cho images, 1 month cho CSS/JS
- ✅ **GZIP Compression**: Giảm 60-80% bandwidth
- ✅ **HTTP/2**: Hỗ trợ multiplexing (Nginx)

## 📞 Support

Nếu gặp vấn đề, kiểm tra:
1. Server logs
2. File permissions
3. Module configuration
4. PHP-FPM status (cho API)

## 📚 Tài Liệu Tham Khảo

- [Apache mod_rewrite](https://httpd.apache.org/docs/current/mod/mod_rewrite.html)
- [Nginx Rewrite Module](https://nginx.org/en/docs/http/ngx_http_rewrite_module.html)
- [SEO Best Practices](https://developers.google.com/search/docs/crawling-indexing/url-structure)
