# DEBUG GUIDE - Phân Tích Hóa Đơn Điện & Tổng Chi Phí

## 🐛 Vấn đề phát hiện (từ ảnh chụp màn hình)

### 1. ❌ Phân Tích Hóa Đơn Điện - Hiển thị SAI
```
Bậc 1: 0 kWh      1.984đ/kWh    0đ  ❌
Bậc 2: 0 kWh      2.050đ/kWh    0đ  ❌
Bậc 3: 0 kWh      2.380đ/kWh    0đ  ❌
...
```

**Mong đợi:**
```
Bậc 1: 50 kWh     1.984đ/kWh    99.200đ  ✅
Bậc 2: 50 kWh     2.050đ/kWh    102.500đ ✅
```

### 2. ❌ Tổng Chi Phí Dự Án - BỊ KHUẤT
Section "💰 Tổng Chi Phí Dự Án" không hiển thị (bị cắt)

## ✅ Giải pháp đã áp dụng

### Fix 1: Tăng max-height cho survey-body
**Vấn đề:** CSS `.survey-card.expanded .survey-body` có `max-height: 2000px` → Không đủ để hiển thị tất cả sections

**Sửa:**
```css
.survey-card.expanded .survey-body {
    max-height: 5000px;  /* Tăng từ 2000px → 5000px */
}
```

### Fix 2: Thêm debug logs chi tiết
**Mục đích:** Xác định chính xác lỗi billBreakdown ở đâu

**Thêm logs:**
```javascript
// API Response
console.log('📦 API Response:', data);

// Survey parsing
console.log('=== Survey #0 ===');
console.log('billBreakdown raw:', survey.results.billBreakdown);

// After parsing
console.log('✅ Final billBreakdown:', survey.results.billBreakdown);
console.log('Array length:', survey.results.billBreakdown.length);

// Each tier
survey.results.billBreakdown.forEach((tier, i) => {
    console.log(`Tier ${i}:`, tier);
});
```

## 🧪 HƯỚNG DẪN DEBUG (QUAN TRỌNG!)

### Bước 1: Mở DevTools
1. Bấm **F12** hoặc **Ctrl+Shift+I**
2. Chọn tab **Console**

### Bước 2: Reload trang
1. Bấm **Ctrl+Shift+R** (hard reload)
2. Hoặc **Ctrl+F5**

### Bước 3: Tìm logs trong Console

#### Log 1: API Response
```javascript
📡 Response status: 200
📦 API Response: {
  success: true,
  surveys: [
    {
      id: 1,
      results: {
        billBreakdown: "..." // KIỂM TRA GIÁ TRỊ NÀY
      }
    }
  ]
}
```

**Kiểm tra `billBreakdown`:**
- ✅ Nếu là **string**: `"[{\"kwh\":50,\"price\":1806}]"` → OK, sẽ được parse
- ✅ Nếu là **array**: `[{kwh: 50, price: 1806}]` → OK, đã parse
- ❌ Nếu là **null**: `null` → KHÔNG CÓ DỮ LIỆU trong database
- ❌ Nếu là **"null"**: String "null" → Database lưu sai

#### Log 2: Parse Process
```javascript
=== Survey #0 ===
Survey ID: 1
Has results: true
billBreakdown raw: "[{\"kwh\":50,\"price\":1806,\"amount\":90300}]"
=== parseBillBreakdown called ===
Input: "[{\"kwh\":50,\"price\":1806,\"amount\":90300}]"
Type: string
✅ Parsed successfully: [{kwh: 50, price: 1806, amount: 90300}]
```

#### Log 3: Final Data
```javascript
✅ Final billBreakdown: [{kwh: 50, price: 1806, amount: 90300}, ...]
Array length: 6
Tier 0: {kwh: 50, price: 1806, amount: 90300}
Tier 1: {kwh: 50, price: 1866, amount: 93300}
```

## 🔍 PHÂN TÍCH CÁC TRƯỜNG HỢP

