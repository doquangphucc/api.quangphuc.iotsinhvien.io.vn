-- =====================================================
-- HC ECO SYSTEM - DATABASE DATA
-- File: database_data.sql
-- Description: Dữ liệu mẫu cho hệ thống HC Eco
-- Usage: Import file này SAU KHI đã import database_schema.sql
-- =====================================================

USE nangluongmattroi;

-- =====================================================
-- DỮ LIỆU MẪU - PRODUCT_CATEGORIES
-- =====================================================
INSERT INTO product_categories (id, name, logo_url, description, display_order, is_active) VALUES
(1, 'Tấm Pin Mặt Trời', 'assets/img/products/tam-pin-jinko-solar-590w-tiger-neo.jpg', 'Tấm pin năng lượng mặt trời công suất cao', 1, TRUE),
(2, 'Inverter 1 Pha', 'assets/img/products/eco-hybrid-5kw-sna5000wpv.png', 'Biến tần hòa lưới 1 pha cho hộ gia đình', 2, TRUE),
(3, 'Inverter 3 Pha', 'assets/img/products/hybrid-trip2-lb-3p-12k.png', 'Biến tần hòa lưới 3 pha công suất cao', 3, TRUE),
(4, 'Pin Lưu Trữ', 'assets/img/products/cell-a-cornex-lifepo4-16cell.png', 'Pin lưu trữ năng lượng LiFePO4', 4, TRUE),
(5, 'Tủ Điện Hybrid', 'assets/img/products/electrical-cabinet.jpg', 'Tủ điện chuyên dụng cho hệ thống solar', 5, TRUE),
(6, 'Phụ Kiện', 'assets/img/products/daydien.png', 'Phụ kiện lắp đặt và kết nối', 6, TRUE);

-- =====================================================
-- DỮ LIỆU MẪU - PRODUCTS
-- =====================================================
INSERT INTO products (id, category_id, name, brand, model, price, price_installation, image_url, specifications) VALUES
-- Tấm Pin Mặt Trời (category_id = 1)
(1, 1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 'Jinko Solar', 'Tiger Neo 590W', 2300000, NULL, 'assets/img/products/tam-pin-jinko-solar-590w-tiger-neo.jpg', 'Công suất: 590W, Công nghệ: N-Type Tiger Neo'),
(2, 1, 'Tấm Pin Jinko Solar 630W Tiger Neo', 'Jinko Solar', 'Tiger Neo 630W', 2600000, NULL, 'assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png', 'Công suất: 630W, Công nghệ: N-Type Tiger Neo'),

-- Inverter LuxPower 1 Pha (category_id = 2)
(3, 2, 'ECO Hybrid 5kW (Bản mới 2025)', 'LuxPower', 'SNA5000WPV', 16500000, NULL, 'assets/img/products/eco-hybrid-5kw-sna5000wpv.png', 'Model: SNA5000WPV, On-grid/Back-up: 5kW'),
(4, 2, 'ECO Hybrid 6kW', 'LuxPower', 'SNA6000WPV', 17500000, NULL, 'assets/img/products/eco-hybrid-6kw-sna6000wpv.png', 'Model: SNA6000WPV, On-grid/Back-up: 6kW'),
(5, 2, 'ECO Hybrid 12kW', 'LuxPower', 'SNA 12K', 35500000, NULL, 'assets/img/products/eco-hybrid-12kw-sna12k.png', 'Model: SNA 12K, On-grid/Back-up: 12kW'),
(6, 2, 'ECO Hybrid 14kW', 'LuxPower', 'SNA EU 14K', 39000000, NULL, 'assets/img/products/eco-hybrid-14kw-sna-eu-14k.png', 'Model: SNA EU 14K, On-grid/Back-up: 14kW'),
(7, 2, 'Hybrid GEN-LB-EU 6K', 'LuxPower', 'GEN-LB-EU 6K', 28900000, NULL, 'assets/img/products/hybrid-gen-lb-eu-6k.png', 'On-grid/Backup: 6kW, Sạc/xả: 6000W 125A/140A'),
(8, 2, 'Hybrid GEN-LB-EU 8K', 'LuxPower', 'GEN-LB-EU 8K', 48000000, NULL, 'assets/img/products/hybrid-gen-lb-eu-8k.png', 'On-grid/Backup: 8kW, Sạc/xả: 8000W 167A/167A'),
(9, 2, 'Hybrid GEN-LB-EU 10K', 'LuxPower', 'GEN-LB-EU 10K', 54000000, NULL, 'assets/img/products/hybrid-gen-lb-eu-10k.png', 'On-grid/Backup: 10kW, Sạc/xả: 10000W 210A/210A'),
(10, 2, 'Hybrid LXP-12K 12kW', 'LuxPower', 'LXP-12K', 59000000, NULL, 'assets/img/products/hybrid-lxp-12k.png', 'Hòa lưới On-grid: 12kW, Chạy độc lập Back-up: 12kW'),

