const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, 'assets', 'js', 'shopping-cart.js');
let content = fs.readFileSync(filePath, 'utf8');

// Simple string replacements
content = content.replace(/âŒ/g, '❌');
content = content.replace(/âœ…/g, '✅');
content = content.replace(/ðŸ"'/g, '🔒');
content = content.replace(/YÃªu cáº§u Ä'Äƒng nháº­p/g, 'Yêu cầu đăng nhập');
content = content.replace(/Vui lÃ²ng Ä'Äƒng nháº­p Ä'á»ƒ sá»­ dá»¥ng giá» hÃ ng\./g, 'Vui lòng đăng nhập để sử dụng giỏ hàng.');
content = content.replace(/Nháº¥n Ä'á»ƒ Ä'áº¿n trang Ä'Äƒng nháº­p/g, 'Nhấn để đến trang đăng nhập');
content = content.replace(/ÄÃ£ thÃªm/g, 'Đã thêm');
content = content.replace(/vÃ o giá» hÃ ng\./g, 'vào giỏ hàng.');
content = content.replace(/CÃ³ lá»—i xáº£y ra\./g, 'Có lỗi xảy ra.');
content = content.replace(/KhÃ´ng thá»ƒ thÃªm vÃ o giá» hÃ ng\./g, 'Không thể thêm vào giỏ hàng.');
content = content.replace(/Sáº£n pháº©m/g, 'Sản phẩm');
content = content.replace(/Chá»©c nÄƒng "Mua Ngay" sáº½ Ä'Æ°á»£c phÃ¡t triá»ƒn sau\./g, 'Chức năng "Mua Ngay" sẽ được phát triển sau.');

fs.writeFileSync(filePath, content, 'utf8');
console.log('✅ File đã được sửa thành công!');