### Case 1: billBreakdown = null
**Console log:**
```
billBreakdown raw: null
billBreakdown is null/undefined
❌ No billBreakdown data
```

**Nguyên nhân:** Database không có dữ liệu
**Giải pháp:** Kiểm tra database

### Case 2: billBreakdown = "null" (string)
**Console log:**
```
billBreakdown raw: "null"
Type: string
❌ Error parsing billBreakdown: Unexpected token
```

**Nguyên nhân:** PHP lưu string "null" thay vì NULL
**Giải pháp:** Sửa API PHP

### Case 3: billBreakdown parse thành công nhưng vẫn hiển thị 0
**Console log:**
```
✅ Final billBreakdown: [{kwh: 50, price: 1806, amount: 90300}]
Tier 0: {kwh: 50, price: 1806, amount: 90300}
```

Nhưng vẫn hiển thị "0 kWh"

**Nguyên nhân:** HTML render trước khi parse xong
**Giải pháp:** Đã fix bằng cách parse TRƯỚC khi render

### Case 4: Tổng Chi Phí bị khuất
**Console log:** (Không có lỗi)

**Nguyên nhân:** CSS max-height quá nhỏ
**Giải pháp:** Đã tăng max-height lên 5000px ✅

## 📋 CHECKLIST DEBUG

Sau khi reload trang, kiểm tra:

### ✅ Checklist 1: Console Logs
- [ ] Có log "📡 Response status: 200"?
- [ ] Có log "📦 API Response"? 
- [ ] Có log "=== Survey #0 ==="?
- [ ] Có log "billBreakdown raw"?
- [ ] Có log "✅ Final billBreakdown"?
- [ ] Có log "Tier 0:", "Tier 1:" ...?

### ✅ Checklist 2: Data Values
- [ ] `billBreakdown raw` KHÔNG phải null?
- [ ] Parse thành công (có ✅)?
- [ ] Array length > 0?
- [ ] Mỗi tier có đầy đủ: kwh, price, amount?

### ✅ Checklist 3: Display
- [ ] Bảng "Phân Tích Hóa Đơn Điện" hiển thị?
- [ ] Các bậc hiển thị số kWh > 0?
- [ ] Thành tiền > 0đ?
- [ ] Section "Tổng Chi Phí" hiển thị đầy đủ?

## 🚨 NẾU VẪN CÒN LỖI

### Lỗi 1: Console log "billBreakdown raw: null"
→ **Kiểm tra database:**
```sql
SELECT id, bill_breakdown FROM survey_results WHERE id = 1;
```

Nếu kết quả là NULL hoặc rỗng:
→ **Tạo lại survey mới** từ trang khảo sát

### Lỗi 2: Parse error
→ **Kiểm tra API:**
```php
// File: api/get_survey_history.php, dòng ~163
'billBreakdown' => $row['bill_breakdown'] ? 
    json_decode($row['bill_breakdown'], true) : null
```

Đảm bảo có `json_decode(..., true)`

### Lỗi 3: Data đúng nhưng vẫn hiển thị 0
→ **Chụp màn hình Console** và gửi cho tôi:
- Toàn bộ logs
- Network tab → Response của get_survey_history.php

## 📦 Deploy Info

```
Commit: 53e423c
Message: "Fix: Increase max-height for survey body & add comprehensive debug logs"
Files changed:
  - html/survey_history.html (48 insertions, 10 deletions)

Changes:
  ✅ max-height: 2000px → 5000px
  ✅ Comprehensive debug logging
  ✅ Better error handling for billBreakdown
```

## 📸 Screenshot Request

Khi test, hãy chụp màn hình:
1. **Console logs** (toàn bộ)
2. **Network tab** → get_survey_history.php → Response
3. **Trang web** (phần Phân Tích Hóa Đơn + Tổng Chi Phí)

Gửi cho tôi để phân tích chính xác hơn!

---
**Ngày fix:** 2025-10-07  
**Status:** ✅ Deployed - Chờ test với debug logs
