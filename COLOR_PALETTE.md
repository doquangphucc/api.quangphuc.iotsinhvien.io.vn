# � Solar Green Palette – Website Năng Lượng Mặt Trời

Website đã được cập nhật với bảng màu "Solar Green Palette" - một bảng màu xanh lá tươi sáng, thể hiện sự tươi mới, năng lượng và công nghệ xanh.

## 🎨 Bảng màu chính

### 🌳 Dark Tech Green (#1E5631)
- **RGB**: rgb(30, 86, 49)
- **Công dụng**: Header, text nổi bật, tiêu đề quan trọng
- **CSS Variable**: `--color-text`, `--color-green-dark`
- **Ý nghĩa**: Xanh lá công nghệ đậm - Thể hiện sự chuyên nghiệp, uy tín

### 🍃 Primary Bright Green (#3FA34D)
- **RGB**: rgb(63, 163, 77)
- **Công dụng**: Menu, button chính, links, màu chủ đạo
- **CSS Variable**: `--color-green`, `--color-muted`
- **Ý nghĩa**: Xanh lá tươi sáng - Năng lượng, sự sống, tươi mới

### 🌿 Light Green (#77DD77)
- **RGB**: rgb(119, 221, 119)
- **Công dụng**: Background section, hover states, accents
- **CSS Variable**: `--color-green-light`
- **Ý nghĩa**: Xanh lá sáng nhẹ - Thân thiện, dễ chịu

### 🌾 Yellow-Green Pastel (#C5E384)
- **RGB**: rgb(197, 227, 132)
- **Công dụng**: CTA (Call-to-Action), highlight text, badges
- **CSS Variable**: `--color-yellow`
- **Ý nghĩa**: Vàng xanh pastel - Thu hút chú ý, năng lượng mặt trời

### ☀️ Off-White Green (#F4F9E9)
- **RGB**: rgb(244, 249, 233)
- **Công dụng**: Nền tổng thể website, card backgrounds
- **CSS Variable**: `--color-bg`
- **Ý nghĩa**: Trắng ngả xanh - Thoáng đãng, sạch sẽ, dễ đọc

## 📐 CSS Variables

```css
:root {
    /* Solar Green Palette - Năng Lượng Mặt Trời */
    --color-green: #3FA34D;        /* Primary green - Xanh lá tươi sáng */
    --color-green-dark: #1E5631;   /* Dark green - Xanh lá công nghệ đậm */
    --color-green-light: #77DD77;  /* Light green - Xanh lá sáng nhẹ */
    --color-yellow: #C5E384;       /* Yellow-green pastel - Vàng xanh */
    --color-white: #ffffff;
    --color-bg: #F4F9E9;           /* Off-white green - Trắng ngả xanh */
    --color-text: #1E5631;         /* Dark green for text */
    --color-muted: #3FA34D;        /* Primary green for muted */
}
```

## 💡 Ý tưởng sử dụng

### 🎨 Nền website
- **Màu chính**: `#F4F9E9` (Trắng ngả xanh)
- **Lý do**: Tạo cảm giác thoáng đãng, sạch sẽ, thân thiện và dễ đọc
- **Hiệu ứng**: Làm nổi bật các element màu xanh

### 🔘 Buttons & CTAs
- **Màu chính**: `#3FA34D` (Xanh lá tươi sáng)
- **Hover**: `#1E5631` (Xanh đậm) hoặc `#77DD77` (Xanh sáng)
- **CTA nổi bật**: `#C5E384` (Vàng xanh) với text `#1E5631`
- **Gradients**: `linear-gradient(135deg, #3FA34D, #1E5631)`

### 📝 Text & Headings
- **Heading chính**: `#1E5631` (Xanh đậm)
- **Body text**: `#1E5631` (Xanh đậm)
- **Text phụ**: `#3FA34D` (Xanh tươi)
- **Links**: `#3FA34D` với hover `#1E5631`

### 🎯 Highlights & Accents
- **Primary**: `#C5E384` (Vàng xanh)
- **Secondary**: `#77DD77` (Xanh sáng)
- **Backgrounds**: `rgba(119, 221, 119, 0.1)` - Xanh sáng với alpha
- **Shadows**: `rgba(63, 163, 77, 0.35)` - Xanh tươi với alpha

