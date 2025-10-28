# 🔐 HƯỚNG DẪN QUẢN LÝ USER VÀ PHÂN QUYỀN

## 📋 Tổng quan

Hệ thống quản lý user với phân quyền chi tiết cho từng module trong trang Admin.

## 🎯 Tính năng chính

### 1. **Hai cấp độ quyền:**
   - **👑 Admin**: Có toàn quyền truy cập tất cả module
   - **👤 User**: Phân quyền chi tiết theo từng module

### 2. **Phân quyền theo Module:**
Mỗi module có 4 loại quyền:
   - **👁️ Xem (View)**: Xem danh sách và chi tiết
   - **➕ Tạo mới (Create)**: Thêm mới
   - **✏️ Sửa (Edit)**: Chỉnh sửa
   - **🗑️ Xóa (Delete)**: Xóa

### 3. **Các Module được phân quyền:**
   - 📁 **Danh mục SP** (`categories`)
   - 📦 **Sản phẩm** (`products`)
   - 📋 **Khảo sát** (`survey`)
   - 🎁 **Gói sản phẩm** (`packages`)
   - 🛒 **Đơn hàng** (`orders`)
   - 🎫 **Vé quay** (`tickets`)
   - 🎁 **Phần thưởng** (`rewards`)
   - 📝 **Bài giới thiệu** (`intro-posts`)
   - 🏗️ **Dự án** (`projects`)
   - 🔧 **Dịch vụ** (`dich-vu`)

## 📂 Cấu trúc Database

### Bảng `users`
```sql
- id: INT (Primary Key)
- full_name: VARCHAR(255)
- username: VARCHAR(100) UNIQUE
- phone: VARCHAR(20) UNIQUE
- password: VARCHAR(255) (hashed)
- is_admin: BOOLEAN (0 = User, 1 = Admin)
- created_at: TIMESTAMP
- updated_at: TIMESTAMP
```

### Bảng `user_permissions`
```sql
- id: INT (Primary Key)
- user_id: INT (Foreign Key -> users.id)
- permission_key: VARCHAR(50) (tên module)
- can_view: BOOLEAN
- can_create: BOOLEAN
- can_edit: BOOLEAN
- can_delete: BOOLEAN
- created_at: TIMESTAMP
- updated_at: TIMESTAMP
- UNIQUE(user_id, permission_key)
```

## 🚀 Cách sử dụng

### **1. Truy cập trang Admin**
```
https://yourdomain.com/html/admin.html
```
Đăng nhập với tài khoản **Admin**

### **2. Quản lý User**
1. Click tab **"👥 Quản lý User"**
2. Click **"+ Thêm người dùng"**
3. Điền thông tin:
   - Họ tên
   - Username (dùng để đăng nhập)
   - Số điện thoại
   - Mật khẩu (tối thiểu 6 ký tự)

### **3. Phân quyền Admin**
- ✅ Check **"👑 Quyền Admin"** → User có toàn quyền
- ❌ Uncheck → Phân quyền chi tiết theo module

### **4. Phân quyền chi tiết**
Khi **KHÔNG phải Admin**, chọn quyền cho từng module:

**Ví dụ:** User chỉ quản lý Sản phẩm:
```
📦 Sản phẩm:
  ✅ Xem
  ✅ Tạo mới
  ✅ Sửa
  ❌ Xóa
```

### **5. Sửa User**
1. Click **"✏️ Sửa"** ở user cần sửa
2. Thay đổi thông tin
3. **Mật khẩu:** Để trống nếu không đổi
4. Click **"💾 Lưu"**

### **6. Xóa User**
1. Click **"🗑️ Xóa"**
2. Xác nhận xóa
3. ⚠️ **Lưu ý:** Không thể xóa chính tài khoản đang đăng nhập

## 🔧 API Endpoints

### 1. Get All Users
```
GET /api/admin/get_all_users.php
```
Lấy danh sách tất cả users

### 2. Get User Permissions
```
GET /api/admin/get_user_permissions.php?user_id={id}
```
Lấy danh sách quyền của 1 user

### 3. Save User
```
POST /api/admin/save_user.php
Body: {
  "id": null, // null = tạo mới, number = cập nhật
  "full_name": "Nguyễn Văn A",
  "username": "nguyenvana",
  "phone": "0123456789",
  "password": "123456", // null nếu không đổi mật khẩu (khi update)
  "is_admin": false,
  "permissions": {
    "products": {
      "can_view": true,
      "can_create": true,
      "can_edit": true,
      "can_delete": false
    },
    ...
  }
}
```

### 4. Delete User
```
POST /api/admin/delete_user.php
Body: {
  "id": 5
}
```

## 📝 Ví dụ phân quyền thực tế

### **Trường hợp 1: Nhân viên quản lý Sản phẩm**
```
✅ Sản phẩm: Xem, Tạo mới, Sửa
✅ Danh mục SP: Xem
❌ Các module khác: Không có quyền
```

### **Trường hợp 2: Nhân viên xử lý Đơn hàng**
```
✅ Đơn hàng: Xem, Sửa (cập nhật trạng thái)
✅ Sản phẩm: Xem
✅ Gói sản phẩm: Xem
❌ Các module khác: Không có quyền
```

### **Trường hợp 3: Nhân viên Content**
```
✅ Bài giới thiệu: Xem, Tạo mới, Sửa, Xóa
✅ Dự án: Xem, Tạo mới, Sửa, Xóa
✅ Dịch vụ: Xem, Tạo mới, Sửa
❌ Các module khác: Không có quyền
```

### **Trường hợp 4: Admin**
```
✅ Tất cả module: Full quyền
✅ Quản lý User: Thêm/Sửa/Xóa user và phân quyền
```

## ⚠️ Lưu ý quan trọng

1. **Chỉ Admin mới có quyền:**
   - Truy cập tab "Quản lý User"
   - Thêm/Sửa/Xóa user
   - Phân quyền cho user

2. **Không thể xóa chính mình:**
   - Admin đang đăng nhập không thể tự xóa tài khoản của mình

3. **Phân quyền linh hoạt:**
   - Có thể cho phép User chỉ Xem mà không cho Tạo/Sửa/Xóa
   - Có thể cho phép Sửa nhưng không cho Xóa
   - Tùy biến theo nhu cầu thực tế

4. **Bảo mật:**
   - Mật khẩu được hash bằng `password_hash()`
   - Session được quản lý an toàn
   - Tất cả API đều check quyền Admin

## 🔄 Import Database

Sau khi thêm bảng `user_permissions`, cần import lại database:

```bash
# 1. Xóa database cũ
DROP DATABASE nangluongmattroi;

# 2. Tạo database mới
CREATE DATABASE nangluongmattroi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Import schema
mysql -u root -p nangluongmattroi < database/database_schema.sql

# 4. Import data
mysql -u root -p nangluongmattroi < database/database_data.sql
```

## 🎨 Giao diện

- **Danh sách User:** Hiển thị đầy đủ thông tin với badge phân biệt Admin/User
- **Modal phân quyền:** Giao diện trực quan với checkbox cho từng quyền
- **Toggle Admin:** Khi check "Quyền Admin", phần phân quyền chi tiết bị disable
- **Toast notification:** Thông báo thành công/lỗi

## 📞 Hỗ trợ

Nếu có vấn đề, kiểm tra:
1. ✅ Database đã import đầy đủ 2 bảng: `users`, `user_permissions`
2. ✅ API files đã upload đúng vị trí: `api/admin/`
3. ✅ Session admin đang hoạt động
4. ✅ Check console browser để xem lỗi JavaScript
5. ✅ Check PHP error log để xem lỗi backend

