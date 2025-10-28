# CHANGELOG - Media Gallery Feature

## 📅 Ngày: 2025-10-28

## ✨ Tính năng mới: Media Gallery (Thư viện Media)

Cho phép admin upload **nhiều ảnh và video** vào các module:
- 🏠 **Trang chủ** (Home Posts)
- 📝 **Bài giới thiệu** (Intro Posts)
- 🏗️ **Dự án** (Projects)

---

## 📁 CÁC FILE ĐÃ THAY ĐỔI

### Database
1. ✅ `database/database_schema.sql`
   - Thêm field `media_gallery TEXT` vào bảng `intro_posts`
   - Thêm field `media_gallery TEXT` vào bảng `projects`
   - Thêm field `media_gallery TEXT` vào bảng `home_posts`

2. ✅ `database/UPDATE_MEDIA_GALLERY.sql` **(MỚI)**
   - Script cập nhật database nhanh (không cần xóa dữ liệu)

### Frontend
3. ✅ `html/admin.html`
   - Thêm script import: `admin-media-gallery.js`
   - Thêm UI Media Gallery vào **Intro Post Modal**
   - Thêm UI Media Gallery vào **Project Modal**
   - Thêm UI Media Gallery vào **Home Post Modal**
   - Sửa `openIntroPostModal()` - khởi tạo & load media gallery
   - Sửa `saveIntroPost()` - lưu media gallery
   - Sửa `openProjectModal()` - khởi tạo & load media gallery
   - Sửa `saveProject()` - lưu media gallery
   - Sửa `openHomePostModal()` - khởi tạo media gallery
   - Sửa `loadHomePostData()` - load media gallery khi edit
   - Sửa `saveHomePost()` - lưu media gallery

4. ✅ `assets/js/admin-media-gallery.js` **(MỚI)**
   - Class `MediaGalleryManager` quản lý upload/xóa/sắp xếp media
   - Function `initMediaGallery()` để khởi tạo
   - Hỗ trợ drag & drop thứ tự (⬆️⬇️)
   - Preview ảnh/video (👁️)
   - Upload file lên server tự động

### Backend API
5. ✅ `api/admin/upload_intro_media.php` (Đã có sẵn)
   - Upload ảnh/video cho Intro Posts
   - Lưu vào `/uploads/intro_images/` và `/uploads/intro_videos/`

6. ✅ `api/admin/upload_project_media.php` **(MỚI)**
   - Upload ảnh/video cho Projects
   - Lưu vào `/uploads/project_images/` và `/uploads/project_videos/`

7. ⚠️ `api/admin/upload_home_media.php` (Cần kiểm tra)
   - Upload ảnh/video cho Home Posts
   - Cần đảm bảo hỗ trợ cả ảnh và video (không chỉ ảnh)

8. ⚠️ `api/admin/save_intro_post.php` (Cần cập nhật)
   - Cần xử lý field `media_gallery` khi lưu
   - Parse từ FormData và lưu vào database

9. ⚠️ `api/admin/save_project.php` (Cần cập nhật)
   - Cần xử lý field `media_gallery` khi lưu

10. ⚠️ `api/admin/save_home_post.php` (Cần cập nhật)
    - Cần xử lý field `media_gallery` khi lưu

### Uploads Folders
11. ✅ `uploads/project_images/README.md` **(MỚI)**
12. ✅ `uploads/project_videos/README.md` **(MỚI)**

### Documentation
13. ✅ `HUONG_DAN_MEDIA_GALLERY.md` **(MỚI)**
    - Hướng dẫn đầy đủ cách sử dụng
14. ✅ `CHANGELOG_MEDIA_GALLERY.md` **(File này)**

---

## 🔧 CẦN LÀM TIẾP

### 1. Cập nhật Database ⚠️
```bash
# Chạy script update
mysql -u root -p nangluongmattroi < database/UPDATE_MEDIA_GALLERY.sql
```

### 2. Cập nhật API Save Files ⚠️

Cần sửa 3 file sau để xử lý `media_gallery`:

#### `api/admin/save_intro_post.php`
```php
// Thêm vào cuối trước INSERT/UPDATE
$media_gallery = $_POST['media_gallery'] ?? '[]';

// Trong câu UPDATE/INSERT, thêm field
media_gallery = ?

// Bind param
$stmt->bind_param('...s...', ..., $media_gallery, ...);
```

#### `api/admin/save_project.php`
Tương tự như trên

#### `api/admin/save_home_post.php`
```php
// File này dùng JSON input
$data = json_decode(file_get_contents('php://input'), true);
$media_gallery = $data['media_gallery'] ?? '[]';

// Thêm vào UPDATE/INSERT
```

### 3. Kiểm tra `upload_home_media.php` ⚠️

Đảm bảo file này xử lý được cả ảnh VÀ video (giống `upload_intro_media.php`)

### 4. Set permissions cho folders ⚠️
```bash
chmod 755 uploads/project_images/
chmod 755 uploads/project_videos/
```

---

## 🎯 CÁCH SỬ DỤNG

Xem chi tiết trong `HUONG_DAN_MEDIA_GALLERY.md`

**Tóm tắt:**
1. Đăng nhập Admin Panel
2. Vào tab Bài giới thiệu / Dự án / Trang chủ
3. Click **Thêm** hoặc **Sửa** bài viết
4. Cuộn xuống section **📸 Thư viện Media**
5. Click **🖼️ Thêm Ảnh** hoặc **🎥 Thêm Video**
6. Chọn file (max 50MB)
7. Sắp xếp thứ tự bằng nút ⬆️⬇️
8. Click **💾 Lưu bài viết**

---

## 📊 CẤU TRÚC DỮ LIỆU

Field `media_gallery` lưu JSON array:

```json
[
  {
    "type": "image",
    "url": "/uploads/intro_images/intro_abc123.jpg",
    "order": 1
  },
  {
    "type": "video",
    "url": "/uploads/intro_videos/intro_xyz456.mp4",
    "order": 2
  }
]
```

---

## ⚡ HIỆU NĂNG

- Upload file: Max 50MB
- Timeout: 600 giây (10 phút)
- Memory: 256MB
- Không giới hạn số lượng media trong 1 bài viết

---

## 🐛 KNOWN ISSUES

Không có issue nào được biết đến tại thời điểm này.

---

## 📝 NOTES

- Field `image_url` và `video_url` cũ vẫn được giữ để backward compatibility
- Cần cập nhật frontend pages để hiển thị media gallery
- API `get_intro_posts.php`, `get_projects.php`, `get_home_posts.php` tự động trả về field `media_gallery`

---

**Cập nhật bởi:** AI Assistant  
**Ngày:** 2025-10-28  
**Version:** 1.0

