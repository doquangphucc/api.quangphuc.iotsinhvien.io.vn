# Home Post Images

This directory stores images uploaded for home posts (trang chủ).

## Directory Structure
- Files are named: `home_{uniqid}_{timestamp}.{ext}`
- Supported formats: JPG, PNG, GIF, WEBP
- Max file size: 50MB

## Permissions
Ensure this directory has write permissions:
```bash
chmod 755 uploads/home_images/
```

## API
Upload API: `/api/admin/upload_home_media.php`

