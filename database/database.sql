USE nangluongmattroi;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    brand VARCHAR(100),
    model VARCHAR(100),
    power_rating VARCHAR(50),
    voltage VARCHAR(50),
    price DECIMAL(15, 2) NOT NULL,
    price_unit VARCHAR(20) DEFAULT 'VND',
    description TEXT,
    specifications TEXT,
    image_url VARCHAR(500),
    is_available BOOLEAN DEFAULT TRUE,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clean and insert fresh product data
-- This block is regenerated to ensure consistency with HTML product pages.
INSERT INTO products (id, name, category, brand, model, price, image_url, specifications) VALUES
-- From: product-solar-panels.html
(1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 'Solar Panel', 'Jinko Solar', 'Tiger Neo 590W', 2300000, 'assets/img/products/tam-pin-jinko-solar-590w-tiger-neo.jpg', 'Công suất: 590W, Công nghệ: N-Type Tiger Neo'),
(2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 'Solar Panel', 'Jinko Solar', 'Tiger Neo 630W', 2600000, 'assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png', 'Công suất: 630W, Công nghệ: N-Type Tiger Neo'),

-- From: product-luxpower-hybrid.html
(3, 'ECO Hybrid 5kW (Bản mới 2025)', 'Inverter', 'LuxPower', 'SNA5000WPV', 16500000, 'assets/img/products/eco-hybrid-5kw-sna5000wpv.png', 'Model: SNA5000WPV, On-grid/Back-up: 5kW'),
(4, 'ECO Hybrid 6kW', 'Inverter', 'LuxPower', 'SNA6000WPV', 17500000, 'assets/img/products/eco-hybrid-6kw-sna6000wpv.png', 'Model: SNA6000WPV, On-grid/Back-up: 6kW'),
(5, 'ECO Hybrid 12kW', 'Inverter', 'LuxPower', 'SNA 12K', 35500000, 'assets/img/products/eco-hybrid-12kw-sna12k.png', 'Model: SNA 12K, On-grid/Back-up: 12kW'),
(6, 'ECO Hybrid 14kW', 'Inverter', 'LuxPower', 'SNA EU 14K', 39000000, 'assets/img/products/eco-hybrid-14kw-sna-eu-14k.png', 'Model: SNA EU 14K, On-grid/Back-up: 14kW'),

-- From: product-luxpower-1pha.html
(7, 'Hybrid GEN-LB-EU 6K', 'Inverter', 'LuxPower', 'GEN-LB-EU 6K', 28900000, 'assets/img/products/hybrid-gen-lb-eu-6k.png', 'On-grid/Backup: 6kW, Sạc/xả: 6000W 125A/140A'),
(8, 'Hybrid GEN-LB-EU 8K', 'Inverter', 'LuxPower', 'GEN-LB-EU 8K', 48000000, 'assets/img/products/hybrid-gen-lb-eu-8k.png', 'On-grid/Backup: 8kW, Sạc/xả: 8000W 167A/167A'),
(9, 'Hybrid GEN-LB-EU 10K', 'Inverter', 'LuxPower', 'GEN-LB-EU 10K', 54000000, 'assets/img/products/hybrid-gen-lb-eu-10k.png', 'On-grid/Backup: 10kW, Sạc/xả: 10000W 210A/210A'),
(10, 'Hybrid LXP-12K 12kW', 'Inverter', 'LuxPower', 'LXP-12K', 59000000, 'assets/img/products/hybrid-lxp-12k.png', 'Hòa lưới On-grid: 12kW, Chạy độc lập Back-up: 12kW'),

-- From: product-luxpower-3phase.html
(11, 'Hybrid TriP2-LB-3P 12K 12kW', 'Inverter', 'LuxPower', 'TriP2-LB-3P 12K', 59000000, 'assets/img/products/hybrid-trip2-lb-3p-12k.png', 'On-grid/Backup: 12kW, 3 MPPT'),
(12, 'Hybrid TriP2-LB-3P 15K 15kW', 'Inverter', 'LuxPower', 'TriP2-LB-3P 15K', 63000000, 'assets/img/products/hybrid-trip2-lb-3p-15k.png', 'On-grid/Backup: 15kW, 3 MPPT'),

-- From: product-luxpower-3phase-high.html
(13, 'LUXPOWER Hybrid TRIP 10K', 'Inverter', 'LuxPower', 'TRIP 10K', 69000000, 'assets/img/products/hybrid-trip-10k.png', 'Công suất: 10KW, Điện áp: 3 pha áp cao'),
(14, 'LUXPOWER Hybrid TRIP 15K', 'Inverter', 'LuxPower', 'TRIP 15K', 89000000, 'assets/img/products/hybrid-trip-15k.png', 'Công suất: 15KW, Điện áp: 3 pha áp cao'),
(15, 'LUXPOWER Hybrid TRIP 20K', 'Inverter', 'LuxPower', 'TRIP 20K', 109000000, 'assets/img/products/hybrid-trip-20k.png', 'Công suất: 20KW, Điện áp: 3 pha áp cao'),

