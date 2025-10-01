# Read file with UTF-8
$content = Get-Content -Path "assets\js\shopping-cart.js" -Encoding UTF8 -Raw

# Replace broken characters
$content = $content -replace '\u00E2\u0152\u008C', '\u274C'  # ❌
$content = $content -replace '\u00E2\u009C\u0085', '\u2705'  # ✅
$content = $content -replace '\u00F0\u0178\u201D\u2018', '\uD83D\uDD12'  # 🔒
$content = $content -replace 'Y\u00C3\u00AAu c\u00E1\u00BA\u00A7u \u00C4\u2019\u00C4\u0192ng nh\u00E1\u00BA\u00ADp', 'Yêu cầu đăng nhập'
$content = $content -replace 'Vui l\u00C3\u00B2ng \u00C4\u2019\u00C4\u0192ng nh\u00E1\u00BA\u00ADp \u00C4\u2019\u00E1\u00BB\u201D s\u00E1\u00BB\u00AD d\u00E1\u00BB\u00A5ng gi\u00E1\u00BB h\u00C3 ng\.', 'Vui lòng đăng nhập để sử dụng giỏ hàng.'
$content = $content -replace 'Nh\u00E1\u00BA\u00A5n \u00C4\u2019\u00E1\u00BB\u201D \u00C4\u2019\u00E1\u00BA\u00BFn trang \u00C4\u2019\u00C4\u0192ng nh\u00E1\u00BA\u00ADp', 'Nhấn để đến trang đăng nhập'
$content = $content -replace '\u00C4\u201D\u00C3\u00A3 th\u00C3\u00AAm', 'Đã thêm'
$content = $content -replace 'v\u00C3 o gi\u00E1\u00BB h\u00C3 ng\.', 'vào giỏ hàng.'
$content = $content -replace 'C\u00C3\u00B3 l\u00E1\u00BB\u2014i x\u00E1\u00BA\u00A3y ra\.', 'Có lỗi xảy ra.'
$content = $content -replace 'Kh\u00C3\u00B4ng th\u00E1\u00BB\u201D th\u00C3\u00AAm v\u00C3 o gi\u00E1\u00BB h\u00C3 ng\.', 'Không thể thêm vào giỏ hàng.'
$content = $content -replace 'S\u00E1\u00BA\u00A3n ph\u00E1\u00BA\u00A9m', 'Sản phẩm'
$content = $content -replace 'Ch\u00E1\u00BB\u00A9c n\u00C4\u0192ng "Mua Ngay" s\u00E1\u00BA\u00BD \u00C4\u2019\u00C6\u00B0\u00E1\u00BB\u00A3c ph\u00C3\u00A1t tri\u00E1\u00BB\u201Dn sau\.', 'Chức năng "Mua Ngay" sẽ được phát triển sau.'

# Write back with UTF-8 (no BOM)
[System.IO.File]::WriteAllText((Resolve-Path "assets\js\shopping-cart.js").Path, $content, (New-Object System.Text.UTF8Encoding $false))

Write-Host "Fixed UTF-8 encoding successfully!"
