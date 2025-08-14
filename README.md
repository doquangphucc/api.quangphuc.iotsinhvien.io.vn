# Dòng thời gian cá nhân - Personal Timeline

Hệ thống quản lý mục tiêu và ước mơ cá nhân với giao diện trực quan theo thời gian.

## 🌟 Tính năng chính

### 📱 Dashboard (dashboard.html)
- **Timeline tre**: Hiển thị tổng quan các việc muốn làm và đồ muốn mua theo dòng thời gian
- **Navigation**: Điều hướng nhanh đến các trang chuyên biệt
- **Real-time data**: Tải dữ liệu thực tế từ API, không còn hardcode
- **Responsive design**: Tương thích với mọi thiết bị
- **Animation**: Hiệu ứng lá rơi mùa thu đẹp mắt

### 📝 Quản lý việc muốn làm (all-tasks.html)
- **Danh sách đầy đủ**: Xem tất cả việc muốn làm với thông tin chi tiết
- **Lọc và phân loại**: 
  - Tất cả việc
  - Chưa hoàn thành
  - Đã hoàn thành
- **Thống kê trực quan**: 
  - Tổng số việc
  - Số việc chưa xong
  - Số việc đã xong
  - Tỷ lệ hoàn thành
- **Thao tác nhanh**: 
  - Thêm việc mới
  - Đánh dấu hoàn thành/chưa hoàn thành
  - Xem ngày tạo và cập nhật

### 🛍️ Quản lý đồ muốn mua (all-wishes.html)
- **Danh sách shopping**: Xem tất cả đồ muốn mua
- **Quản lý giá tiền**: Hiển thị giá của các món đồ (nếu có)
- **Lọc theo trạng thái**:
  - Tất cả đồ
  - Chưa mua
  - Đã mua
- **Thống kê tài chính**: Theo dõi tỷ lệ mua hàng
- **Thao tác đơn giản**:
  - Thêm đồ mới (có thể kèm giá)
  - Đánh dấu đã mua/chưa mua

## 🚀 Cải tiến so với phiên bản trước

### ✅ Đã tách riêng
- ❌ **Trước**: Tất cả chức năng đều nhét trong dashboard
- ✅ **Sau**: Dashboard chỉ là tổng quan, chi tiết có trang riêng

### ✅ Tối ưu hiệu suất
- ❌ **Trước**: Load hardcode data, timeline dài vô tận
- ✅ **Sau**: Chỉ load dữ liệu cần thiết từ API

### ✅ Trải nghiệm người dùng tốt hơn
- ❌ **Trước**: Khó quản lý khi có nhiều items
- ✅ **Sau**: Có bộ lọc, thống kê, phân trang rõ ràng

### ✅ Cấu trúc code sạch hơn
- ❌ **Trước**: Code rối, logic phức tạp
- ✅ **Sau**: Mỗi trang có chức năng riêng biệt, dễ maintain

## 📋 Cấu trúc tệp

```
├── dashboard.html          # Trang chủ - tổng quan timeline
├── all-tasks.html         # Trang quản lý việc muốn làm
├── all-wishes.html        # Trang quản lý đồ muốn mua
├── tasks.html            # Trang cũ (có thể giữ làm backup)
├── wishes.html           # Trang cũ (có thể giữ làm backup)
├── index.html            # Trang đăng nhập
├── database_schema.sql   # Cấu trúc database
└── api/
    ├── config.php        # Cấu hình database
    ├── connect.php       # Kết nối database
    ├── add-task.php      # API thêm việc mới
    ├── add-wish.php      # API thêm đồ mới
    ├── get-tasks.php     # API lấy danh sách việc
    ├── get-wishes.php    # API lấy danh sách đồ
    ├── get-timeline.php  # API lấy timeline
    ├── toggle-complete.php # API toggle trạng thái
    ├── login.php         # API đăng nhập
    └── register.php      # API đăng ký
```

## 🎯 Hướng dẫn sử dụng

### 1. Truy cập Dashboard
- Vào `dashboard.html` để xem tổng quan
- Timeline hiển thị việc và đồ theo từng ngày
- Sử dụng sidebar để navigation nhanh

### 2. Quản lý việc muốn làm
- Bấm "📝 Xem tất cả việc muốn làm" từ dashboard
- Hoặc truy cập trực tiếp `all-tasks.html`
- Sử dụng bộ lọc để xem theo trạng thái
- Thêm việc mới bằng nút "➕ Thêm việc"

### 3. Quản lý đồ muốn mua
- Bấm "🛍️ Xem tất cả đồ muốn mua" từ dashboard  
- Hoặc truy cập trực tiếp `all-wishes.html`
- Có thể nhập giá tiền khi thêm đồ mới
- Theo dõi thống kê mua sắm

## 🔧 Triển khai

### Cài đặt Database
1. Tạo database MySQL
2. Import file `database_schema.sql`
3. Cấu hình kết nối trong `api/config.php`

### API Endpoints
- `GET /api/connect.php` - Kiểm tra kết nối DB
- `POST /api/register.php` - Đăng ký tài khoản
- `POST /api/login.php` - Đăng nhập
- `POST /api/add-task.php` - Thêm việc mới
- `POST /api/add-wish.php` - Thêm đồ mới
- `GET /api/get-tasks.php` - Lấy danh sách việc
- `GET /api/get-wishes.php` - Lấy danh sách đồ
- `GET /api/get-timeline.php` - Lấy timeline
- `POST /api/toggle-complete.php` - Toggle trạng thái

### Bảo mật
- Mật khẩu được hash bằng PHP `password_hash()`
- Validate input để tránh SQL injection
- HTTPS khuyến khích cho production

## 🎨 Theme và Styling

- **Màu chủ đạo**: Autumn/Thu (cam, vàng, nâu)
- **Animation**: Lá rơi mùa thu
- **Typography**: Font Merriweather cho cảm giác ấm áp
- **Layout**: Card-based design, grid responsive

## 📈 Roadmap tương lai

- [ ] Thêm tính năng search/tìm kiếm
- [ ] Export data ra Excel/PDF
- [ ] Thêm category cho tasks và wishes
- [ ] Notification/reminder system
- [ ] Dark mode toggle
- [ ] Multi-language support

