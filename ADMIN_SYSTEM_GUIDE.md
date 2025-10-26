# Hướng Dẫn Hệ Thống Admin - HC Eco System

## 🎯 Tổng Quan

Hệ thống admin cho phép quản lý toàn bộ website bán hàng năng lượng mặt trời, bao gồm:
- Quản lý danh mục và sản phẩm
- Duyệt đơn hàng và tặng vé quay
- Quản lý vé quay may mắn
- Quản lý phần thưởng vòng quay

## 📊 Database Changes

### Bảng Mới (17 bảng total)
1. **product_categories** - Danh mục sản phẩm
2. **packages** & **package_categories** & **package_items** - Gói sản phẩm
3. **vouchers** - Mã giảm giá
4. **reward_templates** - Mẫu phần thưởng
5. **orders** (updated) - Thêm voucher, approval workflow
6. **lottery_tickets** (updated) - Thêm pre_assigned_reward_id
7. **lottery_rewards** (updated) - Loại mới: voucher, cash, gift

### Thay Đổi Quan Trọng
- **users**: Thêm cột `is_admin` (BOOLEAN)
- **products**: Thêm `category_id`, `price_installation`
- **orders**: Thêm `subtotal`, `voucher_code`, `discount_amount`, `order_status`, `approved_by`, `approved_at`

## 🔐 Tài Khoản Admin

**Username**: `admin`
**Password**: `admin123`

Để đăng nhập vào admin panel, truy cập: `/html/admin.html`

## 📡 API Endpoints

### Admin Authentication
- `GET /api/admin/check_admin.php` - Kiểm tra quyền admin

### Categories Management
- `GET /api/admin/get_categories.php` - Lấy danh sách danh mục
- `POST /api/admin/save_category.php` - Thêm/sửa danh mục
- `POST /api/admin/delete_category.php` - Xóa danh mục

### Products Management
- `GET /api/admin/get_products.php?category_id={id}` - Lấy sản phẩm
- `POST /api/admin/save_product.php` - Thêm/sửa sản phẩm
- `POST /api/admin/delete_product.php` - Xóa sản phẩm

### Orders Management
- `GET /api/admin/get_orders.php?status={status}` - Lấy đơn hàng
- `POST /api/admin/approve_order.php` - Duyệt đơn & tặng vé

### Lottery Tickets Management
- `GET /api/admin/get_tickets.php?user_id={id}` - Lấy vé quay
- `POST /api/admin/save_ticket.php` - Thêm/sửa vé
- `POST /api/admin/delete_ticket.php` - Xóa vé

### Reward Templates Management
- `GET /api/admin/get_reward_templates.php` - Lấy mẫu phần thưởng
- `POST /api/admin/save_reward_template.php` - Thêm/sửa phần thưởng
- `POST /api/admin/delete_reward_template.php` - Xóa phần thưởng

### Utilities
- `GET /api/admin/get_users.php` - Lấy danh sách user (cho dropdown)

## 🎨 Trang Admin (`/html/admin.html`)

### Chức Năng Chính

#### 1. Danh Mục Sản Phẩm
- Thêm/sửa/xóa danh mục
- Upload logo danh mục
- Bật/tắt danh mục
- Sắp xếp thứ tự hiển thị

#### 2. Sản Phẩm
- Thêm/sửa/xóa sản phẩm
- Chọn danh mục
- Nhập 2 loại giá: Giá niêm yết & Giá lắp đặt trọn gói
- Upload hình ảnh
- Bật/tắt sản phẩm
- Lọc theo danh mục

#### 3. Đơn Hàng
- Xem danh sách đơn hàng
- Lọc theo trạng thái
- **Duyệt đơn hàng**: Khi duyệt → Tự động tặng 1 vé quay cho khách
- Trạng thái: pending → approved → processing → completed

#### 4. Vé Quay May Mắn
- Xem danh sách vé của tất cả user
- Thêm vé thủ công cho user
- **Set phần thưởng sẵn**: Chọn phần thưởng mà user sẽ nhận khi quay vé đó
- Sửa/xóa vé
- Phân loại: Mua hàng, Khuyến mãi, Sự kiện

