const fs = require('fs');

// Read the file
const content = fs.readFileSync('assets/js/shopping-cart.js', 'utf8');

// Replace the broken Vietnamese characters
const replacements = [
    [/âŒ/g, '❌'],
    [/âœ…/g, '✅'],
    [/ðŸ"'/g, '🔒'],
    [/YÃªu cáº§u Ä'Äƒng nháº­p/g, 'Yêu cầu đăng nhập'],
    [/Vui lÃ²ng Ä'Äƒng nháº­p Ä'á»ƒ sá»­ dá»¥ng giá» hÃ ng\./g, 'Vui lòng đăng nhập để sử dụng giỏ hàng.'],
    [/Nháº¥n Ä'á»ƒ Ä'áº¿n trang Ä'Äƒng nháº­p/g, 'Nhấn để đến trang đăng nhập'],
    [/ÄÃ£ thÃªm/g, 'Đã thêm'],
    [/vÃ o giá» hÃ ng\./g, 'vào giỏ hàng.'],
    [/CÃ³ lá»—i xáº£y ra\./g, 'Có lỗi xảy ra.'],
    [/KhÃ´ng thá»ƒ thÃªm vÃ o giá» hÃ ng\./g, 'Không thể thêm vào giỏ hàng.'],
    [/Sáº£n pháº©m/g, 'Sản phẩm'],
    [/Chá»©c nÄƒng "Mua Ngay" sáº½ Ä'Æ°á»£c phÃ¡t triá»ƒn sau\./g, 'Chức năng "Mua Ngay" sẽ được phát triển sau.'],
];

let fixedContent = content;
for (const [pattern, replacement] of replacements) {
    fixedContent = fixedContent.replace(pattern, replacement);
}

// Write back with UTF-8 encoding
fs.writeFileSync('assets/js/shopping-cart.js', fixedContent, 'utf8');

console.log('File has been fixed!');
