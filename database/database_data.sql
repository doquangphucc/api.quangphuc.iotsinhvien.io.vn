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
INSERT INTO product_categories (id, name, logo_url, display_order, is_active) VALUES
(1, 'Tấm Pin Mặt Trời', 'assets/img/categories/solar-panel.png', 1, TRUE),
(2, 'Inverter Hybrid', 'assets/img/categories/inverter.png', 2, TRUE),
(3, 'Pin Lưu Trữ', 'assets/img/categories/battery.png', 3, TRUE),
(4, 'Tủ Điện', 'assets/img/categories/cabinet.png', 4, TRUE),
(5, 'Phụ Kiện', 'assets/img/categories/accessories.png', 5, TRUE);

-- =====================================================
-- DỮ LIỆU MẪU - PRODUCTS (30 sản phẩm)
-- =====================================================
-- Dữ liệu dựa trên file DANH_SACH_30_SAN_PHAM.txt

-- PHẦN 1: TẤM PIN MẶT TRỜI (2 sản phẩm)
INSERT INTO products (category_id, title, market_price, category_price, technical_description, image_url, is_active) VALUES
(1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 2300000, NULL, 
'Thương hiệu: Jinko Solar
Model: Tiger Neo 590W

Thông số kỹ thuật:
- Công suất: 590W
- Công nghệ: N-Type Tiger Neo
- Hiệu suất: 22.3%
- Kích thước: 2278×1134×30mm
- Bảo hành: 15 năm sản phẩm, 30 năm công suất', 
'assets/img/products/tam-pin-jinko-solar-590w-tiger-neo.jpg', TRUE),

(1, 'Tấm Pin Jinko Solar 630W Tiger Neo', 2600000, NULL,
'Thương hiệu: Jinko Solar
Model: Tiger Neo 630W

Thông số kỹ thuật:
- Công suất: 630W
- Công nghệ: N-Type Tiger Neo
- Hiệu suất: 22.5%
- Bảo hành: 15 năm sản phẩm, 30 năm công suất',
'assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png', TRUE),

