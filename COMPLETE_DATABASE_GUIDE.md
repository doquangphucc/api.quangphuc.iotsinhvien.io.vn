# HƯỚNG DẪN SỬ DỤNG FILE SQL HOÀN CHỈNH

## 📁 File: `complete_database.sql`

**Đây là file SQL duy nhất chứa TẤT CẢ bảng và dữ liệu cho hệ thống HC Eco System.**

## 🚀 CÁCH SỬ DỤNG

### Bước 1: Chuẩn Bị Database
1. **Mở phpMyAdmin**
2. **Tạo database mới** (nếu chưa có): `nangluongmattroi`
3. **Chọn database** `nangluongmattroi`

### Bước 2: Import File SQL
1. **Click tab "Import"**
2. **Chọn file** `complete_database.sql`
3. **Click "Go"** để import

### Bước 3: Xác Nhận Import Thành Công
Sau khi import, bạn sẽ thấy thông báo:
```
Database setup completed successfully!
Total tables created: 11
Sample data inserted for testing
Ready for HC Eco System!
```

## 📋 DANH SÁCH BẢNG ĐƯỢC TẠO

### 1. **Bảng Chính**
- ✅ `users` - Người dùng
- ✅ `products` - Sản phẩm (30 sản phẩm mẫu)
- ✅ `tinh` - Tỉnh/Thành phố (61 tỉnh)
- ✅ `phuong` - Phường/Xã (mẫu)

### 2. **Bảng Đơn Hàng**
- ✅ `orders` - Đơn hàng
- ✅ `order_items` - Chi tiết đơn hàng
- ✅ `cart_items` - Giỏ hàng

### 3. **Bảng Lottery (Vòng Quay May Mắn)**
- ✅ `lottery_tickets` - Vé quay may mắn
- ✅ `lottery_rewards` - Phần thưởng vòng quay

### 4. **Bảng Khảo Sát**
- ✅ `solar_surveys` - Khảo sát điện mặt trời
- ✅ `survey_results` - Kết quả khảo sát

## 🎯 DỮ LIỆU MẪU ĐƯỢC TẠO

### **User Test**
- **Username:** `testuser`
- **Phone:** `0123456789`
- **Password:** `123456`
- **ID:** `1`

### **Lottery Tickets Test**
- User ID 1 có **3 vé quay** để test
- Tất cả đều ở trạng thái `active`

### **Products**
- **30 sản phẩm** đầy đủ từ tấm pin đến phụ kiện
- Bao gồm: Solar Panel, Inverter, Battery, Cabinet, Accessories

### **Địa Chỉ**
- **61 tỉnh/thành phố** của Việt Nam
- **Một số phường/xã** mẫu cho Hà Nội, Đà Nẵng, TP.HCM

## 🔧 SAU KHI IMPORT

### **Test Lottery System**
1. **Đăng nhập** với user test: `testuser` / `123456`
2. **Vào trang vòng quay:** `/html/vong-quay-may-man.html`
3. **Quay thử** - sẽ không còn lỗi 500!

### **Test Các Chức Năng Khác**
- ✅ Đăng ký/Đăng nhập
- ✅ Xem sản phẩm
- ✅ Thêm vào giỏ hàng
- ✅ Đặt hàng
- ✅ Khảo sát điện mặt trời
- ✅ Vòng quay may mắn
- ✅ Xem phần thưởng

## 🗑️ XÓA DỮ LIỆU CŨ (Nếu Cần)

**Nếu muốn import lại từ đầu:**

```sql
-- Xóa tất cả bảng (cẩn thận!)
DROP TABLE IF EXISTS survey_results;
DROP TABLE IF EXISTS solar_surveys;
DROP TABLE IF EXISTS lottery_rewards;
DROP TABLE IF EXISTS lottery_tickets;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS phuong;
DROP TABLE IF EXISTS tinh;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
```

**Sau đó import lại file `complete_database.sql`**

## ⚠️ LƯU Ý QUAN TRỌNG

### **1. Backup Trước Khi Import**
- Luôn backup database hiện tại trước khi import
- File SQL này sẽ **GHI ĐÈ** tất cả dữ liệu cũ

### **2. Kiểm Tra Database Name**
- Đảm bảo database tên `nangluongmattroi`
- Nếu khác tên, sửa dòng đầu file SQL:
```sql
USE your_database_name;
```

### **3. User Test**
- User test có password đã hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`
- Tương ứng với password gốc: `123456`

## 🎉 KẾT QUẢ MONG ĐỢI

Sau khi import thành công:
- ✅ **11 bảng** được tạo
- ✅ **Dữ liệu mẫu** được insert
- ✅ **Lottery system** hoạt động bình thường
- ✅ **Không còn lỗi 500** khi quay vòng quay
- ✅ **Tất cả chức năng** sẵn sàng để test

---

**File này thay thế hoàn toàn các file SQL riêng lẻ:**
- ❌ `database/database.sql`
- ❌ `database/rewards_table.sql` 
- ❌ `database/survey_tables.sql`
- ❌ `create_lottery_rewards_table.sql`

**Chỉ cần 1 file duy nhất: `complete_database.sql`** 🚀