### 📦 Components
- **Cards**: Background `#ffffff` trên nền `#F4F9E9`
- **Sections**: Background `rgba(119, 221, 119, 0.08)` (Xanh sáng nhẹ)
- **Borders**: `rgba(63, 163, 77, 0.2)` (Xanh tươi alpha)
- **Hover states**: `#77DD77` (Xanh sáng)

## 🎯 Áp dụng chi tiết

### Header & Navigation
```css
background: #ffffff;
color: #1E5631; /* Dark text */
links: #3FA34D; /* Bright green */
hover: #77DD77; /* Light green */
```

### Hero Section
```css
background: linear-gradient(165deg, rgba(63, 163, 77, 0.08), rgba(197, 227, 132, 0.1));
heading: #1E5631;
text: #3FA34D;
button: #C5E384; /* CTA yellow-green */
```

### Content Sections
```css
background: #F4F9E9; /* Off-white */
card-background: #ffffff;
heading: #1E5631;
text: #3FA34D;
accent: #77DD77;
```

### Footer
```css
background: #1E5631; /* Dark green */
text: #F4F9E9; /* Light text */
links: #77DD77; /* Light green */
accent: #C5E384;
```

## 🔄 So sánh với bảng màu cũ

| Element | Màu cũ (Nature Breath) | Màu mới (Solar Green) |
|---------|------------------------|----------------------|
| **Primary** | #5A6E1B (Olive) | #3FA34D (Bright Green) ⬆️ Sáng hơn |
| **Dark** | #394D16 (Deep Olive) | #1E5631 (Tech Green) ⬆️ Xanh công nghệ |
| **Light** | #ACB232 (Leaf) | #77DD77 (Light Green) ⬆️ Tươi sáng hơn |
| **Yellow** | #E3E78C (Pale) | #C5E384 (Pastel) ⬇️ Dịu hơn |
| **Background** | #f6f9f2 | #F4F9E9 ➡️ Xanh nhẹ hơn |

## ✨ Ưu điểm của Solar Green Palette

✅ **Tươi sáng & Năng động**: Màu xanh lá tươi tạo cảm giác năng lượng, sức sống

✅ **Công nghệ xanh**: Thể hiện rõ ràng chủ đề năng lượng tái tạo, thân thiện môi trường

