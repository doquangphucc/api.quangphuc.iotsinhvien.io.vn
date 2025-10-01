import os

file_path = r'assets\js\shopping-cart.js'

# Read as binary
with open(file_path, 'rb') as f:
    content = f.read()

# Decode as UTF-8
text = content.decode('utf-8')

# Replace broken strings
replacements = {
    '\u00e2\u0152\u008c': '\u274c',  # X emoji
    '\u00e2\u009c\u0085': '\u2705',  # Check emoji
    '\u00f0\u0178\u201d\u2018': '\U0001f512',  # Lock emoji
    'Y\u00c3\u00aau c\u00e1\u00ba\u00a7u \u00c4\u2019\u00c4\u0192ng nh\u00e1\u00ba\u00adp': 'Yêu cầu đăng nhập',
    'Vui l\u00c3\u00b2ng \u00c4\u2019\u00c4\u0192ng nh\u00e1\u00ba\u00adp \u00c4\u2019\u00e1\u00bb\u201d s\u00e1\u00bb\u00ad d\u00e1\u00bb\u00a5ng gi\u00e1\u00bb h\u00c3 ng.': 'Vui lòng đăng nhập để sử dụng giỏ hàng.',
    'Nh\u00e1\u00ba\u00a5n \u00c4\u2019\u00e1\u00bb\u201d \u00c4\u2019\u00e1\u00ba\u00bfn trang \u00c4\u2019\u00c4\u0192ng nh\u00e1\u00ba\u00adp': 'Nhấn để đến trang đăng nhập',
    '\u00c4\u201d\u00c3\u00a3 th\u00c3\u00aam': 'Đã thêm',
    'v\u00c3 o gi\u00e1\u00bb h\u00c3 ng.': 'vào giỏ hàng.',
    'C\u00c3\u00b3 l\u00e1\u00bb\u2014i x\u00e1\u00ba\u00a3y ra.': 'Có lỗi xảy ra.',
    'Kh\u00c3\u00b4ng th\u00e1\u00bb\u201d th\u00c3\u00aam v\u00c3 o gi\u00e1\u00bb h\u00c3 ng.': 'Không thể thêm vào giỏ hàng.',
    'S\u00e1\u00ba\u00a3n ph\u00e1\u00ba\u00a9m': 'Sản phẩm',
    'Ch\u00e1\u00bb\u00a3c n\u00c4\u0192ng "Mua Ngay" s\u00e1\u00ba\u00bd \u00c4\u2019\u00c6\u00b0\u00e1\u00bb\u00a3c ph\u00c3\u00a1t tri\u00e1\u00bb\u201dn sau.': 'Chức năng "Mua Ngay" sẽ được phát triển sau.'
}

for old, new in replacements.items():
    text = text.replace(old, new)

# Write back
with open(file_path, 'w', encoding='utf-8', newline='\n') as f:
    f.write(text)

print('Done! File fixed.')
