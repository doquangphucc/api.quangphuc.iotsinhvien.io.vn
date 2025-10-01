# -*- coding: utf-8 -*-
import codecs

# Read the file
with open('assets/js/shopping-cart.js', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the broken Vietnamese characters
replacements = [
    ('âŒ', '❌'),
    ('âœ…', '✅'),
    ('ðŸ"'', '🔒'),
    ('YÃªu cáº§u Ä'Äƒng nháº­p', 'Yêu cầu đăng nhập'),
    ('Vui lÃ²ng Ä'Äƒng nháº­p Ä'á»ƒ sá»­ dá»¥ng giá» hÃ ng.', 'Vui lòng đăng nhập để sử dụng giỏ hàng.'),
    ('Nháº¥n Ä'á»ƒ Ä'áº¿n trang Ä'Äƒng nháº­p', 'Nhấn để đến trang đăng nhập'),
    ('ÄÃ£ thÃªm', 'Đã thêm'),
    ('vÃ o giá» hÃ ng.', 'vào giỏ hàng.'),
    ('CÃ³ lá»—i xáº£y ra.', 'Có lỗi xảy ra.'),
    ('KhÃ´ng thá»ƒ thÃªm vÃ o giá» hÃ ng.', 'Không thể thêm vào giỏ hàng.'),
    ('Sáº£n pháº©m', 'Sản phẩm'),
    ('Chá»©c nÄƒng "Mua Ngay" sáº½ Ä'Æ°á»£c phÃ¡t triá»ƒn sau.', 'Chức năng "Mua Ngay" sẽ được phát triển sau.'),
]

for old, new in replacements:
    content = content.replace(old, new)

# Write back with UTF-8 encoding
with open('assets/js/shopping-cart.js', 'w', encoding='utf-8', newline='\n') as f:
    f.write(content)

print("File has been fixed!")
