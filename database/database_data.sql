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
INSERT INTO product_categories (id, name, logo_url, display_order, is_active, created_at, updated_at) VALUES
(1, 'Bảo Duy Solar', '/assets/img/categories/category_1761566273_68ff5e41b8ea6.jpg', 1, 1, '2025-10-27 11:31:07', '2025-10-27 11:57:53'),
(2, 'C - Home Building', '/assets/img/categories/category_1761566279_68ff5e47a7bc2.jpg', 2, 1, '2025-10-27 11:31:07', '2025-10-27 11:57:59'),
(3, 'Coffee', '/assets/img/categories/category_1761568681_68ff67a96c3f3.jpg', 3, 1, '2025-10-27 12:38:01', '2025-10-27 12:38:01'),
(4, 'Phúc', '/assets/img/categories/category_1761582235_68ff9c9bed00f.jpg', 4, 1, '2025-10-27 16:23:55', '2025-10-27 16:23:55');

-- =====================================================
-- DỮ LIỆU MẪU - PRODUCTS
-- =====================================================
-- Thêm dữ liệu mẫu cho danh mục "Bảo Duy Solar" (category_id = 1)
-- Giá lắp đặt (category_price) = Giá thị trường (market_price) + 15% lợi nhuận
INSERT INTO products (category_id, title, market_price, category_price, technical_description, image_url, is_active) VALUES
(1, 'Tấm Pin Jinko Solar 590W Tiger Neo', 1800000, 1800000, 
'Thương hiệu: Jinko Solar
Model: Tiger Neo 590W

Thông số kỹ thuật:
- Công suất: 590W
- Công nghệ: N-Type Tiger Neo
- Hiệu suất: 22.3%
- Kích thước: 2278×1134×30mm
- Diện tích: 2,583m²/tấm pin
- Bảo hành: 15 năm sản phẩm, 30 năm công suất', 
'assets/img/products/tam-pin-jinko-solar-590w-tiger-neo.jpg', TRUE),

(1, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1950000, 1950000,
'Thương hiệu: Jinko Solar
Model: Tiger Neo 630W

Thông số kỹ thuật:
- Công suất: 630W
- Công nghệ: N-Type Tiger Neo
- Hiệu suất: 22.5%
- Diện tích: 2,702m²/tấm pin
- Bảo hành: 15 năm sản phẩm, 30 năm công suất',
'assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png', TRUE),

