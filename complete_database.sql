-- =====================================================
-- HC ECO SYSTEM - COMPLETE DATABASE SETUP
-- File: complete_database.sql
-- Description: Tất cả bảng và dữ liệu cho hệ thống HC Eco
-- Usage: Import file này vào phpMyAdmin để setup toàn bộ database
-- =====================================================

USE nangluongmattroi;

-- =====================================================
-- 1. BẢNG USERS (Người dùng)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. BẢNG PRODUCTS (Sản phẩm)
-- =====================================================
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

-- =====================================================
-- 3. BẢNG TINH (Tỉnh/Thành phố)
-- =====================================================
CREATE TABLE IF NOT EXISTS tinh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_tinh VARCHAR(255) NOT NULL UNIQUE
);

-- =====================================================
-- 4. BẢNG PHUONG (Phường/Xã)
-- =====================================================
CREATE TABLE IF NOT EXISTS phuong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_phuong VARCHAR(255) NOT NULL,
    id_tinh INT NOT NULL,
    FOREIGN KEY (id_tinh) REFERENCES tinh(id)
);

-- =====================================================
-- 5. BẢNG ORDERS (Đơn hàng)
-- =====================================================
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

-- =====================================================
-- 6. BẢNG ORDER_ITEMS (Chi tiết đơn hàng)
-- =====================================================
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

-- =====================================================
-- 7. BẢNG CART_ITEMS (Giỏ hàng)
-- =====================================================
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

