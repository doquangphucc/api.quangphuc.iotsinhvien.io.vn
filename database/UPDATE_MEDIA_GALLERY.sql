-- =====================================================
-- CẬP NHẬT DATABASE: THÊM MEDIA GALLERY
-- File: UPDATE_MEDIA_GALLERY.sql
-- Mô tả: Thêm field media_gallery vào các bảng để hỗ trợ nhiều ảnh/video
-- Cách dùng: Chạy script này trên database hiện tại
-- =====================================================

USE nangluongmattroi;

-- Thêm field media_gallery vào bảng intro_posts
ALTER TABLE intro_posts 
ADD COLUMN IF NOT EXISTS media_gallery TEXT COMMENT 'JSON array chứa nhiều ảnh/video: [{"type":"image","url":"...","order":1},{"type":"video","url":"...","order":2}]'
AFTER video_url;

-- Thêm field media_gallery vào bảng projects
ALTER TABLE projects 
ADD COLUMN IF NOT EXISTS media_gallery TEXT COMMENT 'JSON array chứa nhiều ảnh/video: [{"type":"image","url":"...","order":1},{"type":"video","url":"...","order":2}]'
AFTER video_url;

-- Thêm field media_gallery vào bảng home_posts
ALTER TABLE home_posts 
ADD COLUMN IF NOT EXISTS media_gallery TEXT COMMENT 'JSON array chứa nhiều ảnh/video: [{"type":"image","url":"...","order":1},{"type":"video","url":"...","order":2}]'
AFTER features;

-- Kiểm tra kết quả
SELECT 'Media gallery fields added successfully!' as message;
SELECT 'Columns updated: intro_posts, projects, home_posts' as info;

