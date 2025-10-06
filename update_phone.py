import os
import glob

# Số điện thoại mới
new_phone = "0988919868"
new_phone_formatted = "0988 919 868"

# Tìm tất cả file HTML
html_files = glob.glob('**/*.html', recursive=True)

replacements = [
    ('0977247393', new_phone),
    ('0977 247 393', new_phone_formatted),
    ('tel:0977247393', f'tel:{new_phone}'),
]

for file_path in html_files:
    try:
        # Đọc file với UTF-8 encoding
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Thực hiện các thay thế
        original_content = content
        for old, new in replacements:
            content = content.replace(old, new)
        
        # Chỉ ghi lại nếu có thay đổi
        if content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"✅ Updated: {file_path}")
    except Exception as e:
        print(f"❌ Error processing {file_path}: {e}")

print("\n✅ Phone number update completed!")