#### 5. Mẫu Phần Thưởng
- Tạo mẫu phần thưởng:
  - **Voucher**: Giảm giá X đồng cho lần mua sau
  - **Tiền mặt**: Nhận tiền mặt X đồng
  - **Quà tặng**: Mô tả quà + số lượng
- Bật/tắt phần thưởng
- Xóa phần thưởng

## 🔄 Workflow Mới

### Quy Trình Mua Hàng → Nhận Vé

```
1. Khách đặt hàng → order_status = 'pending'
2. Admin vào tab "Đơn hàng"
3. Xem chi tiết → Nhấn "Duyệt đơn hàng & Tặng vé quay"
4. Hệ thống:
   - Cập nhật order_status = 'approved'
   - Tạo lottery_ticket cho user
   - Nếu admin đã set phần thưởng sẵn → gán vào ticket
5. User vào "Vòng quay may mắn" → Quay vé
6. Nhận phần thưởng (voucher/cash/gift)
```

### Quy Trình Set Phần Thưởng Sẵn

```
1. Admin vào tab "Phần thưởng"
2. Tạo các mẫu phần thưởng
3. Vào tab "Vé quay"
4. Chọn user → Thêm vé
5. Chọn "Phần thưởng set sẵn" từ dropdown
6. Lưu
7. Khi user quay vé này → Nhận đúng phần thưởng đã set
```

## 📝 Cấu Trúc File

```
api/
  ├── admin/
  │   ├── check_admin.php
  │   ├── get_categories.php
  │   ├── save_category.php
  │   ├── delete_category.php
  │   ├── get_products.php
  │   ├── save_product.php
  │   ├── delete_product.php
  │   ├── get_orders.php
  │   ├── approve_order.php
  │   ├── get_tickets.php
  │   ├── save_ticket.php
  │   ├── delete_ticket.php
  │   ├── get_reward_templates.php
  │   ├── save_reward_template.php
  │   ├── delete_reward_template.php
  │   └── get_users.php
  └── auth_helpers.php (added is_admin function)

assets/js/
  └── admin.js (Admin panel logic)

html/
  └── admin.html (Admin interface)

database/
  ├── database_schema.sql (Updated with new tables)
  └── database_data.sql (Sample data with admin user)
```

## 🚀 Import Database

```bash
# 1. Tạo bảng
mysql -u username -p nangluongmattroi < database/database_schema.sql

# 2. Import dữ liệu mẫu
mysql -u username -p nangluongmattroi < database/database_data.sql
```

## ⚙️ Các Bước Tiếp Theo (Chưa hoàn thành)

### 1. Update Trang Pricing
- Hiển thị sản phẩm từ database
- Lọc theo danh mục
- Hiển thị cả 2 loại giá

### 2. Update Trang Đặt Hàng
- Thêm field nhập voucher code
- Tự động giảm giá khi apply voucher
- Kiểm tra voucher hợp lệ

### 3. Update Vòng Quay
- Kiểm tra xem vé có pre_assigned_reward_id không
- Nếu có → Trả về phần thưởng đó
- Nếu không → Random như cũ
- Tạo voucher code khi thưởng là voucher

### 4. Update Logic Đặt Hàng API
- `create_order.php`: Không tặng vé ngay
- Chỉ lưu order với status = 'pending'
- Đợi admin duyệt mới tạo ticket

## 💡 Tips

- Admin có thể tạo vé thủ công và set sẵn phần thưởng để làm event/giveaway
- Voucher code sẽ được tự động generate khi admin tạo phần thưởng loại voucher
- Mỗi đơn hàng khi được duyệt chỉ tặng 1 vé duy nhất
- Admin có thể xem tất cả vé của tất cả users

## 🔒 Security Notes

- Tất cả API admin đều check `is_admin()` function
- Session-based authentication
- Chỉ admin mới access được `/html/admin.html`
- Non-admin redirect về login page

---

**Created**: October 2025
**Version**: 1.0
**Status**: In Development