-- From: product-growatt-110kw.html
(16, 'Biến tần Growatt 110kW MAX', 'Inverter', 'Growatt', 'MAX 110KTL3-LV', 120000000, 'assets/img/products/growatt-110kw-max-real.png', 'Công suất: 110 kW, Hiệu suất: 98.8%'),

-- From: product-battery-storage.html
(17, 'Cell A-Cornex LiFePO4 16 Cell', 'Battery', 'A-Cornex', 'LiFePO4 16C', 50000000, 'assets/img/products/cell-a-cornex-lifepo4-16cell.png', 'Cấu hình: 16 Cell, Điện áp hệ thống: 52V'),

-- From: product-battery-byd.html
(18, 'Cell BYD 173ah LiFePO4', 'Battery', 'BYD', 'LiFePO4 173Ah', 15500000, 'assets/img/products/cell-byd-173ah-lifepo4.png', 'Điện áp: 51.2V, Dung lượng: 173ah (8.8kW)'),

-- From: product-electrical-cabinet.html
(19, 'Tủ điện Hybrid 1 pha 6kW', 'Electrical Cabinet', 'HC Eco', '1P-6KW', 1850000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 6 kW, 1 pha'),
(20, 'Tủ điện Hybrid 1 pha 12kW', 'Electrical Cabinet', 'HC Eco', '1P-12KW', 2850000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 12 kW, 1 pha'),
(21, 'Tủ điện Hybrid 1 pha 15kW', 'Electrical Cabinet', 'HC Eco', '1P-15KW', 3800000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 15 kW, 1 pha'),
(22, 'Tủ điện Hybrid 3 pha 15kW', 'Electrical Cabinet', 'HC Eco', '3P-15KW', 4850000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 15 kW, 3 pha'),

-- From: product-cables.html
(23, 'Dongles Lan LuxPower', 'Accessories', 'LuxPower', 'LAN Dongle', 1200000, 'assets/img/products/dongles-lan-luxpower.png', 'Kết nối internet qua dây mạng LAN'),
(24, 'Wifi LuxPower', 'Accessories', 'LuxPower', 'WiFi Module', 1000000, 'assets/img/products/wifi-luxpower.png', 'Kết nối internet qua WiFi 2.4GHz'),
(25, 'CT ngoài LuxPower SNA 6kW', 'Accessories', 'LuxPower', 'SNA 6kW CT', 800000, 'assets/img/products/ct-ngoai-luxpower-sna-6kw.png', 'Dòng điện: 100A/100mA'),
(26, 'Bách Z', 'Accessories', 'HC Eco', 'Bách Z', 22000, 'assets/img/products/bachz.png', 'Chức năng: Mạ kẽm nhũng nóng áp mái tôn, Ứng dụng: Cố định khung giá đỡ trên mái tôn, Vật liệu: Thép mạ kẽm nhúng nóng'),
(27, 'Kẹp biên, Kẹp giữa tấm Pin', 'Accessories', 'HC Eco', 'Kẹp Pin', 11500, 'assets/img/products/kepbien-tamgiua.png', 'Cố định tấm pin vào khung giá đỡ, hợp kim nhôm'),
(28, 'Jack Cắm MC4 1500VDC', 'Accessories', 'MC4', 'MC4 1500VDC', 14000, 'assets/img/products/jackcam.png', 'Kết nối dây cáp DC, 30A-40A, IP67'),
(29, 'Dây điện đấu nối tấm PIN', 'Accessories', 'HC Eco', 'Dây DC', 20000, 'assets/img/products/daydien.png', 'Dây DC chuyên dụng 4-6mm², 1000-1500V DC'),

-- From: product-luxpower-3phase-high.html (TRIP 25K was missing)
(30, 'LUXPOWER Hybrid TRIP 25K', 'Inverter', 'LuxPower', 'TRIP 25K', 69000000, 'assets/img/products/luxpower-trip-25k.png', 'Công suất: 25kW, Hỗ trợ pin: Acquy/Lithium 100-700V, 3 MPPT, On-grid/Backup: 50kW');


-- Create index for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_brand ON products(brand);
CREATE INDEX idx_products_available ON products(is_available);

-- Create tinh (provinces) table
CREATE TABLE IF NOT EXISTS tinh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_tinh VARCHAR(255) NOT NULL UNIQUE
);

-- Create phuong (wards) table
CREATE TABLE IF NOT EXISTS phuong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_phuong VARCHAR(255) NOT NULL,
    id_tinh INT NOT NULL,
    FOREIGN KEY (id_tinh) REFERENCES tinh(id)
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    city VARCHAR(255) NOT NULL,
    district VARCHAR(255) NOT NULL,
    address VARCHAR(500) NOT NULL,
    notes TEXT,
    total_amount DECIMAL(15, 2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    image_url VARCHAR(500),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Create cart_items table
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    UNIQUE KEY (user_id, product_id)
);

-- Insert provinces data
INSERT INTO tinh (id, ten_tinh) VALUES
(1, 'THÀNH PHỐ HÀ NỘI'),
(2, 'TỈNH BẮC NINH'),
(3, 'TỈNH QUẢNG NINH'),
(4, 'THÀNH PHỐ HẢI PHÒNG'),
(5, 'TỈNH HƯNG YÊN'),
(6, 'TỈNH NINH BÌNH'),
(7, 'TỈNH CAO BẰNG'),
(8, 'TỈNH TUYÊN QUANG'),
(9, 'TỈNH LÀO CAI'),
(10, 'TỈNH ĐIỆN BIÊN'),
(11, 'TỈNH LAI CHÂU'),
(12, 'TỈNH SƠN LA'),
(13, 'TỈNH YÊN BÁI'),
(14, 'TỈNH HOÀ BÌNH'),
(15, 'TỈNH THÁI NGUYÊN'),
(16, 'TỈNH LẠNG SƠN'),
(17, 'TỈNH QUẢNG NAM'),
(18, 'TỈNH QUẢNG NGÃI'),
(19, 'TỈNH BÌNH ĐỊNH'),
(20, 'TỈNH PHÚ YÊN'),
(21, 'TỈNH KHÁNH HÒA'),
(22, 'TỈNH NINH THUẬN'),
(23, 'TỈNH BÌNH THUẬN'),
(24, 'TỈNH KONTUM'),
(25, 'TỈNH GIA LAI'),
(26, 'TỈNH ĐẮK LẮK'),
(27, 'TỈNH ĐẮK NÔNG'),
(28, 'TỈNH LÂM ĐỒNG'),
(29, 'TỈNH BÌNH PHƯỚC'),
(30, 'TỈNH TÂY NINH'),
(31, 'TỈNH BÌNH DƯƠNG'),
(32, 'TỈNH ĐỒNG NAI'),
(33, 'TỈNH BÀ RỊA - VŨNG TÀU'),
(34, 'THÀNH PHỐ HỒ CHÍ MINH'),
(35, 'TỈNH LONG AN'),
(36, 'TỈNH TIỀN GIANG'),
(37, 'TỈNH BẾN TRE'),
(38, 'TỈNH TRÀ VINH'),
(39, 'TỈNH VĨNH LONG'),
(40, 'TỈNH ĐỒNG THÁP'),
(41, 'TỈNH AN GIANG'),
(42, 'TỈNH KIÊN GIANG'),
(43, 'TỈNH CÀ MAU'),
(44, 'TỈNH BẠC LIÊU'),
(45, 'TỈNH SÓC TRĂNG'),
(46, 'TỈNH HẬU GIANG'),
(47, 'TỈNH THÀNH PHỐ ĐÀ NẴNG'),
(48, 'TỈNH THỪA THIÊN HUẾ'),
(49, 'TỈNH QUẢNG TRỊ'),
(50, 'TỈNH QUẢNG BÌNH'),
(51, 'TỈNH HÀ TĨNH'),
(52, 'TỈNH NGHỆ AN'),
(53, 'TỈNH THANH HÓA'),
(54, 'TỈNH NAM ĐỊNH'),
(55, 'TỈNH THÁI BÌNH'),
(56, 'TỈNH HẢI DƯƠNG'),
(57, 'TỈNH HÀ NAM'),
(58, 'TỈNH VĨNH PHÚC'),
(59, 'TỈNH BẮC GIANG'),
(60, 'TỈNH BẮC KẠN'),
(61, 'TỈNH TUYÊN QUANG'),
(62, 'TỈNH PHÚ THỌ'),
(63, 'TỈNH HÒA BÌNH');

-- Insert some sample districts for testing (focusing on major cities)
INSERT INTO phuong (ten_phuong, id_tinh) VALUES
-- Hà Nội (id=1)
('Phường Hoàn Kiếm', 1),
('Phường Cửa Nam', 1),
('Phường Ba Đình', 1),
('Phường Ngọc Hà', 1),
('Phường Giảng Võ', 1),
('Phường Hai Bà Trưng', 1),
('Phường Vĩnh Tuy', 1),
('Phường Bạch Mai', 1),
('Phường Đống Đa', 1),
('Phường Kim Liên', 1),
('Phường Láng Thượng', 1),
('Phường Ô Chợ Dừa', 1),
('Phường Thịnh Quang', 1),
('Phường Trung Liệt', 1),
('Phường Cát Linh', 1),
('Phường Văn Miếu', 1),
('Phường Quốc Tử Giám', 1),
('Phường Láng Hạ', 1),
('Phường Khâm Thiên', 1),
('Phường Thổ Quan', 1),
('Phường Nam Đồng', 1),
('Phường Trung Phụng', 1),
('Phường Quang Trung', 1),
('Phường Tràng Tiền', 1),
('Phường Cửa Đông', 1),
('Phường Lý Thái Tổ', 1),
('Phường Hàng Bạc', 1),
('Phường Hàng Buồm', 1),
('Phường Hàng Đào', 1),
('Phường Hàng Giấy', 1),
('Phường Hàng Mã', 1),
('Phường Hàng Ngang', 1),
('Phường Hàng Rồng', 1),
('Phường Hàng Trống', 1),
('Phường Chương Dương Độ', 1),
('Phường Cửa Nam', 1),
('Phường Đồng Xuân', 1),
('Phường Hàng Bồ', 1),
('Phường Hàng Bông', 1),
('Phường Hàng Gai', 1),
('Phường Lý Thường Kiệt', 1),
('Phường Phan Chu Trinh', 1),
('Phường Phúc Tân', 1),
('Phường Trần Hưng Đạo', 1),
('Phường Tràng Thi', 1),

-- Đà Nẵng (id=47)
('Phường An Hải Bắc', 47),
('Phường An Hải Đông', 47),
('Phường An Hải Tây', 47),
('Phường An Hải Nam', 47),
('Phường An Hải Trung', 47),
('Phường Mân Thái', 47),
('Phường Nại Hiên Đông', 47),
('Phường Phước Mỹ', 47),
('Phường Thọ Quang', 47),
('Phường An Khê', 47),
('Phường Hải Châu I', 47),
('Phường Hải Châu II', 47),
('Phường Phước Ninh', 47),
('Phường Hòa Thuận Tây', 47),
('Phường Hòa Thuận Đông', 47),
('Phường Nam Dương', 47),
('Phường Bình Hiên', 47),
('Phường Bình Thuận', 47),
('Phường Hòa Cường Bắc', 47),
('Phường Hòa Cường Nam', 47),
('Phường Thạch Thang', 47),
('Phường Hải Châu', 47),
('Phường Thanh Bình', 47),
('Phường Thuận Phước', 47),
('Phường Hòa Minh', 47),
('Phường Hòa Quý', 47),
('Phường Hòa Thọ Đông', 47),
('Phường Hòa Thọ Tây', 47),
('Phường Hòa Phát', 47),
('Phường Hòa An', 47),
('Phường Hòa Phước', 47),
('Phường Hòa Thọ', 47),
('Phường Hòa Xuân', 47),
('Phường Hòa Khánh Bắc', 47),
('Phường Hòa Khánh Nam', 47),
('Phường Hòa Khánh', 47),
('Phường Hòa Minh', 47),
('Phường Hòa Quý', 47),
('Phường Hòa Thọ Đông', 47),
('Phường Hòa Thọ Tây', 47),
('Phường Hòa Phát', 47),
('Phường Hòa An', 47),
('Phường Hòa Phước', 47),
('Phường Hòa Thọ', 47),
('Phường Hòa Xuân', 47),
('Phường Hòa Khánh Bắc', 47),
('Phường Hòa Khánh Nam', 47),
('Phường Hòa Khánh', 47),

-- TP.HCM (id=34)
('Phường Bến Nghé', 34),
('Phường Bến Thành', 34),
('Phường Cầu Kho', 34),
('Phường Cầu Ông Lãnh', 34),
('Phường Cô Giang', 34),
('Phường Đa Kao', 34),
('Phường Nguyễn Cư Trinh', 34),
('Phường Nguyễn Thái Bình', 34),
('Phường Phạm Ngũ Lão', 34),
('Phường Tân Định', 34),
('Phường Đa Kao', 34),
('Phường Bến Nghé', 34),
('Phường Bến Thành', 34),
('Phường Cầu Kho', 34),
('Phường Cầu Ông Lãnh', 34),
('Phường Cô Giang', 34),
('Phường Đa Kao', 34),
('Phường Nguyễn Cư Trinh', 34),
('Phường Nguyễn Thái Bình', 34),
('Phường Phạm Ngũ Lão', 34),
('Phường Tân Định', 34);