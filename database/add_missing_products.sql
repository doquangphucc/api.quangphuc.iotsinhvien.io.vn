-- Thêm 5 sản phẩm còn thiếu vào database
-- 4 sản phẩm từ product-cables.html + 1 sản phẩm từ product-luxpower-3phase-high.html
-- Chạy file này để cập nhật database với các sản phẩm mới

USE nangluongmattroi;

-- Thêm 4 sản phẩm phụ kiện còn thiếu từ product-cables.html
INSERT INTO products (id, name, category, brand, model, price, image_url, specifications) VALUES
(26, 'Bách Z', 'Accessories', 'HC Eco', 'Bách Z', 22000, 'assets/img/products/bachz.png', 'Chức năng: Mạ kẽm nhũng nóng áp mái tôn, Ứng dụng: Cố định khung giá đỡ trên mái tôn, Vật liệu: Thép mạ kẽm nhúng nóng'),
(27, 'Kẹp biên, Kẹp giữa tấm Pin', 'Accessories', 'HC Eco', 'Kẹp Pin', 11500, 'assets/img/products/kepbien-tamgiua.png', 'Chức năng: Cố định tấm pin vào khung giá đỡ, Loại: Kẹp biên và kẹp giữa, Vật liệu: Hợp kim nhôm anodized'),
(28, 'Jack Cắm MC4 1500VDC', 'Accessories', 'MC4', 'MC4 1500VDC', 14000, 'assets/img/products/jackcam.png', 'Model: MC4 1500VDC, Điện áp: 1500V DC, Dòng điện: 30A-40A, Tiêu chuẩn: IP67 chống nước/bụi'),
(29, 'Dây điện đấu nối tấm PIN', 'Accessories', 'HC Eco', 'Dây DC', 20000, 'assets/img/products/daydien.png', 'Loại: Dây DC chuyên dụng cho hệ thống mặt trời, Tiết diện: 4mm²-6mm², Điện áp: 1000-1500V DC, Vật liệu: Đồng nguyên chất XLPE chống UV'),

-- Thêm 1 sản phẩm còn thiếu từ product-luxpower-3phase-high.html (fix ID conflict)
(30, 'LUXPOWER Hybrid TRIP 25K', 'Inverter', 'LuxPower', 'TRIP 25K', 69000000, 'assets/img/products/luxpower-trip-25k.png', 'Công suất: Hybrid Trip-25K 25kW, Hỗ trợ pin: Acquy/Lithium 100-700V, Dòng sạc xả: 50A/50A, PV: 3 MPPT, On-grid/Backup: 50kW')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    category = VALUES(category),
    brand = VALUES(brand),
    model = VALUES(model),
    price = VALUES(price),
    image_url = VALUES(image_url),
    specifications = VALUES(specifications),
    updated_at = CURRENT_TIMESTAMP;

-- Kiểm tra kết quả
SELECT id, name, category, price, model 
FROM products 
WHERE id IN (26, 27, 28, 29, 30)
ORDER BY id;
