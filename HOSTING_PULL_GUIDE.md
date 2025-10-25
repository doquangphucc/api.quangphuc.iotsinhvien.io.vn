# Hướng dẫn Pull Code Sạch từ GitHub về Hosting

## ✅ **Trạng thái hiện tại:**
- **Local:** Đã rollback về `before-qr-payment` ✅
- **GitHub:** Đã force push, code sạch ✅
- **Hosting:** Cần pull code mới

## 🚀 **Các bước pull code trên hosting:**

### **Bước 1: SSH vào hosting**
```bash
ssh username@your-hosting-server
# Hoặc dùng terminal/console của hosting provider
```

### **Bước 2: Di chuyển đến thư mục project**
```bash
cd /path/to/your/project
# Ví dụ: cd /home/username/public_html
# Hoặc: cd /var/www/html
```

### **Bước 3: Backup code hiện tại (để phòng tránh)**
```bash
# Tạo backup folder
cp -r . ../backup_$(date +%Y%m%d_%H%M%S)

# Hoặc chỉ backup files quan trọng
tar -czf ../backup_$(date +%Y%m%d).tar.gz .
```

### **Bước 4: Kiểm tra trạng thái Git**
```bash
git status
git log --oneline -5
```

### **Bước 5: Pull code mới từ GitHub**
```bash
# Fetch latest changes
git fetch origin

# Reset hard về main branch (xóa tất cả thay đổi local)
git reset --hard origin/main

# Hoặc nếu muốn an toàn hơn:
git pull origin main --force
```

### **Bước 6: Verify code đã được pull**
```bash
# Kiểm tra commit hiện tại
git log --oneline -1

# Nên thấy:
# a6fad17 Fix logout button event listeners for mobile containers

# Kiểm tra không còn files payment
ls -la api/ | grep -E "(vnpay|momo|payment)"
# Không nên thấy kết quả nào

ls -la html/ | grep payment
# Không nên thấy kết quả nào
```

### **Bước 7: Clear cache (nếu có)**
```bash
# Clear PHP opcache (nếu có)
# Tùy thuộc vào hosting, có thể cần:
# - Restart PHP-FPM
# - Clear cache qua control panel
# - Hoặc tạo file clear_cache.php

# Ví dụ clear opcache:
echo "<?php opcache_reset(); echo 'Cache cleared'; ?>" > clear_cache.php
curl https://yourdomain.com/clear_cache.php
rm clear_cache.php
```

### **Bước 8: Test website**
```bash
# Test API endpoints
curl https://api.quangphuc.iotsinhvien.io.vn/api/get_cart.php

# Test homepage
curl https://api.quangphuc.iotsinhvien.io.vn/
```

## 🔧 **Nếu gặp vấn đề:**

### **Vấn đề 1: Git không có trên hosting**
```bash
# Nếu hosting không có Git, download code trực tiếp:
wget https://github.com/doquangphucc/api.quangphuc.iotsinhvien.io.vn/archive/refs/heads/main.zip
unzip main.zip
mv api.quangphuc.iotsinhvien.io.vn-main/* .
rm -rf api.quangphuc.iotsinhvien.io.vn-main main.zip
```

### **Vấn đề 2: Permission denied**
```bash
# Fix permissions
chmod -R 755 .
chown -R www-data:www-data .
# Hoặc user của web server
```

### **Vấn đề 3: Conflict khi pull**
```bash
# Force reset về remote
git fetch origin
git reset --hard origin/main
git clean -fd
```

## 📋 **Checklist sau khi pull:**

- [ ] Code đã được pull thành công
- [ ] Commit hiện tại là `a6fad17`
- [ ] Không còn files payment (vnpay, momo)
- [ ] Website hoạt động bình thường
- [ ] API endpoints hoạt động
- [ ] Database không bị ảnh hưởng
- [ ] User có thể đặt hàng bình thường

## 🗄️ **Lưu ý về Database:**

### **Nếu đã import payment tables:**
```sql
-- Xóa các bảng payment (nếu có)
DROP TABLE IF EXISTS payment_logs;
DROP TABLE IF EXISTS payment_transactions;
DROP TABLE IF EXISTS vnpay_config;
DROP TABLE IF EXISTS momo_config;

-- Xóa columns payment trong orders (nếu cần)
ALTER TABLE orders DROP COLUMN IF EXISTS payment_method;
ALTER TABLE orders DROP COLUMN IF EXISTS payment_status;
ALTER TABLE orders DROP COLUMN IF EXISTS vnpay_transaction_id;
ALTER TABLE orders DROP COLUMN IF EXISTS momo_transaction_id;
ALTER TABLE orders DROP COLUMN IF EXISTS payment_expires_at;
ALTER TABLE orders DROP COLUMN IF EXISTS payment_reference;
ALTER TABLE orders DROP COLUMN IF EXISTS payment_amount;
ALTER TABLE orders DROP COLUMN IF EXISTS payment_created_at;
ALTER TABLE orders DROP COLUMN IF EXISTS payment_completed_at;
```

### **Hoặc import lại database sạch:**
```sql
-- Import database gốc (không có payment)
SOURCE database/complete_database.sql;
```

## ✅ **Kết quả mong đợi:**

Sau khi hoàn thành các bước trên:
- ✅ Code trên hosting = code trên GitHub = code local
- ✅ Không còn tích hợp payment
- ✅ Website hoạt động bình thường
- ✅ Đặt hàng trực tiếp (không qua payment gateway)

## 🎯 **Commands tóm tắt (Quick Guide):**

```bash
# 1. SSH vào hosting
ssh username@hosting

# 2. Đến thư mục project
cd /path/to/project

# 3. Backup
cp -r . ../backup_$(date +%Y%m%d)

# 4. Pull code mới
git fetch origin
git reset --hard origin/main

# 5. Verify
git log --oneline -1
# Nên thấy: a6fad17 Fix logout button event listeners

# 6. Test
curl https://yourdomain.com/
```

**Done! Code trên hosting đã sạch và đồng bộ với GitHub!** 🎉
