# Hướng dẫn Set Permissions cho Logo Directory

## 🎯 Mục đích
Đảm bảo thư mục `assets/img/logo/` có quyền ghi để upload logo dịch vụ.

## 📝 Cách thực hiện

### Cách 1: Qua FTP Client (FileZilla, WinSCP...)
1. Kết nối FTP đến hosting
2. Vào thư mục `assets/img/`
3. Click chuột phải vào thư mục `logo` → Properties/File Permissions
4. Chọn: **755** (hoặc 777 nếu cần)
5. Tick "Recurse into subdirectories" → OK

### Cách 2: Qua cPanel File Manager
1. Đăng nhập cPanel
2. Vào "File Manager"
3. Navigate đến `assets/img/logo`
4. Click chuột phải → "Change Permissions"
5. Điền: **755** → OK

### Cách 3: Qua SSH/Terminal
```bash
# Di chuyển đến thư mục
cd /path/to/your/website/assets/img

# Set permission 755 (khuyến nghị)
chmod 755 logo
# HOẶC
chmod -R 755 logo  # Recurse (áp dụng cho tất cả subdirectories)

# Set permission 777 (nếu cần, ít an toàn hơn)
chmod 777 logo
chmod -R 777 logo
```

### Cách 4: Tạo Script PHP (nếu không có SSH)
Tạo file `fix_permissions.php` trong thư mục gốc:

```php
<?php
$dir = __DIR__ . '/assets/img/logo';
if (is_dir($dir)) {
    chmod($dir, 0755);
    echo "Đã set permission 755 cho thư mục logo";
} else {
    echo "Thư mục logo chưa tồn tại";
}
?>
```

Sau đó truy cập: `https://yourdomain.com/fix_permissions.php`

## 🔍 Kiểm tra quyền hiện tại

### Qua SSH:
```bash
ls -la assets/img/ | grep logo
# Hoặc
stat assets/img/logo
```

### Kết quả sẽ hiển thị:
```
drwxr-xr-x  owner group  logo
```
- `drwxr-xr-x` = 755 ✓
- `drwxrwxrwx` = 777 ✓  
- `drwxr-x---` = 750 ✗ (không có quyền ghi)

## 🚨 Lưu ý bảo mật

### Khuyến nghị (755):
```
- Owner (bạn): Read + Write + Execute
- Group: Read + Execute
- Others: Read + Execute
- ✅ Bảo mật tốt, đủ quyền ghi
```

### Không khuyến nghị (777):
```
- Owner: Read + Write + Execute
- Group: Read + Write + Execute  
- Others: Read + Write + Execute
- ⚠️ Tất cả user đều có quyền ghi → KHÔNG AN TOÀN
- Chỉ dùng khi 755 không work
```

## ✅ Auto-check trong Code
API `upload_logo.php` đã có logic tự động:
- Nếu thư mục chưa tồn tại → Tự tạo
- Nếu không có quyền ghi → Báo lỗi rõ ràng
- User sẽ thấy message: "Thư mục upload không có quyền ghi"

## 🎯 Kết luận
**Trên hosting:** Chỉ cần chmod 755 là đủ!
```bash
chmod 755 assets/img/logo
```

Nếu vẫn lỗi → thử 777 (tạm thời), rồi báo lại để kiểm tra.