✅ **Dễ đọc**: Nền sáng (#F4F9E9) với text đậm (#1E5631) tạo contrast tốt

✅ **Thân thiện**: Các tone màu pastel tạo cảm giác gần gũi, không gây áp lực

✅ **Chuyên nghiệp**: Xanh đậm (#1E5631) tạo sự uy tín, tin cậy

✅ **Nổi bật**: CTA màu vàng-xanh (#C5E384) thu hút mắt người xem

## 📊 Files đã cập nhật

### CSS Files (12 files)
- ✅ assets/css/base.css
- ✅ assets/css/main.css
- ✅ assets/css/components.css
- ✅ assets/css/header.css
- ✅ assets/css/hero.css
- ✅ assets/css/footer.css
- ✅ assets/css/sections.css
- ✅ assets/css/pricing.css
- ✅ assets/css/products.css
- ✅ assets/css/news.css
- ✅ assets/css/layout.css
- ✅ assets/css/responsive.css

### HTML Files (22 files)
- ✅ index.html (root)
- ✅ All files in html/ folder with inline styles

### JavaScript Files (1 file)
- ✅ assets/js/shopping-cart.js - notification colors

## 🎨 Design Philosophy

**Solar Green Palette** được thiết kế dựa trên:

1. **☀️ Năng lượng mặt trời**: Màu xanh tươi, sáng như cây cối nhận ánh sáng mặt trời
2. **🌱 Tăng trưởng xanh**: Thể hiện sự phát triển bền vững, thân thiện môi trường
3. **💚 Công nghệ xanh**: Màu xanh công nghệ hiện đại, không "quê mùa"
4. **🌿 Tươi mới & Sạch**: Nền sáng, thoáng đãng, dễ chịu
5. **⚡ Năng động**: Màu sáng tạo cảm giác năng lượng, không trì trệ

---

**Ngày cập nhật**: 1 tháng 10, 2025  
**Design Palette**: Solar Green Palette - Năng lượng mặt trời


## 🌿 Bảng màu chính

### Dark Forest (#243816)
- **RGB**: rgb(36, 56, 22)
- **Sử dụng**: Text chính, tiêu đề
- **CSS Variable**: `--color-text`, `--color-green-darker`

### Deep Forest (#394D16)
- **RGB**: rgb(57, 77, 22)
- **Sử dụng**: Text phụ, hover states, accents
- **CSS Variable**: `--color-green-dark`, `--color-muted`

### Forest Green (#5A6E1B) - PRIMARY
- **RGB**: rgb(90, 110, 27)
- **Sử dụng**: Màu chủ đạo, buttons, links, highlights
- **CSS Variable**: `--color-green`

### Leaf Green (#ACB232)
- **RGB**: rgb(172, 178, 50)
- **Sử dụng**: Secondary accents, highlights
- **CSS Variable**: `--color-yellow-bright`

### Pale Yellow (#E3E78C)
- **RGB**: rgb(227, 231, 140)
- **Sử dụng**: Soft highlights, badges, light accents
- **CSS Variable**: `--color-yellow`

## 📐 CSS Variables

```css
:root {
    /* Nature Breath Color Palette */
    --color-green: #5A6E1B;        /* Primary green - Forest Green */
    --color-green-dark: #394D16;   /* Dark green - Deep Forest */
    --color-green-darker: #243816; /* Darkest green - Dark Forest */
    --color-yellow: #E3E78C;       /* Pale Yellow */
    --color-yellow-bright: #ACB232;/* Leaf Green */
    --color-white: #ffffff;
    --color-bg: #f6f9f2;
    --color-text: #243816;         /* Dark Forest for text */
    --color-muted: #394D16;        /* Deep Forest for muted text */
}
```

## 🎯 Áp dụng

### Buttons & CTAs
- Background: `--color-green` (#5A6E1B)
- Hover: `--color-green-dark` (#394D16)
- Gradients: `linear-gradient(135deg, #5A6E1B, #394D16)`

### Text
- Heading: `--color-text` (#243816)
- Body: `--color-text` (#243816)
- Muted/Secondary: `--color-muted` (#394D16)

### Highlights & Accents
- Primary: `--color-yellow` (#E3E78C)
- Secondary: `--color-yellow-bright` (#ACB232)
- Shadows: `rgba(90, 110, 27, 0.3)` hoặc `rgba(57, 77, 22, 0.2)`

### Backgrounds
- Light: `rgba(90, 110, 27, 0.08)` - Very light green
- Medium: `rgba(90, 110, 27, 0.12)` - Light green
- Strong: `rgba(90, 110, 27, 0.2)` - Medium green

## 🔄 Thay đổi từ bảng màu cũ

| Element | Màu cũ | Màu mới |
|---------|--------|---------|
| Primary Green | #0b8f24 | #5A6E1B |
| Dark Green | #06661a | #394D16 |
| Yellow | #f2c744 | #E3E78C |
| Text | #1f2c1f | #243816 |
| Muted | #5a6d58 | #394D16 |

## ✨ Files đã cập nhật

### CSS Files (12 files)
- ✅ `assets/css/base.css`
- ✅ `assets/css/main.css`
- ✅ `assets/css/components.css`
- ✅ `assets/css/header.css`
- ✅ `assets/css/hero.css`
- ✅ `assets/css/footer.css`
- ✅ `assets/css/sections.css`
- ✅ `assets/css/pricing.css`
- ✅ `assets/css/products.css`
- ✅ `assets/css/news.css`
- ✅ `assets/css/layout.css`
- ✅ `assets/css/responsive.css`

### HTML Files (22 files)
- ✅ All inline styles updated

### JavaScript Files (1 file)
- ✅ `assets/js/shopping-cart.js` - notification colors

## 🎨 Design Philosophy

Bảng màu "Nature Breath" được chọn vì:

1. **Tự nhiên & Xanh**: Phản ánh giá trị năng lượng sạch, bền vững
2. **Chuyên nghiệp**: Tone màu trầm, ổn định tạo sự tin cậy
3. **Dễ nhìn**: Contrast tốt, không gây mỏi mắt
4. **Hài hòa**: Các tông màu xanh hòa quyện với nhau một cách tự nhiên
5. **Hiện đại**: Phù hợp với xu hướng thiết kế web 2025

---

**Ngày cập nhật**: 1 tháng 10, 2025  
**Designer**: Nature Breath Palette by Designer Vietnam
