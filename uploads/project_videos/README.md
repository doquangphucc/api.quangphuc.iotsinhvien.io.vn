# Project Videos Folder

Folder này lưu trữ video cho các dự án (Projects) được upload qua Media Gallery trong Admin Panel.

## Cấu trúc file
- File được đặt tên theo format: `project_{unique_id}_{timestamp}.{ext}`
- Extensions được hỗ trợ: mp4, webm

## Permissions
Folder này cần có quyền write (755 hoặc 775) để PHP có thể lưu file vào.

```bash
chmod 755 uploads/project_videos/
```

## File size limit
- Maximum 50MB per file

