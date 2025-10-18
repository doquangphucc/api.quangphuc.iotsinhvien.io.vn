# Database Directory

## 📁 File SQL Chính

**File SQL duy nhất cần sử dụng:**
- `complete_database.sql` - **File SQL hoàn chỉnh chứa tất cả bảng và dữ liệu**

## 🚀 Cách Sử Dụng

1. **Mở phpMyAdmin**
2. **Chọn database:** `nangluongmattroi`
3. **Import file:** `complete_database.sql`
4. **Click "Go"** để import

## 📋 Nội Dung File SQL

File `complete_database.sql` chứa:
- ✅ **11 bảng** (users, products, orders, lottery_tickets, lottery_rewards, survey, v.v.)
- ✅ **Dữ liệu mẫu** đầy đủ
- ✅ **User test** với lottery tickets: `testuser` / `123456`
- ✅ **30 sản phẩm** mẫu
- ✅ **61 tỉnh/thành phố**
- ✅ **Bảng lottery_rewards** để fix lỗi 500

## 🎯 Test Ngay Sau Khi Import

1. **Đăng nhập:** `testuser` / `123456`
2. **Vào vòng quay:** `/html/vong-quay-may-man.html`
3. **Quay thử** - sẽ không còn lỗi 500!

## ⚠️ Lưu Ý

- **Chỉ sử dụng** `complete_database.sql`
- **Backup** database trước khi import
- File này sẽ **ghi đè** tất cả dữ liệu cũ
- **Không cần** các file SQL riêng lẻ nữa

---

**Tác giả:** AI Assistant  
**Ngày:** 2025-10-18  
**Mục đích:** Đơn giản hóa việc quản lý database
