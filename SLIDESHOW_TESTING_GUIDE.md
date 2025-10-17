# Hướng Dẫn Kiểm Tra Slideshow Background

## ✅ Các Lỗi Đã Được Sửa

### 1. Lỗi 404 - File Not Found
**Triệu chứng**: Console báo lỗi "404 (Not Found)" khi tải ảnh/video

**Đã sửa**:
- ✅ Thêm biến `pathPrefix` tự động phát hiện vị trí trang
- ✅ Cập nhật tất cả đường dẫn trong `slideshow-config.js` sử dụng template string
- ✅ Thêm script `window.SLIDESHOW_PATH_PREFIX` vào mỗi trang

**Cách kiểm tra**:
```javascript
// Mở Console (F12) và chạy lệnh này:
console.log(window.SLIDESHOW_PATH_PREFIX);
// Kết quả mong đợi:
// - Trang index.html: '' (empty string)
// - Các trang trong html/: '../'
```

---

## 📋 Checklist Kiểm Tra

### Bước 1: Xóa Cache Trình Duyệt
1. Nhấn `Ctrl + Shift + Del` (Windows) hoặc `Cmd + Shift + Del` (Mac)
2. Chọn "Cached images and files"
3. Chọn "All time"
4. Nhấn "Clear data"

### Bước 2: Hard Refresh
Sau khi xóa cache, tải lại trang bằng:
- `Ctrl + Shift + R` (Windows)
- `Cmd + Shift + R` (Mac)
- Hoặc `Ctrl + F5` (Windows)

### Bước 3: Kiểm Tra Console
Mở Developer Tools (F12) và kiểm tra tab "Console":

**✅ Không có lỗi** - Slideshow hoạt động tốt
**❌ Có lỗi màu đỏ** - Vẫn còn vấn đề

---

## 🎯 Kiểm Tra Từng Trang

### 1. index.html (Trang chủ)
- **URL**: `http://localhost/index.html`
- **data-page**: `home`
- **Số slides**: 8 (4 video + 4 ảnh)
- **Thời gian**: 7 giây/slide

**Kiểm tra**:
```javascript
// Mở Console và chạy:
console.log(window.slideshowConfig);
// Kết quả phải có: { slides: [...], interval: 7000 }
```

### 2. html/khao-sat-dien-mat-troi.html (Trang khảo sát)
- **URL**: `http://localhost/html/khao-sat-dien-mat-troi.html`
- **data-page**: `survey`
- **Số slides**: 5 (2 video + 3 ảnh)
- **Thời gian**: 7 giây/slide

**Video phải hiển thị**:
- Prompt 7.mp4 (Dòng năng lượng)
- Prompt 5.mp4 (Hệ thống điện sạch)

### 3. html/pricing.html (Trang bảng giá)
- **URL**: `http://localhost/html/pricing.html`
- **data-page**: `pricing`
- **Số slides**: 5 (1 video + 4 ảnh)
- **Thời gian**: 6 giây/slide

### 4. html/tin-tuc.html (Trang tin tức)
- **URL**: `http://localhost/html/tin-tuc.html`
- **data-page**: `news`
- **Số slides**: 8 (sử dụng homepage slides)
- **Thời gian**: 7 giây/slide

### 5. html/gioi-thieu.html (Trang giới thiệu)
- **URL**: `http://localhost/html/gioi-thieu.html`
- **data-page**: `about`
- **Số slides**: 6 (3 video + 3 ảnh)
- **Thời gian**: 8 giây/slide

### 6. html/lien-he.html (Trang liên hệ)
- **URL**: `http://localhost/html/lien-he.html`
- **data-page**: `contact`
- **Số slides**: 6 (2 video + 4 ảnh)
- **Thời gian**: 7 giây/slide

---

## 🔍 Kiểm Tra Chi Tiết

### Kiểm Tra Slideshow Đang Chạy
Mở Console (F12) và chạy:

```javascript
// Kiểm tra container có tồn tại không
const container = document.getElementById('slideshow-background');
console.log('Container exists:', !!container);

// Kiểm tra số lượng slides
const slides = document.querySelectorAll('.slideshow-slide');
console.log('Number of slides:', slides.length);

// Kiểm tra slide hiện tại
const activeSlide = document.querySelector('.slideshow-slide.active');
console.log('Active slide:', activeSlide);

// Kiểm tra config
console.log('Slideshow config:', window.slideshowConfig);
```

### Kiểm Tra Video Có Phát Không
```javascript
// Lấy tất cả video elements
const videos = document.querySelectorAll('.slideshow-slide video');
console.log('Videos found:', videos.length);

// Kiểm tra video đầu tiên
if (videos.length > 0) {
    console.log('First video src:', videos[0].src);
    console.log('Video loaded:', !videos[0].error);
}
```

---

## 🐛 Xử Lý Lỗi Thường Gặp

### Lỗi 1: Video không phát (Video autoplay prevented)
**Nguyên nhân**: Trình duyệt chặn autoplay
**Giải pháp**: Đã được xử lý trong code với `muted` và `playsInline`

### Lỗi 2: Ảnh/Video không tải được (404 Not Found)
**Nguyên nhân**: Đường dẫn file không đúng
**Giải pháp**: Đã sửa bằng cách thêm `pathPrefix`

