# Hướng Dẫn Sử Dụng Background Slideshow

## Tổng Quan

Hệ thống background slideshow cho phép bạn thêm ảnh và video chạy nền tự động với hiệu ứng chuyển đổi mượt mà, làm tăng tính thẩm mỹ và sự sinh động cho website.

## Tính Năng

✅ Hỗ trợ cả ảnh (JPG, PNG) và video (MP4)  
✅ Chuyển đổi mượt mà với fade effect  
✅ Tự động phát và pause  
✅ Controls: Play/Pause, Next, Previous  
✅ Progress bar hiển thị tiến trình  
✅ Ken Burns effect (zoom nhẹ)  
✅ Glassmorphism effect cho content  
✅ Dark mode support  
✅ Mobile responsive  
✅ Auto-pause khi tab không active  

## Cấu Trúc Files

```
assets/
├── css/
│   └── slideshow.css          # Styles cho slideshow
├── js/
│   ├── slideshow.js            # Logic chính
│   └── slideshow-config.js     # Cấu hình cho từng trang
Photo/                          # Thư mục chứa ảnh
Video/                          # Thư mục chứa video
```

## Cách Thêm Slideshow Cho Trang Mới

### Bước 1: Thêm CSS và Data Attribute

Trong `<head>` của HTML:

```html
<!-- Slideshow CSS -->
<link rel="stylesheet" href="assets/css/slideshow.css" />
```

Trong thẻ `<body>`:

```html
<body data-page="ten-trang">
```

### Bước 2: Thêm Slideshow Container

Ngay sau thẻ `<body>`:

```html
<!-- Background Slideshow -->
<div id="slideshow-background" class="slideshow-background"></div>
```

### Bước 3: Thêm Scripts

Trước thẻ đóng `</body>`:

```html
<!-- Slideshow Scripts -->
<script src="assets/js/slideshow-config.js"></script>
<script src="assets/js/slideshow.js"></script>
```

### Bước 4: Thêm CSS Tùy Chỉnh (Optional)

Trong `<style>` tag để làm content trong suốt:

```css
/* Slideshow enhancements */
body {
    background-color: transparent !important;
}

/* Make content sections slightly transparent */
.bg-gray-50 {
    background-color: rgba(249, 250, 251, 0.95) !important;
}

body[data-theme="dark"] .bg-gray-900 {
    background-color: rgba(17, 24, 39, 0.95) !important;
}

/* Sections with glassmorphism */
section {
    background: rgba(255, 255, 255, 0.75);
    backdrop-filter: blur(8px);
}

body[data-theme="dark"] section {
    background: rgba(26, 32, 44, 0.8);
}
```

## Cấu Hình Slides

### Các Trang Đã Được Cấu Hình

1. **home** - Trang chủ (index.html)
2. **about** - Giới thiệu (gioi-thieu.html)
3. **pricing** - Bảng giá (pricing.html)
4. **survey** - Khảo sát (khao-sat-dien-mat-troi.html)
5. **projects** - Dự án (du-an.html)
6. **installation** - Lắp đặt

### Thêm Configuration Mới

Trong file `assets/js/slideshow-config.js`, thêm array slides mới:

```javascript
const tenTrangSlides = [
    {
        type: 'image',
        src: 'Photo/ten-anh.jpg',
        alt: 'Mô tả ảnh'
    },
    {
        type: 'video',
        src: 'Video/ten-video.mp4',
        alt: 'Mô tả video'
    }
];
```

Sau đó thêm vào object `configs`:

```javascript
function getSlideshowConfig(pageName) {
    const configs = {
        'ten-trang': { slides: tenTrangSlides, interval: 7000 },
        // ... các configs khác
    };
    return configs[pageName] || configs['home'];
}
```

## Mapping Video với Nội Dung

### Video Descriptions

