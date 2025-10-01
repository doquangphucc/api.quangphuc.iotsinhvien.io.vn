# CSS Structure Documentation

## Tổng quan
CSS đã được chia thành các module riêng biệt để dễ quản lý và bảo trì. Thay vì 1 file `styles.css` lớn, giờ chúng ta có 10 file CSS được tổ chức theo chức năng.

## Cấu trúc file CSS

### 📁 assets/css/
```
├── main.css          # File chính import tất cả modules
├── base.css          # Variables, reset, base styles
├── layout.css        # Grid, container, section layouts
├── header.css        # Header, navigation, quick contact
├── hero.css          # Hero section styles
├── components.css    # Buttons, cards, forms, breadcrumb
├── sections.css      # Content sections (benefits, process, contact)
├── footer.css        # Footer styles
├── pricing.css       # Pricing page specific styles
├── products.css      # Product pages specific styles
├── responsive.css    # Media queries for all breakpoints
└── styles.css        # (File cũ - có thể xóa sau khi test)
```

## Mô tả từng file

### 🎨 **base.css**
- CSS Variables (colors, fonts, sizes)
- Reset styles
- Base typography
- Screen reader utilities

### 📐 **layout.css**
- Container styles
- Grid systems
- Section base layouts
- Header structures for content

### 🧭 **header.css**
- Topbar & navigation
- Logo & branding
- Quick contact widget (fixed sidebar) - 6 items: Facebook, Zalo, TikTok, YouTube, Phone, Maps
- Navigation hover effects

### 🎯 **hero.css**
- Hero section layouts
- Hero cards & stats
- Eyebrow badges
- Hero action buttons

### 🧱 **components.css**
- Button styles (primary, secondary, full-width)
- Card components
- Form elements
- CTA banners
- Breadcrumbs

### 📄 **sections.css**
- Benefits section
- Process section (with step numbers)
- Contact section
- Content-specific layouts

### 🦶 **footer.css**
- Footer layouts
- Footer branding
- Policy links
- Contact info styles

### 💰 **pricing.css**
- Pricing cards & grids
- Plan badges
- Comparison tables
- FAQ sections
- Product grid sections

### 📦 **products.css**
- Product hero sections
- Product categories & cards
- Product detail tables
- Feature grids
- Product specifications

### 📱 **responsive.css**
- All media queries (1024px, 768px, 520px)
- Mobile-specific adjustments
- Responsive grid modifications

## Cách sử dụng

### Import trong HTML
```html
<link rel="stylesheet" href="assets/css/main.css" />
```

### Chỉnh sửa styles
- **Màu sắc, fonts**: Chỉnh trong `base.css`
- **Layout, grid**: Chỉnh trong `layout.css`
- **Navigation**: Chỉnh trong `header.css`
- **Buttons, forms**: Chỉnh trong `components.css`
- **Pricing**: Chỉnh trong `pricing.css`
- **Products**: Chỉnh trong `products.css`
- **Responsive**: Chỉnh trong `responsive.css`

## Lợi ích của cấu trúc mới

✅ **Dễ bảo trì**: Mỗi file có chức năng riêng biệt
✅ **Tránh conflict**: Không lo thay đổi nhầm code
✅ **Team work**: Nhiều người có thể làm việc đồng thời
✅ **Performance**: Có thể lazy load các module không cần thiết
✅ **Scalable**: Dễ thêm tính năng mới
✅ **Debug**: Dễ tìm và fix lỗi CSS

## Notes
- File `styles.css` cũ vẫn còn, có thể xóa sau khi test kỹ
- Tất cả HTML files đã được update để dùng `main.css`
- CSS import theo thứ tự ưu tiên (base → layout → components → responsive)