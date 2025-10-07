# ✅ FIXED: Bill Breakdown Hiển Thị "0 kWh"

## 🐛 Vấn đề
Bảng "Phân Tích Hóa Đơn Điện" hiển thị sai:
```
Bậc 1: 0 kWh      1.984đ/kWh    0đ  ❌
Bậc 2: 0 kWh      2.050đ/kWh    0đ  ❌
```

## 🔍 Nguyên nhân - FIELD NAME MISMATCH!

### API trả về (từ save_survey.php):
```javascript
billBreakdown: [
  {
    tier: 1,
    label: "Bậc 1: 0-50 kWh",
    kwhUsed: 50,        // ← Tên field: "kwhUsed"
    price: 1984,
    cost: 99200,        // ← Tên field: "cost"
    range: "1-50 kWh"
  }
]
```

### HTML đang tìm (SAI):
```javascript
${tier.kwh}      // ❌ Không tồn tại! (đúng là "kwhUsed")
${tier.amount}   // ❌ Không tồn tại! (đúng là "cost")
```

→ Kết quả: `undefined` → `0` sau khi `|| 0`

## ✅ Giải pháp

### Sửa HTML mapping:
```javascript
// TRƯỚC (SAI):
<td>${tier.kwh || 0} kWh</td>
<td>${formatPrice(tier.amount || 0)}</td>

// SAU (ĐÚNG):
<td>${tier.kwhUsed || tier.kwh || 0} kWh</td>
<td>${formatPrice(tier.cost || tier.amount || 0)}</td>
```

### Cải tiến thêm:
```javascript
// Dùng label từ API thay vì "Bậc 1, Bậc 2"
<td>${tier.label || `Bậc ${tier.tier || i + 1}`}</td>
```

## 📊 Kết quả mong đợi

Sau khi fix, bảng sẽ hiển thị:
```
Bậc thang              | Số kWh  | Đơn giá      | Thành tiền
-----------------------|---------|--------------|-------------
Bậc 1: 0-50 kWh        | 50 kWh  | 1.984đ/kWh   | 99.200đ    ✅
Bậc 2: 51-100 kWh      | 50 kWh  | 2.050đ/kWh   | 102.500đ   ✅
Bậc 3: 101-200 kWh     | 100 kWh | 2.380đ/kWh   | 238.000đ   ✅
Bậc 4: 201-300 kWh     | 100 kWh | 2.998đ/kWh   | 299.800đ   ✅
Bậc 5: 301-400 kWh     | 100 kWh | 3.350đ/kWh   | 335.000đ   ✅
Bậc 6: Từ 401 kWh...   | 123 kWh | 3.460đ/kWh   | 425.500đ   ✅
```

## 🧪 Cách test

### Bước 1: Reload trang
```
https://api.quangphuc.iotsinhvien.io.vn/html/survey_history.html
```
Bấm **Ctrl+F5**

### Bước 2: Mở survey để xem
Click vào một survey card để expand

### Bước 3: Kiểm tra bảng "Phân Tích Hóa Đơn Điện"
- ✅ Cột "Số kWh" hiển thị số > 0
- ✅ Cột "Thành tiền" hiển thị giá > 0đ
- ✅ Label hiển thị đầy đủ "Bậc 1: 0-50 kWh" thay vì chỉ "Bậc 1"

## 📝 Technical Details

### Field Mapping Table
| Hiển thị HTML | API Field (Primary) | API Field (Fallback) | Giá trị mẫu |
|---------------|---------------------|----------------------|-------------|
| Bậc thang     | `tier.label`        | `tier.tier` hoặc `i+1` | "Bậc 1: 0-50 kWh" |
| Số kWh        | `tier.kwhUsed`      | `tier.kwh`           | 50 |
| Đơn giá       | `tier.price`        | -                    | 1984 |
| Thành tiền    | `tier.cost`         | `tier.amount`        | 99200 |

### Tại sao có fallback?
- `tier.kwhUsed || tier.kwh`: Hỗ trợ cả 2 format (mới & cũ)
- `tier.cost || tier.amount`: Tương thích nếu field name thay đổi
- `tier.label || \`Bậc ${tier.tier}\``: Fallback nếu không có label

## 📦 Deploy Info

```
Commit: 7e00d6b
Message: "Fix billBreakdown field mapping: kwh->kwhUsed, amount->cost"
Files changed:
  - html/survey_history.html (4 lines)

Mapping changes:
  ❌ tier.kwh      → ✅ tier.kwhUsed || tier.kwh
  ❌ tier.amount   → ✅ tier.cost || tier.amount
  ❌ "Bậc ${i+1}"  → ✅ tier.label || `Bậc ${tier.tier || i+1}`
```

## 🎯 Root Cause Analysis

### Vì sao xảy ra lỗi này?
1. **save_survey.php** lưu billBreakdown với structure:
   ```javascript
   {tier, label, kwhUsed, price, cost, range}
   ```

2. **HTML** được viết dựa trên giả định khác:
   ```javascript
   {kwh, price, amount}  // ← Giả định sai!
   ```

3. **Không có type checking** → Lỗi không được phát hiện sớm

### Bài học:
- ✅ Luôn kiểm tra console logs khi có undefined
- ✅ Document API response structure
- ✅ Sử dụng TypeScript hoặc JSDoc để type safety

---
**Ngày fix:** 2025-10-07  
**Status:** ✅ FIXED & DEPLOYED  
**Test:** Ready for testing