- **Prompt 1**: Flycam kỹ sư lắp đặt pin mặt trời → Sử dụng cho: Trang chủ, Dự án
- **Prompt 2**: Chuyên gia tư vấn khách hàng → Sử dụng cho: Giới thiệu, Liên hệ
- **Prompt 3**: Kỹ sư kiểm tra hệ thống → Sử dụng cho: Dịch vụ, Bảo hành
- **Prompt 4**: Timelapse hoàng hôn với pin mặt trời → Sử dụng cho: Trang chủ, Portfolio
- **Prompt 5**: Công tắc điện và thiết bị hoạt động → Sử dụng cho: Giải pháp, Lợi ích
- **Prompt 6**: Khách hàng hài lòng → Sử dụng cho: Testimonials, Giới thiệu
- **Prompt 7**: Motion graphics dòng năng lượng → Sử dụng cho: Khảo sát, Pricing
- **Prompt 8**: Góc thấp nhìn lên dãy pin lớn → Sử dụng cho: Dự án thương mại
- **Prompt 9**: Gia đình hạnh phúc → Sử dụng cho: Trang chủ, Residential

## Tối Ưu Hóa Performance

### 1. Tối Ưu Hóa File

- **Ảnh**: Nén xuống < 500KB, sử dụng WebP nếu có thể
- **Video**: Nén xuống < 5MB, codec H.264, resolution 1920x1080 hoặc thấp hơn

### 2. Lazy Loading

- Slide đầu tiên load `eager`
- Các slides khác load `lazy`

### 3. Mobile Optimization

- Tắt animation phức tạp trên mobile
- Giảm số lượng slides trên mobile
- Sử dụng ảnh thay vì video trên mobile

### 4. Browser Compatibility

```javascript
// Kiểm tra hỗ trợ video
const video = document.createElement('video');
const supportsVideo = video.canPlayType('video/mp4');
```

## Controls

### Play/Pause Button
- Click để tạm dừng/tiếp tục slideshow
- Icon tự động thay đổi

### Previous/Next Buttons
- Navigate giữa các slides
- Reset progress bar

### Progress Bar
- Hiển thị tiến trình slide hiện tại
- Màu gradient xanh lá

## Troubleshooting

### Video Không Phát

1. Kiểm tra browser có hỗ trợ autoplay
2. Đảm bảo video có thuộc tính `muted`
3. Kiểm tra đường dẫn file

### Slideshow Không Hiển Thị

1. Kiểm tra `data-page` attribute
2. Verify slideshow-config.js được load
3. Check console errors

### Performance Issues

1. Giảm số lượng slides
2. Giảm kích thước file media
3. Tăng interval time
4. Tắt animations phức tạp

## Best Practices

✅ Sử dụng 5-8 slides per page  
✅ Mix ảnh và video (ratio 60:40)  
✅ Interval 6-8 giây  
✅ File size < 3MB  
✅ Alt text rõ ràng  
✅ Test trên nhiều devices  

## Ví Dụ Hoàn Chỉnh

```html
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Trang Mẫu</title>
    <!-- Slideshow CSS -->
    <link rel="stylesheet" href="assets/css/slideshow.css" />
    <style>
        body { background-color: transparent !important; }
        section { background: rgba(255,255,255,0.75); backdrop-filter: blur(8px); }
    </style>
</head>
<body data-page="home">
    <!-- Background Slideshow -->
    <div id="slideshow-background" class="slideshow-background"></div>
    
    <!-- Your content here -->
    
    <!-- Slideshow Scripts -->
    <script src="assets/js/slideshow-config.js"></script>
    <script src="assets/js/slideshow.js"></script>
</body>
</html>
```

## Customization

### Thay Đổi Transition Speed

Trong `slideshow.css`:

```css
.slideshow-slide {
    transition: opacity 2s ease-in-out; /* Thay đổi 2s */
}
```

### Thay Đổi Interval

Trong `slideshow-config.js`:

```javascript
{ slides: mySlides, interval: 10000 } // 10 giây
```

### Thay Đổi Overlay

Trong `slideshow.css`:

```css
.slideshow-overlay {
    background: linear-gradient(to bottom, 
        rgba(0,0,0,0.5) 0%, 
        rgba(0,0,0,0.2) 50%, 
        rgba(0,0,0,0.6) 100%
    );
}
```

## Support

Nếu gặp vấn đề, kiểm tra:
1. Console errors trong Browser DevTools
2. Network tab để xem files có load được không
3. File paths có đúng không
4. Browser compatibility

---

**Tạo bởi**: HC Eco System Development Team  
**Ngày**: 2024  
**Version**: 1.0

