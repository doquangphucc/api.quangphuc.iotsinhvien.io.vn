# 🔧 Fix Upload Permission - Chi tiết

## ❌ Vấn đề:
Sau khi chạy `chmod -R 755 assets` vẫn gặp lỗi "Thư mục không có quyền ghi"

## ✅ Nguyên nhân:
Owner của thư mục là `admin1` nhưng web server chạy với user khác (thường là `www-data` hoặc `nobody`)

## 🎯 Cách fix:

### Bước 1: Kiểm tra ownership
```bash
ls -la assets/img/ | head -20
```

Sẽ thấy:
```
drwxr-xr-x admin1 admin1 logo
```
→ Owner là `admin1`, web server cần quyền

### Bước 2: Fix ownership cho thư mục (KHÔNG cần sudo)
```bash
# Chỉ set ownership cho THƯ MỤC (không cần sudo)
chown admin1:admin1 -R assets/img/logo
```

### Bước 3: Nếu cần web server user có quyền ghi
```bash
# Kiểm tra web server user là gì
ps aux | grep nginx
ps aux | grep apache

# Thường là www-data hoặc nginx
# Set ownership cho web server user:
chown -R www-data:www-data assets/img/logo
# HOẶC
chown -R nginx:nginx assets/img/logo
```

### Bước 4: Tạo thư mục logo nếu chưa có
```bash
mkdir -p assets/img/logo
chmod 755 assets/img/logo
```

## 🚀 Giải pháp nhanh nhất (chmod + tạo thư mục):
```bash
# 1. Tạo thư mục logo
mkdir -p assets/img/logo

# 2. Set quyền cho THƯ MỤC
chmod 755 assets/img/logo

# 3. Set ownership
chown admin1:admin1 assets/img/logo

# 4. Kiểm tra
ls -la assets/img/ | grep logo
```

Kết quả mong đợi:
```
drwxr-xr-x 2 admin1 admin1 4096 Oct 28 00:00 logo
```

## 📝 Note:
- Quyền 755 trên thư mục = owner (r/w/x), group và other (r/x)
- Web server chỉ cần quyền GHI (write) để tạo file mới
- Nếu owner là admin1 → OK
- Nếu owner là web server user (www-data) → cũng OK

## ✅ Verify:
```bash
# Xem quyền
ls -ld assets/img/logo

# Phải thấy: drwxr-xr-x
# Nếu thấy --- hoặc ??? → Sai quyền
```

