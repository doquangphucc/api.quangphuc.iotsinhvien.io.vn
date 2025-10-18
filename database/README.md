# Database Directory

## 📁 File SQL Chính

**File SQL duy nhất cần sử dụng:**
- `../complete_database.sql` - **File SQL hoàn chỉnh chứa tất cả bảng và dữ liệu**

## 🗑️ Files Đã Xóa

Các file SQL cũ đã được xóa vì đã được hợp nhất vào `complete_database.sql`:

- ❌ `database.sql` - Đã hợp nhất
- ❌ `rewards_table.sql` - Đã hợp nhất  
- ❌ `survey_tables.sql` - Đã hợp nhất
- ❌ `create_tables.php` - Không cần thiết
- ❌ `fix_duplicate_tickets.sql` - Không cần thiết

## 🚀 Cách Sử Dụng

1. **Import file:** `complete_database.sql` vào phpMyAdmin
2. **Chọn database:** `nangluongmattroi`
3. **Click "Go"** để import

## 📋 Nội Dung File SQL

File `complete_database.sql` chứa:
- ✅ 11 bảng (users, products, orders, lottery_tickets, lottery_rewards, survey, v.v.)
- ✅ Dữ liệu mẫu đầy đủ
- ✅ User test với lottery tickets
- ✅ 30 sản phẩm mẫu
- ✅ 61 tỉnh/thành phố

## ⚠️ Lưu Ý

- **Chỉ sử dụng** `complete_database.sql`
- **Không cần** các file SQL riêng lẻ nữa
- **Backup** database trước khi import
- File này sẽ **ghi đè** tất cả dữ liệu cũ

---

**Tác giả:** AI Assistant  
**Ngày:** 2025-10-18  
**Mục đích:** Đơn giản hóa việc quản lý database
