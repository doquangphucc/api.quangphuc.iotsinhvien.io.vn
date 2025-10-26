# 📁 Thư mục Ảnh Sản Phẩm

Thư mục này chứa tất cả ảnh sản phẩm được upload từ trang admin.

## 📋 Quy tắc:
- **Tên file**: `product_[timestamp]_[random].[ext]`
- **Format hỗ trợ**: JPG, PNG, GIF, WEBP
- **Kích thước tối đa**: 5MB
- **Quyền thư mục**: 755 hoặc 777

## 🔧 Cách sử dụng:
1. Admin upload ảnh từ `admin.html` → Tab "Quản lý sản phẩm"
2. Ảnh sẽ tự động lưu vào thư mục này
3. Khi thêm/sửa sản phẩm, chọn ảnh từ dropdown

## ⚠️ Lưu ý:
- Thư mục phải có quyền ghi (writable)
- Nếu lỗi upload, chạy: `chmod 755 assets/img/products/` hoặc `chmod 777 assets/img/products/`