-- PHẦN 2: INVERTER LUXPOWER HYBRID (4 sản phẩm)
(2, 'ECO Hybrid 5kW (Bản mới 2025)', 16500000, NULL,
'Thương hiệu: LuxPower
Model: SNA5000WPV

Thông số kỹ thuật:
- On-grid/Back-up: 5kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-5kw-sna5000wpv.png', TRUE),

(2, 'ECO Hybrid 6kW', 17500000, NULL,
'Thương hiệu: LuxPower
Model: SNA6000WPV

Thông số kỹ thuật:
- On-grid/Back-up: 6kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-6kw-sna6000wpv.png', TRUE),

(2, 'ECO Hybrid 12kW', 35500000, NULL,
'Thương hiệu: LuxPower
Model: SNA 12K

Thông số kỹ thuật:
- On-grid/Back-up: 12kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-12kw-sna12k.png', TRUE),

(2, 'ECO Hybrid 14kW', 39000000, NULL,
'Thương hiệu: LuxPower
Model: SNA EU 14K

Thông số kỹ thuật:
- On-grid/Back-up: 14kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-14kw-sna-eu-14k.png', TRUE),

-- PHẦN 3: INVERTER LUXPOWER 1 PHA (4 sản phẩm)
(2, 'Hybrid GEN-LB-EU 6K', 28900000, NULL,
'Thương hiệu: LuxPower
Model: GEN-LB-EU 6K

Thông số kỹ thuật:
- On-grid/Backup: 6kW
- Sạc/xả: 6000W 125A/140A
- Điện áp: 1 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-gen-lb-eu-6k.png', TRUE),

(2, 'Hybrid GEN-LB-EU 8K', 48000000, NULL,
'Thương hiệu: LuxPower
Model: GEN-LB-EU 8K

Thông số kỹ thuật:
- On-grid/Backup: 8kW
- Sạc/xả: 8000W 167A/167A
- Điện áp: 1 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-gen-lb-eu-8k.png', TRUE),

(2, 'Hybrid GEN-LB-EU 10K', 54000000, NULL,
'Thương hiệu: LuxPower
Model: GEN-LB-EU 10K

Thông số kỹ thuật:
- On-grid/Backup: 10kW
- Sạc/xả: 10000W 210A/210A
- Điện áp: 1 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-gen-lb-eu-10k.png', TRUE),

(2, 'Hybrid LXP-12K 12kW', 59000000, NULL,
'Thương hiệu: LuxPower
Model: LXP-12K

Thông số kỹ thuật:
- Hòa lưới On-grid: 12kW
- Chạy độc lập Back-up: 12kW
- Điện áp: 1 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-lxp-12k.png', TRUE),

-- PHẦN 4: INVERTER LUXPOWER 3 PHA (2 sản phẩm)
(2, 'Hybrid TriP2-LB-3P 12K 12kW', 59000000, NULL,
'Thương hiệu: LuxPower
Model: TriP2-LB-3P 12K

Thông số kỹ thuật:
- On-grid/Backup: 12kW
- 3 MPPT
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip2-lb-3p-12k.png', TRUE),

(2, 'Hybrid TriP2-LB-3P 15K 15kW', 63000000, NULL,
'Thương hiệu: LuxPower
Model: TriP2-LB-3P 15K

Thông số kỹ thuật:
- On-grid/Backup: 15kW
- 3 MPPT
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip2-lb-3p-15k.png', TRUE),

-- PHẦN 5: INVERTER LUXPOWER 3 PHA ÁP CAO (4 sản phẩm)
(2, 'LUXPOWER Hybrid TRIP 10K', 69000000, NULL,
'Thương hiệu: LuxPower
Model: TRIP 10K

Thông số kỹ thuật:
- Công suất: 10KW
- Điện áp: 3 pha áp cao
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip-10k.png', TRUE),

(2, 'LUXPOWER Hybrid TRIP 15K', 89000000, NULL,
'Thương hiệu: LuxPower
Model: TRIP 15K

Thông số kỹ thuật:
- Công suất: 15KW
- Điện áp: 3 pha áp cao
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip-15k.png', TRUE),

(2, 'LUXPOWER Hybrid TRIP 20K', 109000000, NULL,
'Thương hiệu: LuxPower
Model: TRIP 20K

Thông số kỹ thuật:
- Công suất: 20KW
- Điện áp: 3 pha áp cao
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip-20k.png', TRUE),

(2, 'LUXPOWER Hybrid TRIP 25K', 69000000, NULL,
'Thương hiệu: LuxPower
Model: TRIP 25K

Thông số kỹ thuật:
- Công suất: 25kW
- Hỗ trợ pin: Acquy/Lithium 100-700V
- 3 MPPT
- On-grid/Backup: 50kW
- Bảo hành: 5 năm',
'assets/img/products/luxpower-trip-25k.png', TRUE),

-- PHẦN 6: INVERTER GROWATT (1 sản phẩm)
(2, 'Biến tần Growatt 110kW MAX', 120000000, NULL,
'Thương hiệu: Growatt
Model: MAX 110KTL3-LV

Thông số kỹ thuật:
- Công suất: 110 kW
- Hiệu suất: 98.8%
- Điện áp: 3 pha
- Ứng dụng: Nhà máy, xí nghiệp
- Bảo hành: 5 năm',
'assets/img/products/growatt-110kw-max-real.png', TRUE),

-- PHẦN 7: PIN LƯU TRỮ (2 sản phẩm)
(3, 'Cell A-Cornex LiFePO4 16 Cell', 50000000, NULL,
'Thương hiệu: A-Cornex
Model: LiFePO4 16C

Thông số kỹ thuật:
- Cấu hình: 16 Cell
- Điện áp hệ thống: 52V
- Công nghệ: LiFePO4
- Bảo hành: 10 năm',
'assets/img/products/cell-a-cornex-lifepo4-16cell.png', TRUE),

(3, 'Cell BYD 173ah LiFePO4', 15500000, NULL,
'Thương hiệu: BYD
Model: LiFePO4 173Ah

Thông số kỹ thuật:
- Điện áp: 51.2V
- Dung lượng: 173ah (8.8kW)
- Công nghệ: LiFePO4
- Bảo hành: 10 năm',
'assets/img/products/cell-byd-173ah-lifepo4.png', TRUE),

-- PHẦN 8: TỦ ĐIỆN (4 sản phẩm)
(4, 'Tủ điện Hybrid 1 pha 6kW', 1850000, NULL,
'Thương hiệu: HC Eco
Model: 1P-6KW

Thông số kỹ thuật:
- Công suất hệ thống: 6 kW
- Điện áp: 1 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(4, 'Tủ điện Hybrid 1 pha 12kW', 2850000, NULL,
'Thương hiệu: HC Eco
Model: 1P-12KW

Thông số kỹ thuật:
- Công suất hệ thống: 12 kW
- Điện áp: 1 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(4, 'Tủ điện Hybrid 1 pha 15kW', 3800000, NULL,
'Thương hiệu: HC Eco
Model: 1P-15KW

Thông số kỹ thuật:
- Công suất hệ thống: 15 kW
- Điện áp: 1 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(4, 'Tủ điện Hybrid 3 pha 15kW', 4850000, NULL,
'Thương hiệu: HC Eco
Model: 3P-15KW

Thông số kỹ thuật:
- Công suất hệ thống: 15 kW
- Điện áp: 3 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

-- PHẦN 9: PHỤ KIỆN (7 sản phẩm)
(5, 'Dongles Lan LuxPower', 1200000, NULL,
'Thương hiệu: LuxPower
Model: LAN Dongle

Thông số kỹ thuật:
- Kết nối internet qua dây mạng LAN
- Tương thích: Tất cả inverter LuxPower',
'assets/img/products/dongles-lan-luxpower.png', TRUE),

(5, 'Wifi LuxPower', 1000000, NULL,
'Thương hiệu: LuxPower
Model: WiFi Module

Thông số kỹ thuật:
- Kết nối internet qua WiFi 2.4GHz
- Tương thích: Tất cả inverter LuxPower',
'assets/img/products/wifi-luxpower.png', TRUE),

(5, 'CT ngoài LuxPower SNA 6kW', 800000, NULL,
'Thương hiệu: LuxPower
Model: SNA 6kW CT

Thông số kỹ thuật:
- Dòng điện: 100A/100mA
- Ứng dụng: Đo dòng điện cho inverter',
'assets/img/products/ct-ngoai-luxpower-sna-6kw.png', TRUE),

(5, 'Bách Z', 22000, NULL,
'Thương hiệu: HC Eco
Model: Bách Z

Thông số kỹ thuật:
- Chức năng: Mạ kẽm nhũng nóng áp mái tôn
- Ứng dụng: Cố định khung giá đỡ trên mái tôn
- Vật liệu: Thép mạ kẽm nhúng nóng',
'assets/img/products/bachz.png', TRUE),

(5, 'Kẹp biên, Kẹp giữa tấm Pin', 11500, NULL,
'Thương hiệu: HC Eco
Model: Kẹp Pin

Thông số kỹ thuật:
- Chức năng: Cố định tấm pin vào khung giá đỡ
- Vật liệu: Hợp kim nhôm',
'assets/img/products/kepbien-tamgiua.png', TRUE),

(5, 'Jack Cắm MC4 1500VDC', 14000, NULL,
'Thương hiệu: MC4
Model: MC4 1500VDC

Thông số kỹ thuật:
- Kết nối: Dây cáp DC
- Dòng điện: 30A-40A
- Chống nước: IP67',
'assets/img/products/jackcam.png', TRUE),

(5, 'Dây điện đấu nối tấm PIN', 20000, NULL,
'Thương hiệu: HC Eco
Model: Dây DC

Thông số kỹ thuật:
- Dây DC chuyên dụng: 4-6mm²
- Điện áp: 1000-1500V DC
- Ứng dụng: Đấu nối tấm pin',
'assets/img/products/daydien.png', TRUE);

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
-- Chỉ thêm 2 danh mục gói cơ bản, các thuộc tính khác (badge_text, badge_color) admin sẽ tự thêm sau
INSERT INTO package_categories (id, name, display_order, is_active) VALUES
(1, 'Bảo Duy Solar', 1, TRUE),
(2, 'C - Home Building', 2, TRUE);

-- =====================================================
-- DỮ LIỆU MẪU - PACKAGES
-- =====================================================
-- Không thêm dữ liệu mẫu - Admin sẽ tự thêm gói qua giao diện quản lý

-- =====================================================
-- DỮ LIỆU MẪU - PACKAGE_ITEMS
-- =====================================================
-- Không thêm dữ liệu mẫu - Sẽ được thêm cùng với packages qua giao diện admin

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
(2, 'Admin User', 'admin', '0988919868', '$2y$10$k8S9LHvAOtxAvDFTGmV7n.cyqvIuFbnlZGzZ.DcPzpOihPfnYWbF2', TRUE);

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
SELECT 'Packages: 4 packages' as info;
SELECT 'Reward Templates: 6 templates' as info;
SELECT 'Provinces: 61 provinces/cities' as info;
SELECT 'Districts: Sample districts for major cities' as info;
SELECT 'Test users created:' as info;
SELECT '  - testuser / 123456 (Regular user)' as info;
SELECT '  - admin / admin123 (Admin user)' as info;
SELECT '' as info;
SELECT '⚠️ Product Categories & Products: Không có dữ liệu mẫu' as note;
SELECT '→ Admin cần tự thêm qua giao diện quản lý tại admin.html' as note;
SELECT '' as info;
SELECT 'Ready for HC Eco System!' as status;

