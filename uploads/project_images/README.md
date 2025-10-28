# Project Images Folder

Folder này lưu trữ ảnh cho các dự án (Projects) được upload qua Media Gallery trong Admin Panel.

## Cấu trúc file
- File được đặt tên theo format: `project_{unique_id}_{timestamp}.{ext}`
- Extensions được hỗ trợ: jpg, jpeg, png, gif, webp

## Permissions
Folder này cần có quyền write (755 hoặc 775) để PHP có thể lưu file vào.

```bash
chmod 755 uploads/project_images/
```

