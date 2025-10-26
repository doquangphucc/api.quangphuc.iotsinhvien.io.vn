# Thư mục upload logo danh mục gói

Thư mục này dùng để lưu trữ logo của các danh mục gói.

## Quyền thư mục

Để upload ảnh được, hosting cần cấp quyền **777** hoặc **755** cho thư mục này.

## Cách cấp quyền

### 1. Qua File Manager (cPanel/Website Tool Kit)
1. Vào File Manager
2. Tìm thư mục: `assets/img/package-categories/`
3. Click chuột phải → Change Permissions
4. Chọn **755** hoặc **777**
5. Click Change Permissions

### 2. Qua SSH/Terminal
```bash
cd /path/to/website/assets/img/
chmod 755 package-categories/
# hoặc
chmod 777 package-categories/
```

### 3. Qua FTP
1. Kết nối FTP
2. Tìm thư mục `assets/img/package-categories/`
3. Click chuột phải → Change Permissions
4. Nhập **755** hoặc **777**
5. Apply

## Lưu ý

- **755**: Quyền an toàn hơn, chỉ owner mới có quyền ghi
- **777**: Quyền mở hơn, tất cả user có quyền ghi (không khuyến khích nếu không cần)
- Nếu gặp lỗi "Permission denied", hãy thử dùng **777** tạm thời

## Kiểm tra quyền

Sau khi cấp quyền, vào trang admin và thử upload logo danh mục gói để kiểm tra.

## Cấu trúc file

Logo sẽ được lưu với tên format:
```
package-category_{timestamp}_{uniqid}.{ext}
```

Ví dụ:
```
package-category_1737912000_abc123def456.jpg
```