### Lỗi 3: Slideshow không chuyển slide
**Nguyên nhân**: JavaScript chưa load hoặc có lỗi
**Kiểm tra**:
```javascript
console.log(typeof BackgroundSlideshow);
// Phải hiển thị: "function"
```

### Lỗi 4: Slideshow làm trang chậm
**Giải pháp**:
1. Kiểm tra kích thước file ảnh/video (nên < 5MB)
2. Tăng interval time (hiện tại 6-8 giây)
3. Giảm số lượng slides

---

## ✨ Tính Năng Slideshow

### 1. Auto-play
- Tự động chuyển slide sau mỗi interval time
- Tạm dừng khi tab bị ẩn (tiết kiệm tài nguyên)

### 2. Manual Controls
- Nút Previous/Next để điều khiển thủ công
- Pause/Play button

### 3. Progress Bar
- Thanh tiến trình hiển thị thời gian còn lại của slide hiện tại

### 4. Lazy Loading
- Chỉ tải ảnh/video khi cần thiết
- Tiết kiệm băng thông

### 5. Hiệu ứng
- **Fade**: Mờ dần vào/ra
- **Zoom**: Phóng to nhẹ trong khi hiển thị (Ken Burns effect)
- **Glassmorphism**: Hiệu ứng kính mờ cho content

---

## 📊 Thống Kê Sử Dụng Media

### Video được sử dụng: 9/9 ✅
- Prompt 1.mp4 ✓ (Homepage, Projects, Installation)
- Prompt 2.mp4 ✓ (About, Contact)
- Prompt 3.mp4 ✓ (About, Installation)
- Prompt 4.mp4 ✓ (Homepage)
- Prompt 5.mp4 ✓ (Survey)
- Prompt 6.mp4 ✓ (About, Contact)
- Prompt 7.mp4 ✓ (Survey)
- Prompt 8.mp4 ✓ (Homepage, Pricing, Projects)
- Prompt 9.mp4 ✓ (Homepage)

### Ảnh được sử dụng: Nhiều ảnh từ Photo/
Xem file `MEDIA_USAGE_REPORT.md` để biết chi tiết

---

## 🎨 Tùy Chỉnh

### Thay đổi thời gian chuyển slide
Mở `assets/js/slideshow-config.js` và chỉnh sửa:

```javascript
const configs = {
    'home': { slides: homepageSlides, interval: 7000 }, // 7 giây
    'about': { slides: aboutSlides, interval: 8000 },   // 8 giây
    'pricing': { slides: pricingSlides, interval: 6000 } // 6 giây
    // ...
};
```

### Thêm/Xóa slides
Mở `assets/js/slideshow-config.js` và chỉnh sửa mảng tương ứng:

```javascript
const homepageSlides = [
    {
        type: 'video',
        src: `${pathPrefix}Video/Prompt 1.mp4`,
        alt: 'Mô tả video'
    },
    {
        type: 'image',
        src: `${pathPrefix}Photo/ten-anh.jpg`,
        alt: 'Mô tả ảnh'
    }
    // Thêm slides mới tại đây
];
```

### Thay đổi hiệu ứng animation
Mở `assets/css/slideshow.css` và chỉnh sửa:

```css
.slideshow-slide.active {
    opacity: 1;
    transform: scale(1.05); /* Tăng/giảm độ zoom */
    animation: kenBurns 20s ease-in-out; /* Thay đổi thời gian */
}
```

---

## 📞 Hỗ Trợ

Nếu vẫn gặp lỗi sau khi làm theo hướng dẫn:

1. **Kiểm tra lại cấu trúc thư mục**:
   ```
   D:\api.quangphuc.iotsinhvien.io.vn\
   ├── index.html
   ├── Photo/
   │   └── [ảnh]
   ├── Video/
   │   └── [video]
   ├── assets/
   │   ├── css/
   │   │   └── slideshow.css
   │   └── js/
   │       ├── slideshow.js
   │       └── slideshow-config.js
   └── html/
       └── [các trang HTML]
   ```

2. **Kiểm tra file có tồn tại không**:
   Mở Console và chạy:
   ```javascript
   fetch('../Video/Prompt 1.mp4', {method: 'HEAD'})
       .then(r => console.log('File exists:', r.ok))
       .catch(e => console.log('File not found:', e));
   ```

3. **Gửi thông tin lỗi**:
   - Screenshot console (F12 > Console tab)
   - URL trang đang gặp lỗi
   - Trình duyệt đang sử dụng

---

## 🎉 Kết Luận

Slideshow background đã được tích hợp hoàn chỉnh và **tất cả lỗi 404 đã được sửa**. Website giờ đây có:

✅ Background động với ảnh và video chuyên nghiệp  
✅ Hiệu ứng glassmorphism hiện đại  
✅ Tự động phát và điều khiển thủ công  
✅ Responsive trên mọi thiết bị  
✅ Hỗ trợ dark mode  
✅ Tối ưu hiệu năng với lazy loading  

**Hãy hard refresh (Ctrl+Shift+R) và thưởng thức website mới của bạn!** 🚀