(1, 'ECO Hybrid 5kW (Bản mới 2025)', 16500000, 18975000,
'Thương hiệu: LuxPower
Model: SNA5000WPV

Thông số kỹ thuật:
- On-grid/Back-up: 5kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-5kw-sna5000wpv.png', TRUE),

(1, 'ECO Hybrid 6kW', 17500000, 20125000,
'Thương hiệu: LuxPower
Model: SNA6000WPV

Thông số kỹ thuật:
- On-grid/Back-up: 6kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-6kw-sna6000wpv.png', TRUE),

(1, 'ECO Hybrid 12kW', 35500000, 40825000,
'Thương hiệu: LuxPower
Model: SNA 12K

Thông số kỹ thuật:
- On-grid/Back-up: 12kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-12kw-sna12k.png', TRUE),

(1, 'Hybrid GEN-LB-EU 6K', 21350000, 21350000,
'Thương hiệu: LuxPower
Model: GEN-LB-EU 6K

Thông số kỹ thuật:
- On-grid/Backup: 6kW
- Sạc/xả: 6000W 125A/140A
- Điện áp: 1 pha
- Bảo hành: 12 tháng',
'assets/img/products/hybrid-gen-lb-eu-6k.png', TRUE),

(1, 'Hybrid GEN-LB-EU 8K', 37250000, 37250000,
'Thương hiệu: LuxPower
Model: GEN-LB-EU 8K

Thông số kỹ thuật:
- On-grid/Backup: 8kW
- Sạc/xả: 8000W 167A/167A
- Điện áp: 1 pha
- Bảo hành: 12 tháng',
'assets/img/products/hybrid-gen-lb-eu-8k.png', TRUE),

(1, 'Hybrid GEN-LB-EU 10K', 39350000, 39350000,
'Thương hiệu: LuxPower
Model: GEN-LB-EU 10K

Thông số kỹ thuật:
- On-grid/Backup: 10kW
- Sạc/xả: 10000W 210A/210A
- Điện áp: 1 pha
- Bảo hành: 12 tháng',
'assets/img/products/hybrid-gen-lb-eu-10k.png', TRUE),

(1, 'Hybrid LXP-12K 12kW', 44350000, 44350000,
'Thương hiệu: LuxPower
Model: LXP-12K

Thông số kỹ thuật:
- Hòa lưới On-grid: 12kW
- Chạy độc lập Back-up: 12kW
- Điện áp: 1 pha
- Bảo hành: 12 tháng',
'assets/img/products/hybrid-lxp-12k.png', TRUE),

(1, 'Cell BYD 173ah LiFePO4', 14500000, 14500000,
'Thương hiệu: BYD
Model: LiFePO4 173Ah

Thông số kỹ thuật:
- Điện áp: 51.2V
- Dung lượng: 173ah (8.8kW)
- Công nghệ: LiFePO4
- Bảo hành: 10 năm',
'assets/img/products/cell-byd-173ah-lifepo4.png', TRUE),

(1, 'Cell A-Cornex LiFePO4 16 Cell', 25500000, 25500000,
'Thương hiệu: A-Cornex
Model: LiFePO4 16C

Thông số kỹ thuật:
- Cấu hình: 16 Cell
- Điện áp hệ thống: 52V
- Công nghệ: LiFePO4
- Bảo hành: 10 năm',
'assets/img/products/cell-a-cornex-lifepo4-16cell.png', TRUE),

(1, 'Tủ điện Hybrid 1 pha 6kW', 1850000, 1850000,
'Thương hiệu: HC Eco
Model: 1P-6KW

Thông số kỹ thuật:
- Công suất hệ thống: 6 kW
- Điện áp: 1 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(1, 'Tủ điện Hybrid 1 pha 8kW', 2100000, 2100000,
'Thương hiệu: HC Eco
Model: 1P-8KW

Thông số kỹ thuật:
- Công suất hệ thống: 8 kW
- Điện áp: 1 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(1, 'Tủ điện Hybrid 1 pha 10kW', 2350000, 2350000,
'Thương hiệu: HC Eco
Model: 1P-10KW

Thông số kỹ thuật:
- Công suất hệ thống: 10 kW
- Điện áp: 1 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(1, 'Tủ điện Hybrid 1 pha 12kW', 2600000, 2600000,
'Thương hiệu: HC Eco
Model: 1P-12KW

Thông số kỹ thuật:
- Công suất hệ thống: 12 kW
- Điện áp: 1 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(1, 'Tủ điện Hybrid 3 pha 12kW', 2850000, 2850000,
'Thương hiệu: HC Eco
Model: 3P-12KW

Thông số kỹ thuật:
- Công suất hệ thống: 12 kW
- Điện áp: 3 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(1, 'Tủ điện Hybrid 3 pha 15kW', 3100000, 3100000,
'Thương hiệu: HC Eco
Model: 3P-15KW

Thông số kỹ thuật:
- Công suất hệ thống: 15 kW
- Điện áp: 3 pha
- Bao gồm: MCB, RCCB, SPD, các thiết bị bảo vệ',
'assets/img/products/electrical-cabinet.jpg', TRUE),

(1, 'Dongles Lan LuxPower', 1200000, 1380000,
'Thương hiệu: LuxPower
Model: LAN Dongle

Thông số kỹ thuật:
- Kết nối internet qua dây mạng LAN
- Tương thích: Tất cả inverter LuxPower',
'assets/img/products/dongles-lan-luxpower.png', TRUE),

(1, 'Wifi LuxPower', 1000000, 1150000,
'Thương hiệu: LuxPower
Model: WiFi Module

Thông số kỹ thuật:
- Kết nối internet qua WiFi 2.4GHz
- Tương thích: Tất cả inverter LuxPower',
'assets/img/products/wifi-luxpower.png', TRUE),

(1, 'Bách Z Mạ Kẽm', 80000, 80000,
'Thương hiệu: HC Eco
Model: Bách Z

Thông số kỹ thuật:
- Chức năng: Mạ kẽm nhũng nóng áp mái tôn
- Ứng dụng: Cố định khung giá đỡ trên mái tôn
- Vật liệu: Thép mạ kẽm nhúng nóng
- Sử dụng: 6 cái/tấm pin',
'assets/img/products/bachz.png', TRUE),

(1, 'Kẹp biên, Kẹp giữa tấm Pin', 15000, 15000,
'Thương hiệu: HC Eco
Model: Kẹp Pin

Thông số kỹ thuật:
- Chức năng: Cố định tấm pin vào khung giá đỡ
- Vật liệu: Hợp kim nhôm
- Sử dụng: 6 bộ/tấm pin',
'assets/img/products/kepbien-tamgiua.png', TRUE),

(1, 'Jack MC4 1500VDC', 50000, 50000,
'Thương hiệu: HC Eco
Model: MC4 Connector

Thông số kỹ thuật:
- Chức năng: Kết nối dây điện giữa các tấm pin
- Tiêu chuẩn: IP67
- Vật liệu: Nhựa chống UV
- Sử dụng: Số tấm + 3 bộ dự phòng',
'assets/img/products/jackcam.png', TRUE),

(1, 'Dây Điện (AC/DC)', 30000, 30000,
'Thương hiệu: HC Eco
Model: PV Cable 4mm² / 6mm²

Thông số kỹ thuật:
- Tiết diện: 4mm² hoặc 6mm²
- Tiêu chuẩn: TUV, UL
- Chống tia cực tím, chịu nhiệt độ cao
- Đơn giá: 30,000 VNĐ/mét
- Dự trù: 100m cho toàn bộ hệ thống',
'assets/img/products/daydien.png', TRUE),

-- Thêm sản phẩm từ ảnh còn dư
(1, 'ECO Hybrid 14kW', 42000000, 48300000,
'Thương hiệu: LuxPower
Model: SNA-EU-14K

Thông số kỹ thuật:
- On-grid/Back-up: 14kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'assets/img/products/eco-hybrid-14kw-sna-eu-14k.png', TRUE),

(1, 'Hybrid GEN-LB-EU 12K', 62000000, 71300000,
'Thương hiệu: LuxPower
Model: GEN-LB-EU 12K

Thông số kỹ thuật:
- On-grid/Backup: 12kW
- Sạc/xả: 12000W 250A/250A
- Điện áp: 1 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-gen-lb-eu-12k.png', TRUE),

(1, 'Pin lưu trữ BYD', 14500000, 14500000,
'Thương hiệu: BYD
Model: LiFePO4 173Ah

Thông số kỹ thuật:
- Điện áp: 51.2V
- Dung lượng: 173ah (8.8kW)
- Công nghệ: LiFePO4
- Bảo hành: 10 năm',
'assets/img/products/pin-luu-tru-byd.jpg', TRUE),

(1, 'Pin lưu trữ A-Cornex', 25500000, 25500000,
'Thương hiệu: A-Cornex
Model: LiFePO4 16C

Thông số kỹ thuật:
- Cấu hình: 16 Cell
- Điện áp hệ thống: 52V
- Dung lượng: 16.3kWh (315Ah @ 52V)
- Công nghệ: LiFePO4
- Bảo hành: 10 năm',
'assets/img/products/pin-luu-tru-acornex.jpg', TRUE),

(1, 'Hybrid TRIP 10k', 52000000, 59800000,
'Thương hiệu: LuxPower
Model: TRIP-10K

Thông số kỹ thuật:
- On-grid/Backup: 10kW
- Điện áp: 1 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip-10k.png', TRUE),

(1, 'Hybrid TRIP 15k', 65000000, 74750000,
'Thương hiệu: LuxPower
Model: TRIP-15K

Thông số kỹ thuật:
- On-grid/Backup: 15kW
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip-15k.png', TRUE),

(1, 'Hybrid TRIP 20k', 75000000, 86250000,
'Thương hiệu: LuxPower
Model: TRIP-20K

Thông số kỹ thuật:
- On-grid/Backup: 20kW
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip-20k.png', TRUE),

(1, 'Hybrid TRIP 25k', 85000000, 97750000,
'Thương hiệu: LuxPower
Model: TRIP-25K

Thông số kỹ thuật:
- On-grid/Backup: 25kW
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip-25k.png', TRUE),

(1, 'LuxPower TRIP 25kW', 88000000, 101200000,
'Thương hiệu: LuxPower
Model: TRIP-25KW

Thông số kỹ thuật:
- On-grid/Backup: 25kW
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/luxpower-trip-25k.png', TRUE),

(1, 'Hybrid TRIP2 LB 3P 12k', 48250000, 48250000,
'Thương hiệu: LuxPower
Model: TRIP2-LB-3P-12K

Thông số kỹ thuật:
- On-grid/Backup: 12kW
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip2-lb-3p-12k.png', TRUE),

(1, 'Hybrid TRIP2 LB 3P 15k', 51250000, 51250000,
'Thương hiệu: LuxPower
Model: TRIP2-LB-3P-15K

Thông số kỹ thuật:
- On-grid/Backup: 15kW
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/hybrid-trip2-lb-3p-15k.png', TRUE),

(1, 'CT Ngoài LuxPower SNA 6kW', 1500000, 1725000,
'Thương hiệu: LuxPower
Model: CT-External-6K

Thông số kỹ thuật:
- Chức năng: Hạt nhân đo dòng điện
- Tương thích: Inverter LuxPower 6kW
- Bảo hành: 1 năm',
'assets/img/products/ct-ngoai-luxpower-sna-6kw.png', TRUE),

(1, 'Growatt 110kW MAX', 185000000, 212750000,
'Thương hiệu: Growatt
Model: MAX-110KW

Thông số kỹ thuật:
- Công suất: 110kW
- Điện áp: 3 pha
- Bảo hành: 5 năm',
'assets/img/products/growatt-110kw-max-real.png', TRUE);

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
INSERT INTO package_categories (id, name, logo_url, badge_text, badge_color, display_order, is_active, created_at, updated_at) VALUES
(1, 'Bảo Duy Solar', '/assets/img/package-categories/package-category_1761566289_68ff5e518d89e.jpg', 'Siêu Hot', 'yellow', 1, 1, '2025-10-27 11:31:08', '2025-10-27 12:10:03'),
(2, 'C - Home Building', '/assets/img/package-categories/package-category_1761566300_68ff5e5c5676c.jpg', 'New', 'blue', 2, 1, '2025-10-27 11:31:08', '2025-10-27 12:02:27'),
(3, 'Coffee', '/assets/img/package-categories/package-category_1761568732_68ff67dcd01c8.jpg', 'Bán Chạy', 'purple', 1, 1, '2025-10-27 12:38:52', '2025-10-27 12:38:58');

-- =====================================================
-- DỮ LIỆU MẪU - PACKAGES
-- =====================================================
INSERT INTO packages (id, category_id, name, description, price, savings_per_month, payback_period, highlights, badge_text, badge_color, display_order, is_active, created_at, updated_at) VALUES
(1, 1, 'Gói Solar 3kW - Hộ Gia Đình', 'Hệ thống điện mặt trời 3kW phù hợp cho gia đình 2-3 người, giúp giảm 70-80% hóa đơn điện hàng tháng.', 145000000.00, '~2.5 triệu/tháng', '4-5 năm', '[{\"title\":\"Tiết kiệm/tháng\",\"content\":\"~2.5 triệu/tháng\"},{\"title\":\"Hoàn vốn\",\"content\":\"4-5 năm\"}]', 'PHỔ BIẾN', 'red', 1, 1, '2025-10-27 11:31:09', '2025-10-27 12:03:40'),
(2, 1, 'Gói Solar 5kW - Gia Đình Vừa', 'Hệ thống điện mặt trời 5kW phù hợp cho gia đình 4-5 người, công suất cao, tiết kiệm tối đa.', 225000000.00, '~4 triệu/tháng', '4-5 năm', NULL, 'BÁN CHẠY', 'red', 2, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 1, 'Gói Solar 10kW - Doanh Nghiệp Nhỏ', 'Hệ thống điện mặt trời 10kW phù hợp cho cửa hàng, văn phòng nhỏ, doanh nghiệp tiết kiệm chi phí.', 425000000.00, '~8 triệu/tháng', '4-5 năm', NULL, 'KHUYẾN MÃI', 'green', 3, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(4, 1, 'Gói Solar 20kW - Nhà Xưởng', 'Hệ thống điện mặt trời 20kW phù hợp cho nhà xưởng, doanh nghiệp vừa, tiết kiệm năng lượng lớn.', 785000000.00, '~15 triệu/tháng', '4-5 năm', NULL, 'TIẾT KIỆM', 'yellow', 4, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(5, 2, 'Hệ Thống Điện Nhà Thông Minh', 'Tích hợp hệ thống điện mặt trời với hệ thống điều khiển thông minh, tự động hóa toàn bộ.', 555000000.00, '~10 triệu/tháng', '4-5 năm', NULL, 'MỚI', 'purple', 1, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09');

-- =====================================================
-- DỮ LIỆU MẪU - PACKAGE_ITEMS
-- =====================================================
INSERT INTO package_items (package_id, item_name, item_description, display_order) VALUES
-- Items cho Gói Solar 3kW
(1, 'Tấm Pin Jinko 590W', '10 tấm pin Jinko Solar 590W Tiger Neo', 1),
(1, 'Inverter LuxPower 5kW', 'Bộ inverter hybrid ECO 5kW, hỗ trợ backup', 2),
(1, 'Pin Lưu Trữ BYD', '1 pin lưu trữ BYD 8.8kW (173Ah)', 3),
(1, 'Tủ Điện 1P-6kW', 'Tủ điện hybrid 1 pha 6kW đầy đủ thiết bị', 4),
(1, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 5),

-- Items cho Gói Solar 5kW
(2, 'Tấm Pin Jinko 630W', '10 tấm pin Jinko Solar 630W Tiger Neo', 1),
(2, 'Inverter LuxPower 8kW', 'Bộ inverter hybrid 8kW GEN-LB-EU', 2),
(2, 'Pin Lưu Trữ BYD', '2 pin lưu trữ BYD 8.8kW (173Ah)', 3),
(2, 'Tủ Điện 1P-12kW', 'Tủ điện hybrid 1 pha 12kW đầy đủ thiết bị', 4),
(2, 'Dongles LAN', 'Modul kết nối internet LAN', 5),
(2, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 6),

-- Items cho Gói Solar 10kW
(3, 'Tấm Pin Jinko 630W', '20 tấm pin Jinko Solar 630W Tiger Neo', 1),
(3, 'Inverter LuxPower 12kW', 'Bộ inverter hybrid 12kW ECO', 2),
(3, 'Pin A-Cornex 16 Cell', '1 pin lưu trữ A-Cornex 16 Cell', 3),
(3, 'Tủ Điện 1P-15kW', 'Tủ điện hybrid 1 pha 15kW đầy đủ thiết bị', 4),
(3, 'Dongles LAN', 'Modul kết nối internet LAN', 5),
(3, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 6),

-- Items cho Gói Solar 20kW
(4, 'Tấm Pin Jinko 630W', '40 tấm pin Jinko Solar 630W Tiger Neo', 1),
(4, 'Inverter LuxPower 25kW', 'Bộ inverter hybrid 25kW TRIP', 2),
(4, 'Pin A-Cornex 16 Cell', '2 pin lưu trữ A-Cornex 16 Cell', 3),
(4, 'Tủ Điện 3P-15kW', 'Tủ điện hybrid 3 pha 15kW đầy đủ thiết bị', 4),
(4, 'Dongles LAN', 'Modul kết nối internet LAN', 5),
(4, 'Phụ Kiện', 'Dây cáp DC, kẹp pin, Bách Z và phụ kiện đầy đủ', 6),

-- Items cho Hệ Thống Điện Nhà Thông Minh
(5, 'Tấm Pin Jinko 630W', '15 tấm pin Jinko Solar 630W Tiger Neo', 1),
(5, 'Inverter LuxPower 10kW', 'Bộ inverter hybrid 10kW GEN-LB-EU', 2),
(5, 'Pin Lưu Trữ BYD', '1 pin lưu trữ BYD 8.8kW (173Ah)', 3),
(5, 'Tủ Điện Thông Minh', 'Tủ điện hybrid 1 pha 12kW với điều khiển thông minh', 4),
(5, 'App Điều Khiển', 'Ứng dụng điện thoại điều khiển hệ thống', 5),
(5, 'WiFi Module', 'Modul kết nối WiFi', 6),
(5, 'Phụ Kiện Cao Cấp', 'Dây cáp DC cao cấp, kẹp pin, Bách Z và phụ kiện đầy đủ', 7);

-- =====================================================
-- DỮ LIỆU MẪU - REWARD_TEMPLATES
-- =====================================================
INSERT INTO reward_templates (id, reward_name, reward_type, reward_value, reward_description, reward_quantity, reward_image, is_active, created_at, updated_at) VALUES
(1, 'Voucher giảm 500.000đ', 'voucher', 500000.00, 'Voucher giảm giá 500.000đ cho đơn hàng tiếp theo', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(2, 'Voucher giảm 1.000.000đ', 'voucher', 1000000.00, 'Voucher giảm giá 1.000.000đ cho đơn hàng tiếp theo', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 'Tiền mặt 200.000đ', 'cash', 200000.00, 'Nhận ngay 200.000đ tiền mặt', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(4, 'Tiền mặt 500.000đ', 'cash', 500000.00, 'Nhận ngay 500.000đ tiền mặt', NULL, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(5, 'Chai nước giặt Omo', 'gift', NULL, 'Chai nước giặt Omo 3.8kg', 100, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(6, 'Bộ dụng cụ gia đình', 'gift', NULL, 'Bộ dụng cụ gia đình 10 món', 50, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09');

-- =====================================================
-- DỮ LIỆU TEST - USER MẪU (Để test)
-- =====================================================
-- Tạo user test (password: 123456 - đã hash)
-- Tạo admin user (username: admin, password: admin123)
INSERT INTO users (id, full_name, username, phone, password, is_admin, created_at, updated_at) VALUES
(1, 'Test User', 'testuser', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(2, 'Admin User', 'admin', '0988919868', '$2y$10$k8S9LHvAOtxAvDFTGmV7n.cyqvIuFbnlZGzZ.DcPzpOihPfnYWbF2', 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 'Đỗ Quang Phúc', 'quangphuc', '0375779219', '$2y$10$r6M1D/MPVDVm9bXdlOaA4.NxfWO68iL2loDBpuZTySRWGnFVovhui', 0, '2025-10-27 11:57:05', '2025-10-27 11:57:05');

-- Tạo lottery tickets test cho user
INSERT INTO lottery_tickets (id, user_id, ticket_type, status, created_at) VALUES
(1, 1, 'bonus', 'active', '2025-10-27 11:31:10'),
(2, 1, 'bonus', 'active', '2025-10-27 11:31:10'),
(3, 1, 'bonus', 'active', '2025-10-27 11:31:10');

-- =====================================================
-- DỮ LIỆU TEST - VOUCHERS MẪU
-- =====================================================
INSERT INTO vouchers (id, code, discount_amount, description, is_used, used_by_user_id, used_at, expires_at, created_at) VALUES
(1, 'WELCOME500K', 500000.00, 'Voucher chào mừng khách hàng mới', 0, NULL, NULL, '2025-11-26 11:31:10', '2025-10-27 11:31:10'),
(2, 'NEWYEAR1M', 1000000.00, 'Voucher năm mới giảm 1 triệu', 0, NULL, NULL, '2025-12-26 11:31:10', '2025-10-27 11:31:10');

-- =====================================================
-- DỮ LIỆU MẪU - SURVEY_PRODUCT_CONFIGS (Cấu hình sản phẩm cho khảo sát)
-- =====================================================
INSERT INTO survey_product_configs (id, product_id, survey_category, phase_type, price_type, is_active, display_order, notes, created_at, updated_at) VALUES
(1, 1, 'solar_panel', 'none', 'market_price', 1, 1, 'Jinko 590W', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(2, 2, 'solar_panel', 'none', 'market_price', 1, 2, 'Jinko 630W', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(3, 5, 'inverter', '1_phase', 'market_price', 1, 1, 'ECO Hybrid 5kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(4, 6, 'inverter', '1_phase', 'market_price', 1, 2, 'ECO Hybrid 6kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(5, 7, 'inverter', '1_phase', 'market_price', 1, 3, 'ECO Hybrid 12kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(6, 8, 'inverter', '1_phase', 'market_price', 1, 4, 'Hybrid GEN-LB-EU 6K', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(7, 9, 'inverter', '1_phase', 'market_price', 1, 5, 'Hybrid GEN-LB-EU 8K', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(8, 10, 'inverter', '1_phase', 'market_price', 1, 6, 'Hybrid GEN-LB-EU 10K', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(9, 11, 'inverter', '1_phase', 'market_price', 1, 7, 'Hybrid LXP-12K', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(10, 12, 'battery', 'none', 'market_price', 1, 1, 'Cell BYD 173ah', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(11, 13, 'battery', 'none', 'market_price', 1, 2, 'Cell A-Cornex 16 Cell', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(12, 14, 'electrical_cabinet', '1_phase', 'market_price', 1, 1, '1 pha 6kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(13, 15, 'electrical_cabinet', '1_phase', 'market_price', 1, 2, '1 pha 8kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(14, 16, 'electrical_cabinet', '1_phase', 'market_price', 1, 3, '1 pha 10kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(15, 17, 'electrical_cabinet', '1_phase', 'market_price', 1, 4, '1 pha 12kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(16, 18, 'electrical_cabinet', '3_phase', 'market_price', 1, 1, '3 pha 12kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(17, 19, 'electrical_cabinet', '3_phase', 'market_price', 1, 2, '3 pha 15kW', '2025-10-27 11:31:10', '2025-10-27 11:31:10'),
(18, 3, 'inverter', '1_phase', 'category_price', 1, 1, '', '2025-10-27 11:52:42', '2025-10-27 11:52:42'),
(19, 4, 'inverter', '1_phase', 'category_price', 1, 1, '', '2025-10-27 12:10:31', '2025-10-27 12:10:31'),
(20, 20, 'accessory', 'none', 'market_price', 1, 0, '', '2025-10-27 12:10:46', '2025-10-27 12:10:46'),
(21, 21, 'accessory', 'none', 'category_price', 1, 0, '', '2025-10-27 12:10:54', '2025-10-27 12:10:54'),
(22, 22, 'accessory', 'none', 'category_price', 1, 0, '', '2025-10-27 12:11:01', '2025-10-27 12:11:01');

-- =====================================================
-- DỮ LIỆU MẪU - INTRO_POSTS (Bài viết trang giới thiệu)
-- =====================================================
INSERT INTO intro_posts (id, title, description, image_url, video_url, is_active, display_order, created_at, updated_at) VALUES
(1, 'HC Eco System - Giải Pháp Năng Lượng Xanh', 'HC Eco System là đơn vị hàng đầu trong lĩnh vực cung cấp giải pháp năng lượng mặt trời tại Việt Nam. Với kinh nghiệm nhiều năm trong ngành, chúng tôi tự hào là đối tác đáng tin cậy của hàng ngàn khách hàng trên toàn quốc.', NULL, NULL, 1, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(2, 'Sứ Mệnh Của Chúng Tôi', 'Chúng tôi cam kết cung cấp các giải pháp năng lượng mặt trời chất lượng cao, hiệu quả và tiết kiệm chi phí. Đội ngũ chuyên nghiệp, thiết bị chính hãng và dịch vụ tận tâm.', NULL, NULL, 1, 2, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 'Tầm Nhìn 2030', 'Trở thành đơn vị dẫn đầu trong lĩnh vực năng lượng tái tạo tại Việt Nam, góp phần xây dựng một tương lai xanh, bền vững và thân thiện với môi trường.', NULL, NULL, 1, 3, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(4, 'Giải Pháp Cho Mọi Quy Mô', 'Từ hộ gia đình nhỏ với hệ thống 3kW đến doanh nghiệp lớn với công suất 500kW+, chúng tôi có giải pháp phù hợp cho mọi nhu cầu.', NULL, NULL, 1, 4, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(5, 'dddddđ', 'ffffffffffffffffffffff', '/uploads/intro_images/intro_image_1761599252_68ffdf1432557.jpg', '/uploads/intro_videos/intro_video_1761600454_68ffe3c698f1a.mp4', 1, 1, '2025-10-27 21:07:32', '2025-10-27 21:27:34'),
(6, 'faaf', 'aaaaaaaaaa', '/uploads/intro_images/intro_image_1761600209_68ffe2d1bc014.jpg', '/uploads/intro_videos/intro_video_1761600190_68ffe2bee024b.mp4', 1, 5, '2025-10-27 21:23:10', '2025-10-27 21:23:29');

-- =====================================================
-- DỮ LIỆU MẪU - PROJECTS (Dự án)
-- =====================================================
INSERT INTO projects (id, title, description, image_url, video_url, is_active, display_order, created_at, updated_at) VALUES
(1, 'Lắp Đặt Hệ Thống 5kW Tại Gia Đình Sài Gòn', 'Hệ thống điện mặt trời 5kW cho gia đình tại Quận 7, TP. Hồ Chí Minh. Sử dụng công nghệ pin cao cấp từ Jinko Solar và inverter Growatt.', NULL, NULL, 1, 1, '2025-10-27 12:00:00', '2025-10-27 12:00:00'),
(2, 'Dự Án Điện Mặt Trời 10kW Cho Shop Coffee', 'Lắp đặt hệ thống 10kW cho cửa hàng coffee shop tại Đà Nẵng. Giúp giảm hơn 80% chi phí điện hàng tháng.', NULL, NULL, 1, 2, '2025-10-27 12:00:00', '2025-10-27 12:00:00'),
(3, 'Hệ Thống 20kW Cho Nhà Xưởng', 'Dự án lắp đặt 20kW trên mái nhà xưởng tại Bình Dương. Công suất lớn đáp ứng nhu cầu sản xuất và còn dư điện bán lưới.', NULL, NULL, 1, 3, '2025-10-27 12:00:00', '2025-10-27 12:00:00'),
(4, 'Hệ Thống Hybrid 15kW Có Pin Lưu Trữ', 'Dự án hệ thống hybrid 15kW kết hợp pin lưu trữ tại Vũng Tàu. Tự chủ năng lượng 24/7, không lo mất điện.', NULL, NULL, 1, 4, '2025-10-27 12:00:00', '2025-10-27 12:00:00');

-- =====================================================
-- HOÀN THÀNH IMPORT DỮ LIỆU
-- =====================================================
SELECT 'Sample data imported successfully!' as message;
SELECT 'Packages: 4 packages' as info;
SELECT 'Reward Templates: 6 templates' as info;
SELECT 'Intro Posts: 6 posts' as info;
SELECT 'Projects: 4 projects' as info;
SELECT 'Provinces: 61 provinces/cities' as info;
SELECT 'Districts: Sample districts for major cities' as info;
SELECT 'Test users created:' as info;
SELECT '  - testuser / 123456 (Regular user)' as info;
SELECT '  - admin / admin123 (Admin user)' as info;
SELECT '' as info;
SELECT 'Ready for HC Eco System!' as status;