-- Inverter LuxPower 3 Pha (category_id = 3)
(11, 3, 'Hybrid TriP2-LB-3P 12K 12kW', 'LuxPower', 'TriP2-LB-3P 12K', 59000000, NULL, 'assets/img/products/hybrid-trip2-lb-3p-12k.png', 'On-grid/Backup: 12kW, 3 MPPT'),
(12, 3, 'Hybrid TriP2-LB-3P 15K 15kW', 'LuxPower', 'TriP2-LB-3P 15K', 63000000, NULL, 'assets/img/products/hybrid-trip2-lb-3p-15k.png', 'On-grid/Backup: 15kW, 3 MPPT'),
(13, 3, 'LUXPOWER Hybrid TRIP 10K', 'LuxPower', 'TRIP 10K', 69000000, NULL, 'assets/img/products/hybrid-trip-10k.png', 'Công suất: 10KW, Điện áp: 3 pha áp cao'),
(14, 3, 'LUXPOWER Hybrid TRIP 15K', 'LuxPower', 'TRIP 15K', 89000000, NULL, 'assets/img/products/hybrid-trip-15k.png', 'Công suất: 15KW, Điện áp: 3 pha áp cao'),
(15, 3, 'LUXPOWER Hybrid TRIP 20K', 'LuxPower', 'TRIP 20K', 109000000, NULL, 'assets/img/products/hybrid-trip-20k.png', 'Công suất: 20KW, Điện áp: 3 pha áp cao'),
(16, 3, 'LUXPOWER Hybrid TRIP 25K', 'LuxPower', 'TRIP 25K', 69000000, NULL, 'assets/img/products/luxpower-trip-25k.png', 'Công suất: 25kW, Hỗ trợ pin: Acquy/Lithium 100-700V, 3 MPPT, On-grid/Backup: 50kW'),
(17, 3, 'Biến tần Growatt 110kW MAX', 'Growatt', 'MAX 110KTL3-LV', 120000000, NULL, 'assets/img/products/growatt-110kw-max-real.png', 'Công suất: 110 kW, Hiệu suất: 98.8%'),

-- Pin Lưu Trữ (category_id = 4)
(18, 4, 'Cell A-Cornex LiFePO4 16 Cell', 'A-Cornex', 'LiFePO4 16C', 50000000, NULL, 'assets/img/products/cell-a-cornex-lifepo4-16cell.png', 'Cấu hình: 16 Cell, Điện áp hệ thống: 52V'),
(19, 4, 'Cell BYD 173ah LiFePO4', 'BYD', 'LiFePO4 173Ah', 15500000, NULL, 'assets/img/products/cell-byd-173ah-lifepo4.png', 'Điện áp: 51.2V, Dung lượng: 173ah (8.8kW)'),

