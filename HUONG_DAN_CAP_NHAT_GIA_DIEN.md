# Hướng Dẫn Cập Nhật Giá Điện EVN

## Tổng Quan

Hệ thống đã được nâng cấp để quản lý bảng giá điện EVN động, không còn fix cứng trong code.

## Cấu Trúc Mới

### 1. Database
- **Bảng mới**: `electricity_prices` 
- **Chức năng**: Lưu trữ bảng giá điện sinh hoạt EVN theo 6 bậc thang

### 2. API
- **Public API**: `api/get_electricity_prices_public.php` - Lấy giá điện (không cần đăng nhập)
- **Admin API**: 
  - `api/admin/get_electricity_prices.php` - Lấy danh sách giá điện (cần quyền admin)
  - `api/admin/save_electricity_prices.php` - Lưu/cập nhật giá điện (cần quyền admin)

### 3. Admin Panel
- **Tab Khảo sát** → **Bảng Giá Điện EVN**
- Có thể chỉnh sửa trực tiếp từng bậc giá
- Tự động tính giá có VAT khi nhập giá chưa VAT

### 4. Trang Khảo Sát
- Tự động load giá điện từ API khi trang khởi động
- Hiển thị ngày áp dụng động
- Fallback về giá mặc định nếu API lỗi

## Cách Sử Dụng

### Bước 1: Cập Nhật Database

Nếu bạn chưa import database mới, chạy lệnh sau:

```bash
# Xóa database cũ và import lại từ đầu
mysql -u root -p -e "DROP DATABASE IF EXISTS your_database_name;"
mysql -u root -p -e "CREATE DATABASE your_database_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p your_database_name < database/database_schema.sql
mysql -u root -p your_database_name < database/database_data.sql
```

### Bước 2: Truy Cập Admin Panel

1. Đăng nhập vào admin: `https://your-domain.com/html/admin.html`
2. Chọn tab **"📋 Khảo sát"**
3. Xem phần **"⚡ Bảng Giá Điện EVN"**

### Bước 3: Chỉnh Sửa Giá Điện

**Các trường có thể chỉnh sửa:**

| Trường | Mô tả | Ví dụ |
|--------|-------|-------|
| Tên bậc | Tên hiển thị | Bậc 1: 0-50 kWh |
| Từ kWh | Điểm bắt đầu | 0 |
| Đến kWh | Điểm kết thúc (0 = không giới hạn) | 50 |
| Giá chưa VAT | Giá gốc (đ/kWh) | 1984 |
| Giá có VAT 8% | Tự động tính hoặc nhập thủ công | 2143 |
| Ngày áp dụng | Ngày bắt đầu áp dụng | 2025-05-10 |
| Trạng thái | Đang dùng/Không dùng | ✓ |

**Lưu ý:**
- Khi thay đổi "Giá chưa VAT", hệ thống tự động tính "Giá có VAT = Giá chưa VAT × 1.08"
- Bậc 6 (từ 401 kWh trở lên): Để "Đến kWh" = 0 hoặc để trống
- Chỉ các bậc có trạng thái "Đang dùng" mới hiển thị trên trang khảo sát

### Bước 4: Lưu Thay Đổi

Nhấn nút **"💾 Lưu bảng giá"** để lưu tất cả thay đổi.

Hệ thống sẽ:
1. Cập nhật database
2. Trang khảo sát tự động load giá mới khi người dùng truy cập
3. Hiển thị thông báo thành công

## Kiểm Tra

### Kiểm tra trên trang khảo sát:

1. Truy cập: `https://your-domain.com/html/khao-sat-dien-mat-troi.html`
2. Xem phần **"Bảng Giá Điện Sinh Hoạt EVN"** 
3. Kiểm tra:
   - 6 bậc giá hiển thị đúng
   - Ngày áp dụng hiển thị đúng
   - Giá có VAT hiển thị đúng

### Kiểm tra tính toán:

1. Điền form khảo sát
2. Nhấn "Tính toán"
3. Xem phần **"Chi Tiết Tính Toán Số kWh Từ Tiền Điện"**
4. Kiểm tra giá từng bậc có khớp với bảng giá không

## Ví Dụ Cập Nhật Giá Điện Mới

**Tình huống:** EVN tăng giá điện từ ngày 01/06/2025

**Bước thực hiện:**

1. Đăng nhập admin
2. Vào tab "Khảo sát" → "Bảng Giá Điện EVN"
3. Cập nhật từng bậc:
   - Bậc 1: 2143đ → 2200đ
   - Bậc 2: 2214đ → 2280đ
   - ... (các bậc khác)
4. Cập nhật "Ngày áp dụng": 01/06/2025
5. Nhấn "Lưu bảng giá"
6. Xong! Người dùng sẽ thấy giá mới ngay lập tức

## Troubleshooting

### Lỗi: "Không tải được bảng giá điện"

**Nguyên nhân:** API không hoạt động hoặc database chưa có bảng `electricity_prices`

**Giải pháp:**
1. Kiểm tra database có bảng `electricity_prices` chưa
2. Import lại database nếu cần
3. Kiểm tra file `api/get_electricity_prices_public.php` có tồn tại không

### Lỗi: "Giá có VAT tự động tính sai"

**Nguyên nhân:** JavaScript calculateVATPrice() bị lỗi

**Giải pháp:**
- Nhập thủ công giá có VAT = Giá chưa VAT × 1.08
- Ví dụ: 2000 × 1.08 = 2160

### Lỗi: "Thay đổi không hiển thị trên trang khảo sát"

**Nguyên nhân:** Cache trình duyệt

**Giải pháp:**
1. Hard refresh: Ctrl + F5 (Windows) hoặc Cmd + Shift + R (Mac)
2. Xóa cache trình duyệt
3. Thử trình duyệt ẩn danh

## API Documentation

### GET /api/get_electricity_prices_public.php

**Response Success:**
```json
{
  "success": true,
  "prices": [
    {
      "id": 1,
      "tier": 1,
      "tier_name": "Bậc 1: 0-50 kWh",
      "kwh_from": 0,
      "kwh_to": 50,
      "price_no_vat": 1984.00,
      "price_with_vat": 2143.00,
      "effective_date": "2025-05-10",
      "notes": "Bậc tiêu thụ thấp nhất"
    }
    // ... 5 bậc khác
  ],
  "count": 6
}
```

### POST /api/admin/save_electricity_prices.php

**Request Body:**
```json
{
  "prices": [
    {
      "id": 1,
      "tier": 1,
      "tier_name": "Bậc 1: 0-50 kWh",
      "kwh_from": 0,
      "kwh_to": 50,
      "price_no_vat": 1984.00,
      "price_with_vat": 2143.00,
      "effective_date": "2025-05-10",
      "is_active": true,
      "notes": "Bậc tiêu thụ thấp nhất"
    }
    // ... 5 bậc khác
  ]
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Đã lưu bảng giá điện thành công!"
}
```

## Lưu Ý Quan Trọng

1. **Không sửa trực tiếp code JavaScript** - Tất cả giá điện được quản lý qua admin panel
2. **Backup database** trước khi cập nhật giá mới
3. **Test kỹ** sau khi thay đổi giá để đảm bảo tính toán chính xác
4. **Thông báo người dùng** khi có thay đổi giá điện lớn

## Hỗ Trợ

Nếu gặp vấn đề, liên hệ:
- Email: hcecosystem@gmail.com
- Hotline: 0969 397 434

