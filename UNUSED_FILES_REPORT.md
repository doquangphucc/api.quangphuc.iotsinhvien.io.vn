# Báo Cáo File Dư Thừa Trong Dự Án

**Ngày tạo:** 2024-10-16  
**Tổng số file dư thừa:** 14 files  
**Tổng dung lượng có thể tiết kiệm:** ~107.57 KB

---

## 🗑️ NÊN XÓA (13 files)

### CSS Files - Không được sử dụng (12 files - 86.65 KB)

❌ `assets/css/app.css`  
❌ `assets/css/base.css`  
❌ `assets/css/components.css`  
❌ `assets/css/footer.css`  
❌ `assets/css/header.css`  
❌ `assets/css/hero.css`  
❌ `assets/css/layout.css`  
❌ `assets/css/news.css`  
❌ `assets/css/pricing.css`  
❌ `assets/css/products.css`  
❌ `assets/css/responsive.css`  
❌ `assets/css/sections.css`

**Lý do:** Các file CSS này không được import/link trong bất kỳ file HTML nào. Dự án đã chuyển sang sử dụng Tailwind CSS CDN.

### JavaScript Files - Không được sử dụng (1 file - 20.92 KB)

❌ `assets/js/cart-page.js`

**Lý do:** File này không được reference trong bất kỳ trang HTML nào.

### HTML Files - Debug/Testing (1 file)

❌ `html/debug_survey.html`

**Lý do:** File debug không cần thiết cho production.

---

## 🤔 CÓ THỂ XÓA (1 file)

⚠️ `update_phone.py`

**Lý do:** Utility script có thể đã hoàn thành nhiệm vụ. Nếu không cần chạy lại thì có thể xóa.

---

## ✅ GIỮ LẠI - ĐANG SỬ DỤNG

### CSS Files

✅ `assets/css/main.css` - **ĐANG DÙNG**  
Được sử dụng bởi 6 files:
- html/tam-nhin-su-menh.html
- html/dieu-khoan-dieu-kien.html
- html/chinh-sach-bao-hanh.html
- html/chinh-sach-bao-mat-thong-tin-ca-nhan.html
- html/chinh-sach-bao-mat-thong-tin-thanh-toan.html
- html/tro-thanh-nha-phan-phoi.html

### JavaScript Files

✅ `assets/js/auth.js` - **ĐANG DÙNG**  
✅ `assets/js/shopping-cart.js` - **ĐANG DÙNG**  
✅ `assets/js/theme.js` - **ĐANG DÙNG**

### Images (Photo/)

✅ **11 images - TẤT CẢ ĐANG DÙNG**

- `about-us.png` → index.html
- `benefits-roi.png` → index.html
- `cta-footer.png` → index.html
- `hero-banner.png` → index.html
- `industrial.png` → index.html
- `partners-tech.png` → index.html
- `pexels-kelly-2800832.jpg` → tin-tuc.html *(mới thêm)*
- `pexels-pixabay-356036.jpg` → tin-tuc.html *(mới thêm)*
- `portfolio.png` → index.html
- `residential.png` → index.html
- `testimonial.png` → index.html

### Videos (Video/)

✅ **8 video files - TẤT CẢ ĐANG DÙNG**

Được sử dụng trong:
- index.html (1 video)
- html/du-an.html (14 references - một số video được dùng nhiều lần)

### Documentation Files

✅ **GIỮ LẠI** - Các file documentation:
- AUTH_SETUP_GUIDE.md
- COLOR_PALETTE.md
- CSS_STRUCTURE.md
- DEBUG_GUIDE_BILL_BREAKDOWN.md
- FIX_BILL_BREAKDOWN.md
- FIXED_BILL_BREAKDOWN_MAPPING.md
- SURVEY_HISTORY_REMAKE.md
- UPDATED_COST_SUMMARY.md
- URL_REWRITE_GUIDE.md

### Configuration Files

✅ `nginx.conf.example` - **GIỮ LẠI** (config example cho deployment)

---

## 📋 LỆNH XÓA

Nếu muốn xóa các file dư thừa, chạy các lệnh sau:

```powershell
# Xóa CSS files không dùng
Remove-Item assets/css/app.css
Remove-Item assets/css/base.css
Remove-Item assets/css/components.css
Remove-Item assets/css/footer.css
Remove-Item assets/css/header.css
Remove-Item assets/css/hero.css
Remove-Item assets/css/layout.css
Remove-Item assets/css/news.css
Remove-Item assets/css/pricing.css
Remove-Item assets/css/products.css
Remove-Item assets/css/responsive.css
Remove-Item assets/css/sections.css

# Xóa JS file không dùng
Remove-Item assets/js/cart-page.js

# Xóa HTML debug file
Remove-Item html/debug_survey.html

# (Optional) Xóa utility script nếu không cần
# Remove-Item update_phone.py
```

Hoặc xóa tất cả cùng lúc:

```powershell
# XÓA TẤT CẢ FILE DƯ THỪA
$filesToDelete = @(
    "assets/css/app.css",
    "assets/css/base.css",
    "assets/css/components.css",
    "assets/css/footer.css",
    "assets/css/header.css",
    "assets/css/hero.css",
    "assets/css/layout.css",
    "assets/css/news.css",
    "assets/css/pricing.css",
    "assets/css/products.css",
    "assets/css/responsive.css",
    "assets/css/sections.css",
    "assets/js/cart-page.js",
    "html/debug_survey.html"
)

foreach ($file in $filesToDelete) {
    if (Test-Path $file) {
        Remove-Item $file
        Write-Host "✅ Đã xóa: $file"
    } else {
        Write-Host "⚠️ Không tìm thấy: $file"
    }
}
```

---

## 💡 KHUYẾN NGHỊ

1. **Backup trước khi xóa:** Nên commit/push code hiện tại lên git trước khi xóa
2. **Test sau khi xóa:** Kiểm tra lại toàn bộ website để đảm bảo không có gì bị ảnh hưởng
3. **Xem xét main.css:** Cân nhắc chuyển 6 trang còn dùng main.css sang Tailwind CSS CDN để đồng nhất
4. **Giữ documentation:** Các file .md nên giữ lại để tham khảo trong tương lai

---

**Kết luận:** Dự án có thể tiết kiệm ~107.57 KB bằng cách xóa 14 file không sử dụng. Các file images và videos đã được tối ưu và sử dụng hiệu quả.