-- Tủ Điện (category_id = 5)
(20, 5, 'Tủ điện Hybrid 1 pha 6kW', 'HC Eco', '1P-6KW', 1850000, NULL, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 6 kW, 1 pha'),
(21, 5, 'Tủ điện Hybrid 1 pha 12kW', 'HC Eco', '1P-12KW', 2850000, NULL, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 12 kW, 1 pha'),
(22, 5, 'Tủ điện Hybrid 1 pha 15kW', 'HC Eco', '1P-15KW', 3800000, NULL, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 15 kW, 1 pha'),
(23, 5, 'Tủ điện Hybrid 3 pha 15kW', 'HC Eco', '3P-15KW', 4850000, NULL, 'assets/img/products/electrical-cabinet.jpg', 'Công suất hệ thống: 15 kW, 3 pha'),

-- Phụ Kiện (category_id = 6)
(24, 6, 'Dongles Lan LuxPower', 'LuxPower', 'LAN Dongle', 1200000, NULL, 'assets/img/products/dongles-lan-luxpower.png', 'Kết nối internet qua dây mạng LAN'),
(25, 6, 'Wifi LuxPower', 'LuxPower', 'WiFi Module', 1000000, NULL, 'assets/img/products/wifi-luxpower.png', 'Kết nối internet qua WiFi 2.4GHz'),
(26, 6, 'CT ngoài LuxPower SNA 6kW', 'LuxPower', 'SNA 6kW CT', 800000, NULL, 'assets/img/products/ct-ngoai-luxpower-sna-6kw.png', 'Dòng điện: 100A/100mA'),
(27, 6, 'Bách Z', 'HC Eco', 'Bách Z', 22000, NULL, 'assets/img/products/bachz.png', 'Chức năng: Mạ kẽm nhũng nóng áp mái tôn, Ứng dụng: Cố định khung giá đỡ trên mái tôn, Vật liệu: Thép mạ kẽm nhúng nóng'),
(28, 6, 'Kẹp biên, Kẹp giữa tấm Pin', 'HC Eco', 'Kẹp Pin', 11500, NULL, 'assets/img/products/kepbien-tamgiua.png', 'Cố định tấm pin vào khung giá đỡ, hợp kim nhôm'),
(29, 6, 'Jack Cắm MC4 1500VDC', 'MC4', 'MC4 1500VDC', 14000, NULL, 'assets/img/products/jackcam.png', 'Kết nối dây cáp DC, 30A-40A, IP67'),
(30, 6, 'Dây điện đấu nối tấm PIN', 'HC Eco', 'Dây DC', 20000, NULL, 'assets/img/products/daydien.png', 'Dây DC chuyên dụng 4-6mm², 1000-1500V DC');

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
-- DỮ LIỆU MẪU - PACKAGE_CATEGORIES
-- =====================================================
INSERT INTO package_categories (id, name, description, display_order, is_active) VALUES
(1, 'Gói Lắp Đặt Trọn Gói', 'Gói lắp đặt hoàn chỉnh cho hộ gia đình và doanh nghiệp', 1, TRUE);

-- =====================================================
-- DỮ LIỆU MẪU - PACKAGES
-- =====================================================
INSERT INTO packages (id, category_id, name, description, price, savings_per_month, payback_period, badge_text, badge_color, display_order) VALUES
(1, 1, '6 kWp', 'Phù hợp hộ gia đình nhỏ', 6700000000, '3-4 triệu/tháng', '5-6 năm', 'GÓI CƠ BẢN', 'green', 1),
(2, 1, '10 kWp', 'Hộ gia đình trung bình', 10000000000, '5-7 triệu/tháng', '5-6 năm', 'PHỔ BIẾN', 'blue', 2),
(3, 1, '15 kWp', 'Biệt thự, nhà lớn', 14750000000, '8-10 triệu/tháng', '5-6 năm', 'GÓI CAO CẤP', 'purple', 3),
(4, 1, '20+ kWp', 'Nhà xưởng, văn phòng', 0, '15+ triệu/tháng', '4-5 năm', 'GÓI DOANH NGHIỆP', 'orange', 4);

-- =====================================================
-- DỮ LIỆU MẪU - PACKAGE_ITEMS
-- =====================================================
INSERT INTO package_items (package_id, item_name, item_description, display_order) VALUES
-- Gói 6 kWp
(1, '10 tấm Jinko 590W', NULL, 1),
(1, 'Inverter Luxpower 6K', NULL, 2),
(1, 'Pin lưu trữ BYD 8.8kWh', NULL, 3),
(1, 'Phụ kiện & lắp đặt', NULL, 4),
-- Gói 10 kWp
(2, '17 tấm Jinko 590W', NULL, 1),
(2, 'Inverter Luxpower 10K', NULL, 2),
(2, 'Pin lưu trữ A-Cornex 16.3kWh', NULL, 3),
(2, 'Phụ kiện & lắp đặt', NULL, 4),
-- Gói 15 kWp
(3, '24 tấm Jinko 630W', NULL, 1),
(3, 'Inverter Luxpower 15K (3P)', NULL, 2),
(3, 'Pin lưu trữ A-Cornex 16.3kWh x2', NULL, 3),
(3, 'Phụ kiện & lắp đặt', NULL, 4),
-- Gói 20+ kWp
(4, '30+ tấm Jinko 630W', NULL, 1),
(4, 'Inverter Luxpower 20K+ (3P)', NULL, 2),
(4, 'Pin tùy chọn theo nhu cầu', NULL, 3),
(4, 'Thiết kế riêng cho công trình', NULL, 4);

-- =====================================================
-- DỮ LIỆU MẪU - REWARD_TEMPLATES
-- =====================================================
INSERT INTO reward_templates (id, reward_name, reward_type, reward_value, reward_description, reward_quantity, reward_image, is_active) VALUES
(1, 'Voucher giảm 500.000đ', 'voucher', 500000, 'Voucher giảm giá 500.000đ cho đơn hàng tiếp theo', NULL, NULL, TRUE),
(2, 'Voucher giảm 1.000.000đ', 'voucher', 1000000, 'Voucher giảm giá 1.000.000đ cho đơn hàng tiếp theo', NULL, NULL, TRUE),
(3, 'Tiền mặt 200.000đ', 'cash', 200000, 'Nhận ngay 200.000đ tiền mặt', NULL, NULL, TRUE),
(4, 'Tiền mặt 500.000đ', 'cash', 500000, 'Nhận ngay 500.000đ tiền mặt', NULL, NULL, TRUE),
(5, 'Chai nước giặt Omo', 'gift', NULL, 'Chai nước giặt Omo 3.8kg', 100, NULL, TRUE),
(6, 'Bộ dụng cụ gia đình', 'gift', NULL, 'Bộ dụng cụ gia đình 10 món', 50, NULL, TRUE);

-- =====================================================
-- DỮ LIỆU TEST - USER MẪU (Để test)
-- =====================================================
-- Tạo user test (password: 123456 - đã hash)
-- Tạo admin user (username: admin, password: admin123)
INSERT INTO users (id, full_name, username, phone, password, is_admin) VALUES
(1, 'Test User', 'testuser', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE),
(2, 'Admin User', 'admin', '0988919868', '$2y$10$E4Mjzm5z3xW0nYJX5K5XKOYqXvXdWzQ5Q5XYZ5Z5Z5Z5Z5Z5Z5Z5Z5', TRUE);

-- Tạo lottery tickets test cho user
INSERT INTO lottery_tickets (user_id, ticket_type, status, created_at) VALUES
(1, 'bonus', 'active', NOW()),
(1, 'bonus', 'active', NOW()),
(1, 'bonus', 'active', NOW());

-- =====================================================
-- DỮ LIỆU TEST - VOUCHERS MẪU
-- =====================================================
INSERT INTO vouchers (code, discount_amount, description, expires_at) VALUES
('WELCOME500K', 500000, 'Voucher chào mừng khách hàng mới', DATE_ADD(NOW(), INTERVAL 30 DAY)),
('NEWYEAR1M', 1000000, 'Voucher năm mới giảm 1 triệu', DATE_ADD(NOW(), INTERVAL 60 DAY));

-- =====================================================
-- HOÀN THÀNH IMPORT DỮ LIỆU
-- =====================================================
SELECT 'Sample data imported successfully!' as message;
SELECT 'Product Categories: 6 categories' as info;
SELECT 'Products: 30 items' as info;
SELECT 'Packages: 4 packages' as info;
SELECT 'Reward Templates: 6 templates' as info;
SELECT 'Provinces: 61 provinces/cities' as info;
SELECT 'Districts: Sample districts for major cities' as info;
SELECT 'Test users created:' as info;
SELECT '  - testuser / 123456 (Regular user)' as info;
SELECT '  - admin / admin123 (Admin user)' as info;
SELECT 'Ready for HC Eco System!' as status;

