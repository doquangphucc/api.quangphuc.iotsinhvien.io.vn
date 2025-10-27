# 🚀 Hướng dẫn Set Quyền (Chmod) cho toàn bộ Assets

## ⚡ Cách nhanh nhất

### Qua SSH (Khuyến nghị):
```bash
cd /path/to/your/website
chmod -R 755 assets
```

**Giải thích:**
- `chmod` = thay đổi quyền
- `-R` = recursive (áp dụng cho tất cả thư mục con)
- `755` = quyền cho owner (rwx) + group/others (rx)
- `assets` = thư mục cần set quyền

### Nếu 755 không work, thử 777:
```bash
chmod -R 777 assets
```
⚠️ **Lưu ý:** 777 ít an toàn hơn nhưng đảm bảo upload được.

## 📂 Cấu trúc thư mục sẽ được set quyền:
```
assets/
├── css/
├── img/
│   ├── categories/
│   ├── ecosystem/
│   ├── logo/          ← Logo dịch vụ upload ở đây
│   ├── package-categories/
│   └── products/
├── js/
└── ...
```

Tất cả thư mục con đều được set quyền tự động với `-R` (recursive).

## 🎯 Các cách thực hiện khác:

### Cách 1: SSH (Terminal)
```bash
ssh username@your-server.com
cd /var/www/html
chmod -R 755 assets
```

### Cách 2: FTP (FileZilla, WinSCP...)
1. Kết nối FTP
2. Vào thư mục gốc website
3. Click phải vào thư mục `assets`
4. Properties → Permissions → 755
5. ✅ Tick "Recurse into subdirectories"
6. Apply to all files and directories
7. OK

### Cách 3: cPanel File Manager
1. Đăng nhập cPanel
2. File Manager → thư mục gốc
3. Click phải `assets` → Permissions
4. Điền: 755
5. ✅ Recurse
6. Save

### Cách 4: Terminal của Hosting
Trong cPanel Terminal hoặc Web Terminal:
```bash
cd public_html  # hoặc www, htdocs...
chmod -R 755 assets
```

## ✅ Kiểm tra sau khi chmod:
```bash
ls -la | grep assets
```
Sẽ hiển thị:
```
drwxr-xr-x  user group  assets
```

## 📝 Lưu ý:
- **755** (khuyến nghị): Bảo mật tốt, đủ quyền ghi
- **777** (backup): Tất cả user đều có quyền ghi (kém an toàn)
- Chỉ cần chmod 1 lần cho thư mục `assets`
- Tất cả thư mục con sẽ tự động có quyền tương ứng

## 🎯 Kết luận:
```bash
chmod -R 755 assets
```
**Chỉ 1 dòng lệnh, xong tất cả!** ✅

