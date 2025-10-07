# FIX: Bill Breakdown Hiển Thị "undefined kWh"

## 🐛 Vấn đề phát hiện
Trong ảnh chụp màn hình, bảng "Phân Tích Hóa Đơn Điện" hiển thị:
- Bậc 1: **undefined kWh** (thay vì giá trị thực tế)
- Bậc 2: **undefined kWh**
- Tất cả các bậc đều hiển thị "undefined"

## 🔍 Nguyên nhân

### Vấn đề 1: billBreakdown có thể là JSON string
API trả về `billBreakdown` từ database có thể là:
1. Chuỗi JSON: `"[{\"kwh\":50,\"price\":1806,\"amount\":90300}]"`
2. Hoặc đã được parse thành object/array

JavaScript cần parse string JSON trước khi sử dụng.

### Vấn đề 2: Không có xử lý lỗi
Code cũ có thể gặp lỗi nếu billBreakdown không đúng format.

## ✅ Giải pháp đã áp dụng

### 1. Thêm hàm parseBillBreakdown()
```javascript
function parseBillBreakdown(billBreakdown) {
    if (!billBreakdown) return null;
    if (typeof billBreakdown === 'string') {
        try {
            const parsed = JSON.parse(billBreakdown);
            console.log('Parsed billBreakdown:', parsed);
            return parsed;
        } catch (e) {
            console.error('Error parsing billBreakdown:', e);
            return null;
        }
    }
    return billBreakdown;
}
```

### 2. Parse trong displaySurveys()
```javascript
if (survey.results && survey.results.billBreakdown) {
    console.log('Before parse - type:', typeof survey.results.billBreakdown);
    survey.results.billBreakdown = parseBillBreakdown(survey.results.billBreakdown);
    console.log('After parse:', survey.results.billBreakdown);
}
```

### 3. Kiểm tra Array trước khi render
```javascript
${survey.results.billBreakdown && 
  Array.isArray(survey.results.billBreakdown) && 
  survey.results.billBreakdown.length > 0 ? `
    <table>...</table>
` : ''}
```

### 4. Thêm fallback values
```javascript
<td>${tier.kwh || 0} kWh</td>
<td>${formatPrice(tier.price || 0)}/kWh</td>
<td>${formatPrice(tier.amount || 0)}</td>
```

## 🧪 Cách kiểm tra

### Bước 1: Mở DevTools Console (F12)
Khi load trang, sẽ thấy logs:
```
Before parse - billBreakdown type: string
Parsed billBreakdown: [{kwh: 50, price: 1806, amount: 90300}, ...]
After parse - billBreakdown: [{kwh: 50, price: 1806, amount: 90300}, ...]
```

### Bước 2: Kiểm tra API response
Mở Network tab → Tìm request `get_survey_history.php`

### Bước 3: Reload trang
Refresh trang và kiểm tra bảng "Phân Tích Hóa Đơn Điện"

## 🎯 Kết quả mong đợi

Sau khi fix, bảng sẽ hiển thị đúng:
```
Bậc thang | Số kWh  | Đơn giá      | Thành tiền
----------|---------|--------------|------------
Bậc 1     | 50 kWh  | 1.984đ/kWh   | 99.200đ
Bậc 2     | 50 kWh  | 2.050đ/kWh   | 102.500đ
Bậc 3     | 100 kWh | 2.380đ/kWh   | 238.000đ
```

## 🚨 Nếu vẫn hiển thị "undefined"

### Kiểm tra 1: Console logs
Xem console có log gì:
- "Error parsing billBreakdown" → JSON string bị lỗi
- Không có log → billBreakdown = null

### Kiểm tra 2: Database
```sql
SELECT bill_breakdown FROM survey_results WHERE id = 1;
```

Kết quả phải là JSON array:
```json
[{"kwh":50,"price":1806,"amount":90300}]
```

### Kiểm tra 3: API PHP
File `api/get_survey_history.php` dòng 163:
```php
'billBreakdown' => $row['bill_breakdown'] ? 
    json_decode($row['bill_breakdown'], true) : null
```

## 📝 Files đã sửa
- `html/survey_history.html` (32 insertions, 6 deletions)

## 📦 Deploy
```
Commit: 8355744
Message: "Fix billBreakdown parsing - add JSON parse handler and debug logs"
Status: ✅ Pushed to GitHub
```

---
**Ngày sửa:** 2025-10-07  
**Trạng thái:** ✅ Fixed - Cần test trên production để xác nhận