-- =====================================================
-- 8. BẢNG LOTTERY_TICKETS (Vé quay may mắn)
-- =====================================================
CREATE TABLE IF NOT EXISTS lottery_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT,
    ticket_type ENUM('purchase', 'bonus', 'promotion') DEFAULT 'purchase',
    status ENUM('active', 'used', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- =====================================================
-- 9. BẢNG LOTTERY_REWARDS (Phần thưởng vòng quay)
-- =====================================================
CREATE TABLE IF NOT EXISTS lottery_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_name VARCHAR(255) NOT NULL,
    reward_type VARCHAR(50) NOT NULL COMMENT 'discount, free_shipping, accessory, gift, no_prize',
    reward_value VARCHAR(100) DEFAULT NULL COMMENT 'Giá trị phần thưởng (%, tiền, mô tả)',
    reward_code VARCHAR(50) DEFAULT NULL COMMENT 'Mã voucher/gift code nếu có',
    reward_image VARCHAR(255) DEFAULT NULL COMMENT 'Hình ảnh phần thưởng',
    status ENUM('pending', 'used', 'expired') DEFAULT 'pending',
    ticket_id INT DEFAULT NULL COMMENT 'ID của vé số đã sử dụng',
    won_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES lottery_tickets(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_won_at (won_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. BẢNG SOLAR_SURVEYS (Khảo sát điện mặt trời)
-- =====================================================
CREATE TABLE IF NOT EXISTS solar_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    region VARCHAR(50) NOT NULL COMMENT 'mien-bac, mien-trung, mien-nam',
    phase TINYINT NOT NULL COMMENT '1 or 3',
    solar_panel_type INT NOT NULL COMMENT '590 or 630',
    monthly_bill DECIMAL(15, 2) NOT NULL,
    usage_time VARCHAR(50) NOT NULL COMMENT 'day, balanced, night',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- 11. BẢNG SURVEY_RESULTS (Kết quả khảo sát)
-- =====================================================
CREATE TABLE IF NOT EXISTS survey_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    
    -- Thông tin điện năng
    monthly_kwh DECIMAL(10, 2) NOT NULL COMMENT 'Điện tiêu thụ hàng tháng (kWh)',
    sun_hours DECIMAL(3, 1) NOT NULL COMMENT 'Giờ nắng trung bình/ngày',
    region_name VARCHAR(100) NOT NULL COMMENT 'Tên khu vực (Miền Bắc/Trung/Nam)',
    
    -- Thông tin tấm pin
    panel_id INT NOT NULL COMMENT 'ID loại tấm pin (590 hoặc 630)',
    panel_name VARCHAR(255) NOT NULL COMMENT 'Tên tấm pin',
    panel_power DECIMAL(5, 3) NOT NULL COMMENT 'Công suất tấm pin (kW)',
    panel_price DECIMAL(15, 2) NOT NULL COMMENT 'Đơn giá 1 tấm pin',
    panels_needed INT NOT NULL COMMENT 'Số tấm pin cần thiết',
    panel_cost DECIMAL(15, 2) NOT NULL COMMENT 'Tổng tiền tấm pin',
    energy_per_panel_per_day DECIMAL(10, 3) NOT NULL COMMENT 'Năng lượng/tấm/ngày (kWh)',
    total_capacity DECIMAL(10, 2) NOT NULL COMMENT 'Tổng công suất hệ thống (kW)',
    
    -- Thông tin biến tần
    inverter_id INT NOT NULL COMMENT 'ID biến tần được chọn',
    inverter_name VARCHAR(255) NOT NULL,
    inverter_capacity DECIMAL(10, 2) NOT NULL COMMENT 'Công suất biến tần (kW)',
    inverter_price DECIMAL(15, 2) NOT NULL,
    
    -- Thông tin tủ điện
    cabinet_id INT NOT NULL COMMENT 'ID tủ điện được chọn',
    cabinet_name VARCHAR(255) NOT NULL,
    cabinet_capacity DECIMAL(10, 2) NOT NULL COMMENT 'Công suất tủ điện (kW)',
    cabinet_price DECIMAL(15, 2) NOT NULL,
    
    -- Thông tin pin lưu trữ
    battery_needed DECIMAL(10, 2) NOT NULL COMMENT 'Dung lượng pin cần (kWh)',
    battery_type VARCHAR(50) NOT NULL COMMENT '8cell or 16cell',
    battery_id INT NOT NULL COMMENT 'ID loại pin',
    battery_name VARCHAR(255) NOT NULL COMMENT 'Tên pin lưu trữ',
    battery_capacity DECIMAL(10, 2) NOT NULL COMMENT 'Dung lượng/cell (kWh)',
    battery_quantity INT NOT NULL COMMENT 'Số lượng cell',
    battery_unit_price DECIMAL(15, 2) NOT NULL COMMENT 'Đơn giá/cell',
    battery_cost DECIMAL(15, 2) NOT NULL COMMENT 'Tổng tiền pin',
    
    -- Phụ kiện chi tiết
    bach_z_qty INT NOT NULL COMMENT 'Số lượng Bach Z',
    bach_z_price DECIMAL(10, 2) NOT NULL COMMENT 'Đơn giá Bach Z',
    bach_z_cost DECIMAL(15, 2) NOT NULL COMMENT 'Thành tiền Bach Z',
    
    clip_qty INT NOT NULL COMMENT 'Số lượng kẹp biên',
    clip_price DECIMAL(10, 2) NOT NULL COMMENT 'Đơn giá kẹp',
    clip_cost DECIMAL(15, 2) NOT NULL COMMENT 'Thành tiền kẹp',
    
    jack_mc4_qty INT NOT NULL COMMENT 'Số lượng Jack MC4',
    jack_mc4_price DECIMAL(10, 2) NOT NULL COMMENT 'Đơn giá Jack MC4',
    jack_mc4_cost DECIMAL(15, 2) NOT NULL COMMENT 'Thành tiền Jack MC4',
    
    dc_cable_length INT NOT NULL COMMENT 'Chiều dài dây DC (m)',
    dc_cable_price DECIMAL(10, 2) NOT NULL COMMENT 'Đơn giá dây DC/m',
    dc_cable_cost DECIMAL(15, 2) NOT NULL COMMENT 'Thành tiền dây DC',
    
    accessories_cost DECIMAL(15, 2) NOT NULL COMMENT 'Tổng phụ kiện (Bach Z + Clip + Jack + DC)',
    labor_cost DECIMAL(15, 2) NOT NULL COMMENT 'Công thợ lắp đặt',
    
    -- Tổng kết
    total_cost_without_battery DECIMAL(15, 2) NOT NULL COMMENT 'Tổng không tính pin',
    total_cost DECIMAL(15, 2) NOT NULL COMMENT 'Tổng chi phí dự án',
    
    -- Phân tích hóa đơn điện (JSON)
    bill_breakdown JSON COMMENT 'Chi tiết bậc thang điện EVN',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (survey_id) REFERENCES solar_surveys(id) ON DELETE CASCADE
);

-- =====================================================
-- INDEXES CHO PERFORMANCE
-- =====================================================
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_products_category ON products(category);
CREATE INDEX idx_products_brand ON products(brand);
CREATE INDEX idx_products_available ON products(is_available);
CREATE INDEX idx_surveys_user_id ON solar_surveys(user_id);
CREATE INDEX idx_surveys_created_at ON solar_surveys(created_at);
CREATE INDEX idx_survey_results_survey_id ON survey_results(survey_id);

-- =====================================================
-- DỮ LIỆU MẪU - PRODUCTS
-- =====================================================
INSERT INTO products (id, name, category, brand, model, price, image_url, specifications) VALUES
-- Tấm Pin Mặt Trời
(1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 'Solar Panel', 'Jinko Solar', 'Tiger Neo 590W', 2300000, 'assets/img/products/tam-pin-jinko-solar-590w-tiger-neo.jpg', 'Công suất: 590W, Công nghệ: N-Type Tiger Neo'),
(2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 'Solar Panel', 'Jinko Solar', 'Tiger Neo 630W', 2600000, 'assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png', 'Công suất: 630W, Công nghệ: N-Type Tiger Neo'),

-- Inverter LuxPower Hybrid
(3, 'ECO Hybrid 5kW (Bản mới 2025)', 'Inverter', 'LuxPower', 'SNA5000WPV', 16500000, 'assets/img/products/eco-hybrid-5kw-sna5000wpv.png', 'Model: SNA5000WPV, On-grid/Back-up: 5kW'),
(4, 'ECO Hybrid 6kW', 'Inverter', 'LuxPower', 'SNA6000WPV', 17500000, 'assets/img/products/eco-hybrid-6kw-sna6000wpv.png', 'Model: SNA6000WPV, On-grid/Back-up: 6kW'),
(5, 'ECO Hybrid 12kW', 'Inverter', 'LuxPower', 'SNA 12K', 35500000, 'assets/img/products/eco-hybrid-12kw-sna12k.png', 'Model: SNA 12K, On-grid/Back-up: 12kW'),
(6, 'ECO Hybrid 14kW', 'Inverter', 'LuxPower', 'SNA EU 14K', 39000000, 'assets/img/products/eco-hybrid-14kw-sna-eu-14k.png', 'Model: SNA EU 14K, On-grid/Back-up: 14kW'),

-- Inverter LuxPower 1 Pha
(7, 'Hybrid GEN-LB-EU 6K', 'Inverter', 'LuxPower', 'GEN-LB-EU 6K', 28900000, 'assets/img/products/hybrid-gen-lb-eu-6k.png', 'On-grid/Backup: 6kW, Sạc/xả: 6000W 125A/140A'),
(8, 'Hybrid GEN-LB-EU 8K', 'Inverter', 'LuxPower', 'GEN-LB-EU 8K', 48000000, 'assets/img/products/hybrid-gen-lb-eu-8k.png', 'On-grid/Backup: 8kW, Sạc/xả: 8000W 167A/167A'),
(9, 'Hybrid GEN-LB-EU 10K', 'Inverter', 'LuxPower', 'GEN-LB-EU 10K', 54000000, 'assets/img/products/hybrid-gen-lb-eu-10k.png', 'On-grid/Backup: 10kW, Sạc/xả: 10000W 210A/210A'),
(10, 'Hybrid LXP-12K 12kW', 'Inverter', 'LuxPower', 'LXP-12K', 59000000, 'assets/img/products/hybrid-lxp-12k.png', 'Hòa lưới On-grid: 12kW, Chạy độc lập Back-up: 12kW'),

-- Inverter LuxPower 3 Pha
(11, 'Hybrid TriP2-LB-3P 12K 12kW', 'Inverter', 'LuxPower', 'TriP2-LB-3P 12K', 59000000, 'assets/img/products/hybrid-trip2-lb-3p-12k.png', 'On-grid/Backup: 12kW, 3 MPPT'),
(12, 'Hybrid TriP2-LB-3P 15K 15kW', 'Inverter', 'LuxPower', 'TriP2-LB-3P 15K', 63000000, 'assets/img/products/hybrid-trip2-lb-3p-15k.png', 'On-grid/Backup: 15kW, 3 MPPT'),

-- Inverter LuxPower 3 Pha Áp Cao
(13, 'LUXPOWER Hybrid TRIP 10K', 'Inverter', 'LuxPower', 'TRIP 10K', 69000000, 'assets/img/products/hybrid-trip-10k.png', 'Công suất: 10KW, Điện áp: 3 pha áp cao'),
(14, 'LUXPOWER Hybrid TRIP 15K', 'Inverter', 'LuxPower', 'TRIP 15K', 89000000, 'assets/img/products/hybrid-trip-15k.png', 'Công suất: 15KW, Điện áp: 3 pha áp cao'),
(15, 'LUXPOWER Hybrid TRIP 20K', 'Inverter', 'LuxPower', 'TRIP 20K', 109000000, 'assets/img/products/hybrid-trip-20k.png', 'Công suất: 20KW, Điện áp: 3 pha áp cao'),
(16, 'LUXPOWER Hybrid TRIP 25K', 'Inverter', 'LuxPower', 'TRIP 25K', 69000000, 'assets/img/products/luxpower-trip-25k.png', 'Công suất: 25kW, Hỗ trợ pin: Acquy/Lithium 100-700V, 3 MPPT, On-grid/Backup: 50kW'),

-- Inverter Growatt
(17, 'Biến tần Growatt 110kW MAX', 'Inverter', 'Growatt', 'MAX 110KTL3-LV', 120000000, 'assets/img/products/growatt-110kw-max-real.png', 'Công suất: 110 kW, Hiệu suất: 98.8%'),

-- Pin Lưu Trữ
(18, 'Cell A-Cornex LiFePO4 16 Cell', 'Battery', 'A-Cornex', 'LiFePO4 16C', 50000000, 'assets/img/products/cell-a-cornex-lifepo4-16cell.png', 'Cấu hình: 16 Cell, Điện áp hệ thống: 52V'),
(19, 'Cell BYD 173ah LiFePO4', 'Battery', 'BYD', 'LiFePO4 173Ah', 15500000, 'assets/img/products/cell-byd-173ah-lifepo4.png', 'Điện áp: 51.2V, Dung lượng: 173ah (8.8kW)'),

-- Tủ Điện
(20, 'Tủ điện Hybrid 1 pha 6kW', 'Electrical Cabinet', 'HC Eco', '1P-6KW', 1850000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 6 kW, 1 pha'),
(21, 'Tủ điện Hybrid 1 pha 12kW', 'Electrical Cabinet', 'HC Eco', '1P-12KW', 2850000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 12 kW, 1 pha'),
(22, 'Tủ điện Hybrid 1 pha 15kW', 'Electrical Cabinet', 'HC Eco', '1P-15KW', 3800000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 15 kW, 1 pha'),
(23, 'Tủ điện Hybrid 3 pha 15kW', 'Electrical Cabinet', 'HC Eco', '3P-15KW', 4850000, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 15 kW, 3 pha'),

-- Phụ Kiện
(24, 'Dongles Lan LuxPower', 'Accessories', 'LuxPower', 'LAN Dongle', 1200000, 'assets/img/products/dongles-lan-luxpower.png', 'Kết nối internet qua dây mạng LAN'),
(25, 'Wifi LuxPower', 'Accessories', 'LuxPower', 'WiFi Module', 1000000, 'assets/img/products/wifi-luxpower.png', 'Kết nối internet qua WiFi 2.4GHz'),
(26, 'CT ngoài LuxPower SNA 6kW', 'Accessories', 'LuxPower', 'SNA 6kW CT', 800000, 'assets/img/products/ct-ngoai-luxpower-sna-6kw.png', 'Dòng điện: 100A/100mA'),
(27, 'Bách Z', 'Accessories', 'HC Eco', 'Bách Z', 22000, 'assets/img/products/bachz.png', 'Chức năng: Mạ kẽm nhũng nóng áp mái tôn, Ứng dụng: Cố định khung giá đỡ trên mái tôn, Vật liệu: Thép mạ kẽm nhúng nóng'),
(28, 'Kẹp biên, Kẹp giữa tấm Pin', 'Accessories', 'HC Eco', 'Kẹp Pin', 11500, 'assets/img/products/kepbien-tamgiua.png', 'Cố định tấm pin vào khung giá đỡ, hợp kim nhôm'),
(29, 'Jack Cắm MC4 1500VDC', 'Accessories', 'MC4', 'MC4 1500VDC', 14000, 'assets/img/products/jackcam.png', 'Kết nối dây cáp DC, 30A-40A, IP67'),
(30, 'Dây điện đấu nối tấm PIN', 'Accessories', 'HC Eco', 'Dây DC', 20000, 'assets/img/products/daydien.png', 'Dây DC chuyên dụng 4-6mm², 1000-1500V DC');

-- =====================================================
-- DỮ LIỆU MẪU - TỈNH/THÀNH PHỐ
-- =====================================================
INSERT INTO tinh (id, ten_tinh) VALUES
(1, 'THÀNH PHỐ HÀ NỘI'),
(2, 'TỈNH BẮC NINH'),
(3, 'TỈNH QUẢNG NINH'),
(4, 'THÀNH PHỐ HẢI PHÒNG'),
(5, 'TỈNH HƯNG YÊN'),
(6, 'TỈNH NINH BÌNH'),
(7, 'TỈNH CAO BẰNG'),
(8, 'TỈNH LÀO CAI'),
(9, 'TỈNH ĐIỆN BIÊN'),
(10, 'TỈNH LAI CHÂU'),
(11, 'TỈNH SƠN LA'),
(12, 'TỈNH YÊN BÁI'),
(13, 'TỈNH THÁI NGUYÊN'),
(14, 'TỈNH LẠNG SƠN'),
(15, 'TỈNH QUẢNG NAM'),
(16, 'TỈNH QUẢNG NGÃI'),
(17, 'TỈNH BÌNH ĐỊNH'),
(18, 'TỈNH PHÚ YÊN'),
(19, 'TỈNH KHÁNH HÒA'),
(20, 'TỈNH NINH THUẬN'),
(21, 'TỈNH BÌNH THUẬN'),
(22, 'TỈNH KONTUM'),
(23, 'TỈNH GIA LAI'),
(24, 'TỈNH ĐẮK LẮK'),
(25, 'TỈNH ĐẮK NÔNG'),
(26, 'TỈNH LÂM ĐỒNG'),
(27, 'TỈNH BÌNH PHƯỚC'),
(28, 'TỈNH TÂY NINH'),
(29, 'TỈNH BÌNH DƯƠNG'),
(30, 'TỈNH ĐỒNG NAI'),
(31, 'TỈNH BÀ RỊA - VŨNG TÀU'),
(32, 'THÀNH PHỐ HỒ CHÍ MINH'),
(33, 'TỈNH LONG AN'),
(34, 'TỈNH TIỀN GIANG'),
(35, 'TỈNH BẾN TRE'),
(36, 'TỈNH TRÀ VINH'),
(37, 'TỈNH VĨNH LONG'),
(38, 'TỈNH ĐỒNG THÁP'),
(39, 'TỈNH AN GIANG'),
(40, 'TỈNH KIÊN GIANG'),
(41, 'TỈNH CÀ MAU'),
(42, 'TỈNH BẠC LIÊU'),
(43, 'TỈNH SÓC TRĂNG'),
(44, 'TỈNH HẬU GIANG'),
(45, 'THÀNH PHỐ ĐÀ NẴNG'),
(46, 'TỈNH THỪA THIÊN HUẾ'),
(47, 'TỈNH QUẢNG TRỊ'),
(48, 'TỈNH QUẢNG BÌNH'),
(49, 'TỈNH HÀ TĨNH'),
(50, 'TỈNH NGHỆ AN'),
(51, 'TỈNH THANH HÓA'),
(52, 'TỈNH NAM ĐỊNH'),
(53, 'TỈNH THÁI BÌNH'),
(54, 'TỈNH HẢI DƯƠNG'),
(55, 'TỈNH HÀ NAM'),
(56, 'TỈNH VĨNH PHÚC'),
(57, 'TỈNH BẮC GIANG'),
(58, 'TỈNH BẮC KẠN'),
(59, 'TỈNH TUYÊN QUANG'),
(60, 'TỈNH PHÚ THỌ'),
(61, 'TỈNH HÒA BÌNH');

-- =====================================================
-- DỮ LIỆU MẪU - PHƯỜNG/XÃ (Một số phường chính)
-- =====================================================
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
('Phường Đồng Xuân', 1),
('Phường Hàng Bồ', 1),
('Phường Hàng Bông', 1),
('Phường Hàng Gai', 1),
('Phường Lý Thường Kiệt', 1),
('Phường Phan Chu Trinh', 1),
('Phường Phúc Tân', 1),
('Phường Trần Hưng Đạo', 1),
('Phường Tràng Thi', 1),

-- Đà Nẵng (id=45)
('Phường An Hải Bắc', 45),
('Phường An Hải Đông', 45),
('Phường An Hải Tây', 45),
('Phường An Hải Nam', 45),
('Phường An Hải Trung', 45),
('Phường Mân Thái', 45),
('Phường Nại Hiên Đông', 45),
('Phường Phước Mỹ', 45),
('Phường Thọ Quang', 45),
('Phường An Khê', 45),
('Phường Hải Châu I', 45),
('Phường Hải Châu II', 45),
('Phường Phước Ninh', 45),
('Phường Hòa Thuận Tây', 45),
('Phường Hòa Thuận Đông', 45),
('Phường Nam Dương', 45),
('Phường Bình Hiên', 45),
('Phường Bình Thuận', 45),
('Phường Hòa Cường Bắc', 45),
('Phường Hòa Cường Nam', 45),
('Phường Thạch Thang', 45),
('Phường Hải Châu', 45),
('Phường Thanh Bình', 45),
('Phường Thuận Phước', 45),
('Phường Hòa Minh', 45),
('Phường Hòa Quý', 45),
('Phường Hòa Thọ Đông', 45),
('Phường Hòa Thọ Tây', 45),
('Phường Hòa Phát', 45),
('Phường Hòa An', 45),
('Phường Hòa Phước', 45),
('Phường Hòa Thọ', 45),
('Phường Hòa Xuân', 45),
('Phường Hòa Khánh Bắc', 45),
('Phường Hòa Khánh Nam', 45),
('Phường Hòa Khánh', 45),

-- TP.HCM (id=32)
('Phường Bến Nghé', 32),
('Phường Bến Thành', 32),
('Phường Cầu Kho', 32),
('Phường Cầu Ông Lãnh', 32),
('Phường Cô Giang', 32),
('Phường Đa Kao', 32),
('Phường Nguyễn Cư Trinh', 32),
('Phường Nguyễn Thái Bình', 32),
('Phường Phạm Ngũ Lão', 32),
('Phường Tân Định', 32);

-- =====================================================
-- DỮ LIỆU TEST - USER MẪU (Để test lottery)
-- =====================================================
-- Tạo user test (password: 123456 - đã hash)
INSERT INTO users (id, full_name, username, phone, password) VALUES
(1, 'Test User', 'testuser', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Tạo lottery tickets test cho user
INSERT INTO lottery_tickets (user_id, ticket_type, status, created_at) VALUES
(1, 'bonus', 'active', NOW()),
(1, 'bonus', 'active', NOW()),
(1, 'bonus', 'active', NOW());

-- =====================================================
-- COMMENTS CHO CÁC BẢNG
-- =====================================================
ALTER TABLE solar_surveys COMMENT = 'Lưu thông tin khảo sát nhu cầu lắp đặt điện mặt trời';
ALTER TABLE survey_results COMMENT = 'Lưu kết quả tính toán chi tiết từ khảo sát';
ALTER TABLE lottery_rewards COMMENT = 'Lưu phần thưởng từ vòng quay may mắn';
ALTER TABLE lottery_tickets COMMENT = 'Lưu vé quay may mắn của người dùng';

-- =====================================================
-- HOÀN THÀNH SETUP
-- =====================================================
SELECT 'Database setup completed successfully!' as message;
SELECT 'Total tables created: 11' as info;
SELECT 'Sample data inserted for testing' as info;
SELECT 'Ready for HC Eco System!' as status;
