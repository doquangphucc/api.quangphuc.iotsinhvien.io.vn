const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, 'assets', 'js', 'shopping-cart.js');
let content = fs.readFileSync(filePath, 'utf8');

// Replace with actual bytes
content = content.replace(/\u00E2\u0152\u008C/g, '\u274C'); // ❌
content = content.replace(/\u00E2\u009C\u0085/g, '\u2705'); // ✅  
content = content.replace(/\u00F0\u0178\u201D\u2018/g, '\uD83D\uDD12'); // 🔒
content = content.replace(/Y\u00C3\u00AAu c\u00E1\u00BA\u00A7u \u00C4\u2019\u00C4\u0192ng nh\u00E1\u00BA\u00ADp/g, 'Yêu cầu đăng nhập');
content = content.replace(/Vui l\u00C3\u00B2ng \u00C4\u2019\u00C4\u0192ng nh\u00E1\u00BA\u00ADp \u00C4\u2019\u00E1\u00BB\u201D s\u00E1\u00BB\u00AD d\u00E1\u00BB\u00A5ng gi\u00E1\u00BB h\u00C3 ng\./g, 'Vui lòng đăng nhập để sử dụng giỏ hàng.');
content = content.replace(/Nh\u00E1\u00BA\u00A5n \u00C4\u2019\u00E1\u00BB\u201D \u00C4\u2019\u00E1\u00BA\u00BFn trang \u00C4\u2019\u00C4\u0192ng nh\u00E1\u00BA\u00ADp/g, 'Nhấn để đến trang đăng nhập');
content = content.replace(/\u00C4\u201D\u00C3\u00A3 th\u00C3\u00AAm/g, 'Đã thêm');
content = content.replace(/v\u00C3 o gi\u00E1\u00BB h\u00C3 ng\./g, 'vào giỏ hàng.');
content = content.replace(/C\u00C3\u00B3 l\u00E1\u00BB\u2014i x\u00E1\u00BA\u00A3y ra\./g, 'Có lỗi xảy ra.');
content = content.replace(/Kh\u00C3\u00B4ng th\u00E1\u00BB\u201D th\u00C3\u00AAm v\u00C3 o gi\u00E1\u00BB h\u00C3 ng\./g, 'Không thể thêm vào giỏ hàng.');
content = content.replace(/S\u00E1\u00BA\u00A3n ph\u00E1\u00BA\u00A9m/g, 'Sản phẩm');
content = content.replace(/Ch\u00E1\u00BB\u00A3c n\u00C4\u0192ng "Mua Ngay" s\u00E1\u00BA\u00BD \u00C4\u2019\u00C6\u00B0\u00E1\u00BB\u00A3c ph\u00C3\u00A1t tri\u00E1\u00BB\u201Dn sau\./g, 'Chức năng "Mua Ngay" sẽ được phát triển sau.');

fs.writeFileSync(filePath, content, 'utf8');
console.log('✅ File đã được sửa bằng unicode codes!');
