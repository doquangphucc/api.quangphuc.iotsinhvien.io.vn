# HƯỚNG DẪN GRID LAYOUT CHO TRANG SẢN PHẨM

## 📐 Quy tắc Grid Layout

### Desktop (> 1024px)
- **4 sản phẩm**: 2 cột × 2 hàng (class: `grid-4`)
- **3 sản phẩm**: 3 cột × 1 hàng (class: `grid-3`)
- **6 sản phẩm**: 3 cột × 2 hàng (class: `grid-6`)
- **8 sản phẩm**: 4 cột × 2 hàng (class: `grid-8`)

### Tablet (768px - 1024px)
- **4, 3, 6 sản phẩm**: 2 cột
- **8 sản phẩm**: 3 cột

### Mobile (< 768px)
- **Tất cả**: 1 cột

## 🎨 CSS Classes Cần Thêm

```css
/* Products Grid Base */
.products-grid {
    display: grid;
    gap: 30px;
    padding: 60px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

/* Grid với 4 sản phẩm: 2 cột */
.products-grid.grid-4 {
    grid-template-columns: repeat(2, 1fr);
    max-width: 1000px;
}

/* Grid với 3 sản phẩm: 3 cột */
.products-grid.grid-3 {
    grid-template-columns: repeat(3, 1fr);
    max-width: 1200px;
}

/* Grid với 6 sản phẩm: 3 cột */
.products-grid.grid-6 {
    grid-template-columns: repeat(3, 1fr);
    max-width: 1200px;
}

/* Grid với 8 sản phẩm: 4 cột */
.products-grid.grid-8 {
    grid-template-columns: repeat(4, 1fr);
    max-width: 1400px;
}

/* Responsive */
@media (max-width: 1024px) {
    .products-grid.grid-4,
    .products-grid.grid-3,
    .products-grid.grid-6 {
        grid-template-columns: repeat(2, 1fr);
        max-width: 900px;
    }
    .products-grid.grid-8 {
        grid-template-columns: repeat(3, 1fr);
        max-width: 1000px;
    }
}

@media (max-width: 768px) {
    .product-hero h1 { font-size: 2.2rem; }
    .products-grid,
    .products-grid.grid-4,
    .products-grid.grid-3,
    .products-grid.grid-6,
    .products-grid.grid-8 { 
        grid-template-columns: 1fr !important; 
        padding: 40px 15px;
        max-width: 600px;
    }
    .product-actions { flex-direction: column; }
}
```

## 📝 HTML Usage

### Ví dụ với 4 sản phẩm:
```html
<div class="products-grid grid-4">
    <!-- 4 product cards -->
</div>
```

### Ví dụ với 3 sản phẩm:
```html
<div class="products-grid grid-3">
    <!-- 3 product cards -->
</div>
```

### Ví dụ với 8 sản phẩm:
```html
<div class="products-grid grid-8">
    <!-- 8 product cards -->
</div>
```

## ✅ Trang Đã Hoàn Thành

1. ✅ **product-luxpower-hybrid.html** - 4 sản phẩm (`grid-4`)
2. ✅ **product-luxpower-1pha.html** - 4 sản phẩm (`grid-4`)

## 📋 Danh Sách Trang Cần Redesign

3. **product-luxpower-3phase.html** - LUXPOWER 3 PHA ÁP THẤP (cần đếm số sản phẩm)
4. **product-luxpower-3phase-high.html** - LUXPOWER 3 PHA ÁP CAO
5. **product-growatt-110kw.html** - Inverter Growatt 110kW MAX
6. **product-cables.html** - Cáp & Phụ Kiện (8 sản phẩm → `grid-8`)
7. **product-battery-storage.html** - PIN LƯU TRỮ A-CORNEX
8. **product-battery-byd.html** - PIN LƯU TRỮ BYD
9. **product-solar-panels.html** - TẤM PIN NĂNG LƯỢNG MẶT TRỜI
10. **product-electrical-cabinet.html** - TỦ ĐIỆN HYBRID

## 🎯 Encoding Note

**QUAN TRỌNG**: Luôn đảm bảo:
- File lưu với UTF-8 encoding
- Sử dụng `write` tool thay vì PowerShell để tránh lỗi encoding
- Test ký tự tiếng Việt sau khi tạo file

