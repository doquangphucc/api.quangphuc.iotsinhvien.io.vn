# HƯỚNG DẪN SỬ DỤNG MEDIA GALLERY

## 📝 Tổng quan

Tính năng **Media Gallery** cho phép admin thêm **nhiều ảnh và video** vào các bài viết trong:
- **🏠 Trang chủ** (Home Posts)
- **📝 Bài giới thiệu** (Intro Posts)  
- **🏗️ Dự án** (Projects)

## 🔄 BƯỚC 1: Cập nhật Database

### Cách 1: Chạy script UPDATE (Đơn giản - Khuyến nghị)

```sql
-- Chạy file này trên phpMyAdmin hoặc MySQL client
source database/UPDATE_MEDIA_GALLERY.sql
```

Hoặc import file `database/UPDATE_MEDIA_GALLERY.sql` qua phpMyAdmin.

### Cách 2: Xóa và import lại toàn bộ (Nếu cần)

```bash
# Backup dữ liệu trước
mysqldump -u root -p nangluongmattroi > backup_before_update.sql

# Xóa database cũ
mysql -u root -p -e "DROP DATABASE IF EXISTS nangluongmattroi;"

# Tạo lại database
mysql -u root -p -e "CREATE DATABASE nangluongmattroi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema mới
mysql -u root -p nangluongmattroi < database/database_schema.sql

# Import dữ liệu
mysql -u root -p nangluongmattroi < database/database_data.sql
```

## ✅ BƯỚC 2: Kiểm tra cập nhật thành công

Chạy câu lệnh SQL này để kiểm tra:

```sql
USE nangluongmattroi;

-- Kiểm tra field media_gallery đã được thêm
DESCRIBE intro_posts;
DESCRIBE projects;
DESCRIBE home_posts;
```

Bạn phải thấy field `media_gallery` (type: TEXT) trong cả 3 bảng.

## 📸 BƯỚC 3: Sử dụng Media Gallery trong Admin

### 1. Truy cập Admin Panel
- Đăng nhập vào `html/admin.html`
- Chọn tab **Bài giới thiệu**, **Dự án**, hoặc **Trang chủ**

### 2. Thêm/Sửa bài viết

Khi mở modal thêm/sửa bài viết, bạn sẽ thấy section mới:

```
📸 Thư viện Media (Nhiều ảnh/video)
Thêm nhiều ảnh và video cho bài viết. Có thể sắp xếp thứ tự hiển thị.

[🖼️ Thêm Ảnh]  [🎥 Thêm Video]
```

### 3. Thêm ảnh/video

**Thêm ảnh:**
1. Click nút **🖼️ Thêm Ảnh**
2. Chọn file ảnh từ máy tính (max 50MB)
3. Ảnh sẽ được upload và hiển thị trong danh sách

**Thêm video:**
1. Click nút **🎥 Thêm Video**
2. Chọn file video từ máy tính (max 50MB)
3. Video sẽ được upload và hiển thị trong danh sách

### 4. Quản lý media

Mỗi item trong danh sách có các nút:
- **⬆️ Di chuyển lên** - Đổi thứ tự lên trên
- **⬇️ Di chuyển xuống** - Đổi thứ tự xuống dưới
- **👁️ Xem trước** - Xem preview ảnh/video
- **🗑️ Xóa** - Xóa media khỏi gallery

### 5. Lưu bài viết

Click **💾 Lưu bài viết** để lưu tất cả thay đổi, bao gồm media gallery.

## 🔧 Cấu trúc dữ liệu

Media gallery được lưu dưới dạng JSON trong field `media_gallery`:

```json
[
  {
    "type": "image",
    "url": "/uploads/intro_images/abc123.jpg",
    "order": 1
  },
  {
    "type": "video", 
    "url": "/uploads/intro_videos/xyz456.mp4",
    "order": 2
  },
  {
    "type": "image",
    "url": "/uploads/intro_images/def789.png",
    "order": 3
  }
]
```

### Các trường:
- **type**: `"image"` hoặc `"video"`
- **url**: Đường dẫn file đã upload
- **order**: Thứ tự hiển thị (số càng nhỏ càng hiển thị trước)

## 📂 Files đã thay đổi

### 1. Database
- ✅ `database/database_schema.sql` - Thêm field `media_gallery`
- ✅ `database/UPDATE_MEDIA_GALLERY.sql` - Script cập nhật nhanh

### 2. Frontend
- ✅ `html/admin.html` - Thêm UI media gallery trong 3 modal
- ✅ `assets/js/admin-media-gallery.js` - Class quản lý media gallery

### 3. Backend (Cần tạo thêm)
Bạn cần tạo các file PHP sau trong `api/admin/`:

```
api/admin/upload_intro_media.php    - Upload ảnh/video cho Intro Posts
api/admin/upload_project_media.php  - Upload ảnh/video cho Projects  
api/admin/upload_home_media.php     - Upload ảnh/video cho Home Posts (đã có)
```

## 🎯 Lợi ích

### Trước khi có Media Gallery:
- ❌ Chỉ upload được 1 ảnh + 1 video
- ❌ Không thể thay đổi thứ tự
- ❌ Khó quản lý nhiều media

### Sau khi có Media Gallery:
- ✅ Upload **không giới hạn** số lượng ảnh/video
- ✅ **Sắp xếp thứ tự** dễ dàng bằng nút ⬆️⬇️
- ✅ **Xem trước** trực tiếp trong admin
- ✅ **Xóa** media không cần thiết ngay lập tức
- ✅ Dữ liệu được lưu dạng JSON, dễ mở rộng

## 🚀 Hiển thị Media Gallery ở Frontend

Để hiển thị media gallery ở frontend, bạn cần parse JSON và render:

```javascript
// Example: Hiển thị intro post với media gallery
const post = {
  title: "HC Eco System",
  description: "...",
  media_gallery: '[{"type":"image","url":"/uploads/img1.jpg","order":1},...]'
};

const mediaItems = JSON.parse(post.media_gallery || '[]');

mediaItems.forEach(item => {
  if (item.type === 'image') {
    // Render image
    console.log(`<img src="${item.url}" alt="...">`);
  } else if (item.type === 'video') {
    // Render video
    console.log(`<video src="${item.url}" controls></video>`);
  }
});
```

## ❓ Troubleshooting

### Lỗi: "Field media_gallery doesn't exist"
→ Chưa chạy UPDATE script. Hãy chạy `database/UPDATE_MEDIA_GALLERY.sql`

### Lỗi: "Upload failed - 413 Request Entity Too Large"
→ File quá lớn (>50MB). Nén file hoặc chọn file nhỏ hơn.

### Lỗi: "Cannot read property 'forEach' of null"
→ Field `media_gallery` là NULL. Set default value `[]` trong code.

### Media không hiển thị sau khi upload
→ Kiểm tra quyền folder `uploads/` phải có write permission.

## 📞 Hỗ trợ

Nếu gặp vấn đề, hãy kiểm tra:
1. Console log trong trình duyệt (F12)
2. PHP error log trong server
3. Database field `media_gallery` có tồn tại không
4. Quyền write vào folder uploads

---

**Tác giả:** AI Assistant  
**Phiên bản:** 1.0  
**Ngày:** 2025-10-28

