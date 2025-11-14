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
(1, 'Bảo Duy Solar', '/assets/img/categories/category_1761694964_690154f4117b8.jpg', 1, 1, '2025-10-27 11:31:07', '2025-10-28 23:42:44'),
(2, 'C - Home Building', '/assets/img/categories/category_1761694975_690154ffe28bd.jpg', 2, 1, '2025-10-27 11:31:07', '2025-10-28 23:42:55'),
(3, 'HC - Coffee & Restaurant', '/assets/img/categories/category_1761694990_6901550e031fa.jpg', 3, 1, '2025-10-27 12:38:01', '2025-10-28 23:43:10'),
(4, 'HC - Travel', '/assets/img/categories/category_1761695000_69015518e7924.jpg', 4, 1, '2025-10-27 16:23:55', '2025-10-28 23:43:32');

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

(1, 'ECO Hybrid 5kW (Bản mới 2025)', 16500000, 14500000,
'Thương hiệu: LuxPower
Model: SNA5000WPV

Thông số kỹ thuật:
- On-grid/Back-up: 5kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'/assets/img/products/luxpower-6kw-gen.png', TRUE),

(1, 'ECO Hybrid 6kW', 20125000, 14500000,
'Thương hiệu: LuxPower
Model: SNA6000WPV

Thông số kỹ thuật:
- On-grid/Back-up: 6kW
- Điện áp: 1 pha
- Hỗ trợ pin lithium và ắc quy
- Bảo hành: 5 năm',
'/assets/img/products/luxpower-6kw-gen.png', TRUE),

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
(1, 'Bảo Duy Solar', '/assets/img/package-categories/package-category_1761695061_690155556371e.jpg', 'Siêu Hot', '#fb8b23', 1, 1, '2025-10-27 11:31:08', '2025-10-28 23:44:21'),
(2, 'C - Home Building', '/assets/img/package-categories/package-category_1761695069_6901555d9710f.jpg', 'New', '#568de6', 2, 1, '2025-10-27 11:31:08', '2025-10-28 23:44:29'),
(3, 'Coffee', '/assets/img/package-categories/package-category_1761695078_69015566d7908.jpg', 'Bán Chạy', '#5ff7ec', 3, 1, '2025-10-27 12:38:52', '2025-10-28 23:44:38');

-- =====================================================
-- DỮ LIỆU MẪU - PACKAGES
-- =====================================================
INSERT INTO packages (id, category_id, name, description, price, savings_per_month, payback_period, highlights, badge_text, badge_color, display_order, is_active, created_at, updated_at) VALUES
(1, 1, 'Gói Solar 3kW - Hộ Gia Đình', 'Hệ thống điện mặt trời 3kW phù hợp cho gia đình 2-3 người, giúp giảm 70-80% hóa đơn điện hàng tháng.', 145000000.00, '~2.5 triệu/tháng', '4-5 năm', '[{\"title\":\"Tiết kiệm/tháng\",\"content\":\"~2.5 triệu/tháng\"},{\"title\":\"Hoàn vốn\",\"content\":\"4-5 năm\"}]', 'PHỔ BIẾN', '#ff0a0a', 1, 1, '2025-10-27 11:31:09', '2025-10-27 23:48:57'),
(2, 1, 'Gói Solar 5kW - Gia Đình Vừa', 'Hệ thống điện mặt trời 5kW phù hợp cho gia đình 4-5 người, công suất cao, tiết kiệm tối đa.', 225000000.00, '~4 triệu/tháng', '4-5 năm', NULL, 'BÁN CHẠY', 'red', 2, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 1, 'Gói Solar 10kW - Doanh Nghiệp Nhỏ', 'Hệ thống điện mặt trời 10kW phù hợp cho cửa hàng, văn phòng nhỏ, doanh nghiệp tiết kiệm chi phí.', 425000000.00, '~8 triệu/tháng', '4-5 năm', NULL, 'KHUYẾN MÃI', 'green', 3, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(4, 1, 'Gói Solar 20kW - Nhà Xưởng', 'Hệ thống điện mặt trời 20kW phù hợp cho nhà xưởng, doanh nghiệp vừa, tiết kiệm năng lượng lớn.', 785000000.00, '~15 triệu/tháng', '4-5 năm', NULL, 'TIẾT KIỆM', 'yellow', 4, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(5, 2, 'Hệ Thống Điện Nhà Thông Minh', 'Tích hợp hệ thống điện mặt trời với hệ thống điều khiển thông minh, tự động hóa toàn bộ.', 555000000.00, '~10 triệu/tháng', '4-5 năm', '[{\"title\":\"Tiết kiệm/tháng\",\"content\":\"~10 triệu/tháng\"},{\"title\":\"Hoàn vốn\",\"content\":\"4-5 năm\"}]', 'MỚI', '#8b5cf6', 7, 1, '2025-10-27 11:31:09', '2025-10-27 23:48:42');

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
(6, 'Bộ dụng cụ gia đình', 'gift', NULL, 'Bộ dụng cụ gia đình 10 món', 50, NULL, 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(7, 'Voucher giam 1 trieu', 'voucher', 1000000.00, 'cho 0988919868', NULL, NULL, 1, '2025-10-28 14:55:38', '2025-10-28 14:55:38');

-- =====================================================
-- DỮ LIỆU MẪU - WHEEL_PRIZES (Phần thưởng vòng quay admin)
-- =====================================================
INSERT INTO wheel_prizes (id, prize_name, prize_description, prize_value, prize_icon, prize_color, probability_weight, is_active, created_at, updated_at) VALUES
(1, 'Voucher 500K', 'Giảm ngay 500.000đ cho đơn hàng bất kỳ', '500.000đ', '🎟️', '#F59E0B', 3, 1, NOW(), NOW()),
(2, 'Pin dự phòng mini', 'Tặng pin dự phòng mini HC', 'Quà tặng', '🔋', '#3B82F6', 2, 1, NOW(), NOW()),
(3, 'Giảm 15%', 'Giảm 15% cho gói khảo sát bất kỳ', '15%', '💚', '#10B981', 4, 1, NOW(), NOW()),
(4, 'Combo vệ sinh hệ pin', 'Miễn phí vệ sinh hệ pin 1 lần', 'Dịch vụ', '🧽', '#6366F1', 2, 1, NOW(), NOW()),
(5, 'Chúc may mắn lần sau', 'Không trúng, thử lại nhé!', 'May mắn lần sau', '🍀', '#9CA3AF', 5, 1, NOW(), NOW()),
(6, 'Voucher 1 Triệu', 'Giảm 1.000.000đ cho đơn hàng > 30 triệu', '1.000.000đ', '💎', '#EC4899', 1, 1, NOW(), NOW());

-- =====================================================
-- DỮ LIỆU TEST - USER MẪU (Để test)
-- =====================================================
-- Tạo user test (password: 123456 - đã hash)
-- Tạo admin user (username: admin, password: admin123)
INSERT INTO users (id, full_name, username, phone, password, is_admin, created_at, updated_at) VALUES
(1, 'Test User', 'testuser', '0123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(2, 'Admin User', 'admin', '0988919868', '$2y$10$k8S9LHvAOtxAvDFTGmV7n.cyqvIuFbnlZGzZ.DcPzpOihPfnYWbF2', 1, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 'Đỗ Quang Phúc', 'quangphuc', '0375779219', '$2y$10$r6M1D/MPVDVm9bXdlOaA4.NxfWO68iL2loDBpuZTySRWGnFVovhui', 0, '2025-10-27 11:57:05', '2025-10-27 11:57:05'),
(4, 'Nguyễn Minh Hải', 'hainm', '1234567899', '$2y$10$mjReWJD1Izqe1NrwrqqXkeyylvCl/YP68tGYc/pQbY/tj/Ojx/wfy', 1, '2025-10-28 14:22:57', '2025-10-28 14:24:56');

-- Lottery tickets sẽ được tạo tự động khi user đặt hàng hoặc nhận thưởng
-- Không cần dữ liệu mẫu

-- =====================================================
-- DỮ LIỆU TEST - LOTTERY REWARDS (Phần thưởng vòng quay)
-- =====================================================
-- Phần thưởng sẽ được tạo tự động khi user quay và trúng thưởng
-- Không cần dữ liệu mẫu

-- =====================================================
-- DỮ LIỆU TEST - VOUCHERS MẪU
-- =====================================================
INSERT INTO vouchers (id, code, discount_amount, description, is_used, used_by_user_id, used_at, expires_at, created_at) VALUES
(1, 'WELCOME500K', 500000.00, 'Voucher chào mừng khách hàng mới', 0, NULL, NULL, '2025-11-26 11:31:10', '2025-10-27 11:31:10'),
(2, 'NEWYEAR1M', 1000000.00, 'Voucher năm mới giảm 1 triệu', 0, NULL, NULL, '2025-12-26 11:31:10', '2025-10-27 11:31:10'),
(3, 'VC6900104027C86', 1000000.00, 'Voucher giảm 1.000.000đ - Từ vòng quay may mắn', 0, NULL, NULL, '2025-11-27 08:37:20', '2025-10-28 00:37:20'),
(4, 'VC6900D9CE9EC07', 1000000.00, 'Voucher giam 1 trieu - Từ vòng quay may mắn', 0, NULL, NULL, '2025-11-27 22:57:18', '2025-10-28 14:57:18');

-- =====================================================
-- DỮ LIỆU TEST - CART ITEMS (Giỏ hàng)
-- =====================================================
INSERT INTO cart_items (id, user_id, product_id, quantity, created_at, updated_at) VALUES
(4, 2, 1, 1, '2025-10-28 14:57:47', '2025-10-28 14:57:47');

-- =====================================================
-- DỮ LIỆU TEST - ORDERS (Đơn hàng)
-- =====================================================
INSERT INTO orders (id, user_id, full_name, phone, email, city, district, ward, address, notes, subtotal, voucher_code, discount_amount, total_amount, order_status, approved_by, approved_at, created_at) VALUES
(1, 2, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Yên Bái', 'Thị xã Nghĩa Lộ', 'Xã Phù Nham', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1950000.00, NULL, 0.00, 1950000.00, 'approved', 2, '2025-10-28 00:28:18', '2025-10-28 00:02:26'),
(2, 2, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Hoà Bình', 'Huyện Yên Thủy', 'Xã Đoàn Kết', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1950000.00, 'VC6900104027C86', 1000000.00, 950000.00, 'approved', 2, '2025-10-28 00:39:00', '2025-10-28 00:37:48'),
(3, 4, 'hai', '0987955829', 'onemusicdanang@gmail.com', 'Thành phố Đà Nẵng', 'Quận Sơn Trà', 'Phường Thọ Quang', '93 Võ Duy Ninh, Phường Thọ Quang, Sơn Trà, Đà Nẵng', '', 40250000.00, NULL, 0.00, 40250000.00, 'approved', 2, '2025-10-28 16:38:42', '2025-10-28 14:46:53'),
(4, 2, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Hưng Đạo', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1500000.00, NULL, 0.00, 1500000.00, 'approved', 2, '2025-10-28 16:34:03', '2025-10-28 16:33:41'),
(5, 3, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Hồng An', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 1950000.00, NULL, 0.00, 1950000.00, 'approved', 2, '2025-10-28 16:35:08', '2025-10-28 16:34:48'),
(6, 3, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Bảo Lạc', 'Xã Phan Thanh', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 20125000.00, NULL, 0.00, 20125000.00, 'approved', 2, '2025-10-28 16:37:36', '2025-10-28 16:36:31'),
(7, 3, 'ĐỖ QUANG PHÚC', '0375779219', 'doquangphuc21@gmail.com', 'Tỉnh Cao Bằng', 'Huyện Hạ Lang', 'Xã An Lạc', 'Lê Đỉnh, Điện Ngọc, Điện Bàn, Quảng Nam', '', 40250000.00, NULL, 0.00, 40250000.00, 'approved', 2, '2025-10-28 16:37:39', '2025-10-28 16:36:53');

-- =====================================================
-- DỮ LIỆU TEST - ORDER ITEMS (Chi tiết đơn hàng)
-- =====================================================
INSERT INTO order_items (id, order_id, product_id, product_name, quantity, price, image_url) VALUES
(1, 1, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '../assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png'),
(2, 2, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '../assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png'),
(3, 3, 4, 'ECO Hybrid 6kW', 2, 20125000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(4, 4, 35, 'CT Ngoài LuxPower SNA 6kW', 1, 1500000.00, '../assets/img/products/ct-ngoai-luxpower-sna-6kw.png'),
(5, 5, 2, 'Tấm Pin Jinko Solar 630W Tiger Neo', 1, 1950000.00, '../assets/img/products/tam-pin-jinko-solar-630w-tiger-neo.png'),
(6, 6, 4, 'ECO Hybrid 6kW', 1, 20125000.00, '../assets/img/products/luxpower-6kw-gen.png'),
(7, 7, 4, 'ECO Hybrid 6kW', 2, 20125000.00, '../assets/img/products/luxpower-6kw-gen.png');

-- =====================================================
-- DỮ LIỆU TEST - SOLAR SURVEYS (Khảo sát điện mặt trời)
-- =====================================================
INSERT INTO solar_surveys (id, user_id, full_name, phone, region, phase, solar_panel_type, monthly_bill, usage_time, created_at, updated_at) VALUES
(1, 2, 'Admin User', '0988919868', 'mien-bac', 3, 630, 2500000.00, 'balanced', '2025-10-28 00:04:38', '2025-10-28 00:04:38');

-- =====================================================
-- DỮ LIỆU TEST - SURVEY RESULTS (Kết quả khảo sát)
-- =====================================================
INSERT INTO survey_results (id, survey_id, monthly_kwh, sun_hours, region_name, panel_id, panel_name, panel_power, panel_price, panels_needed, panel_cost, energy_per_panel_per_day, total_capacity, inverter_id, inverter_name, inverter_capacity, inverter_price, cabinet_id, cabinet_name, cabinet_capacity, cabinet_price, battery_needed, battery_type, battery_id, battery_name, battery_capacity, battery_quantity, battery_unit_price, battery_cost, bach_z_qty, bach_z_price, bach_z_cost, clip_qty, clip_price, clip_cost, jack_mc4_qty, jack_mc4_price, jack_mc4_cost, dc_cable_length, dc_cable_price, dc_cable_cost, accessories_cost, labor_cost, total_cost_without_battery, total_cost, bill_breakdown, created_at) VALUES
(1, 1, 1000.00, 4.5, 'Miền Bắc', 2, 'Pin mặt trời 630W', 0.630, 2800000.00, 12, 33600000.00, 2.835, 7.56, 1, 'Inverter Luxpower', 6.00, 15000000.00, 1, 'Tủ điện', 6.00, 2000000.00, 0.00, '8cell', 1, 'Pin lưu trữ 8 cell', 8.30, 0, 15000000.00, 0.00, 12, 50000.00, 600000.00, 48, 10000.00, 480000.00, 24, 15000.00, 360000.00, 120, 20000.00, 2400000.00, 6000000.00, 3600000.00, 60200000.00, 60200000.00, '[]', '2025-10-28 00:04:38');

-- =====================================================
-- SURVEY_PRODUCT_CONFIGS: Khởi tạo trống (admin sẽ cấu hình lại)
-- =====================================================
-- Không chèn dữ liệu mặc định cho survey_product_configs

-- =====================================================
-- DỮ LIỆU MẪU - INTRO_POSTS (Bài viết trang giới thiệu)
-- =====================================================
INSERT INTO intro_posts (id, title, description, image_url, video_url, media_gallery, is_active, display_order, created_at, updated_at) VALUES
(1, 'HC Eco System - Giải Pháp Năng Lượng Xanh', 'HC Eco System là đơn vị hàng đầu trong lĩnh vực cung cấp giải pháp năng lượng mặt trời tại Việt Nam. Với kinh nghiệm nhiều năm trong ngành, chúng tôi tự hào là đối tác đáng tin cậy của hàng ngàn khách hàng trên toàn quốc.', '/uploads/intro_images/intro_image_1761695233_6901560173ebd.jpg', '/uploads/intro_videos/intro_video_1761695233_6901560173ee4.mp4', '[{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/intro_images/intro_690155f042c3a0.88497178_1761695216.jpg\",\"order\":1},{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/intro_images/intro_690155f380aa26.57052331_1761695219.jpg\",\"order\":2},{\"type\":\"video\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/intro_videos/intro_690155f6a8a480.00200483_1761695222.mp4\",\"order\":3},{\"type\":\"video\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/intro_videos/intro_690155f9d71b54.51943691_1761695225.mp4\",\"order\":4}]', 1, 6, '2025-10-27 11:31:09', '2025-10-28 23:47:13'),
(2, 'Sứ Mệnh Của Chúng Tôi', 'Chúng tôi cam kết cung cấp các giải pháp năng lượng mặt trời chất lượng cao, hiệu quả và tiết kiệm chi phí. Đội ngũ chuyên nghiệp, thiết bị chính hãng và dịch vụ tận tâm.', NULL, NULL, NULL, 1, 2, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(3, 'Tầm Nhìn 2030', 'Trở thành đơn vị dẫn đầu trong lĩnh vực năng lượng tái tạo tại Việt Nam, góp phần xây dựng một tương lai xanh, bền vững và thân thiện với môi trường.', NULL, NULL, NULL, 1, 3, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(4, 'Giải Pháp Cho Mọi Quy Mô', 'Từ hộ gia đình nhỏ với hệ thống 3kW đến doanh nghiệp lớn với công suất 500kW+, chúng tôi có giải pháp phù hợp cho mọi nhu cầu.', NULL, NULL, NULL, 1, 4, '2025-10-27 11:31:09', '2025-10-27 11:31:09'),
(5, 'dddddđ', 'ffffffffffffffffffffff', '/uploads/intro_images/intro_image_1761599252_68ffdf1432557.jpg', '/uploads/intro_videos/intro_video_1761600454_68ffe3c698f1a.mp4', NULL, 1, 1, '2025-10-27 21:07:32', '2025-10-27 21:27:34'),
(6, 'faaf', 'aaaaaaaaaa', '/uploads/intro_images/intro_image_1761600209_68ffe2d1bc014.jpg', '/uploads/intro_videos/intro_video_1761600190_68ffe2bee024b.mp4', NULL, 1, 5, '2025-10-27 21:23:10', '2025-10-27 21:23:29');

-- =====================================================
-- DỮ LIỆU MẪU - PROJECTS (Dự án)
-- =====================================================
INSERT INTO projects (id, title, description, image_url, video_url, media_gallery, is_active, display_order, created_at, updated_at) VALUES
(1, 'Lắp Đặt Hệ Thống 5kW Tại Gia Đình Sài Gòn', 'Hệ thống điện mặt trời 5kW cho gia đình tại Quận 7, TP. Hồ Chí Minh. Sử dụng công nghệ pin cao cấp từ Jinko Solar và inverter Growatt.', '/uploads/project_images/project_image_1761695201_690155e11d9a9.jpg', '/uploads/project_videos/project_video_1761695201_690155e11d9d2.mp4', '[{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_images/project_690155d4e56574.18828422_1761695188.jpg\",\"order\":1},{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_images/project_690155d7e077b7.91443021_1761695191.jpg\",\"order\":2},{\"type\":\"video\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_videos/project_690155dbf35db8.02372283_1761695195.mp4\",\"order\":3},{\"type\":\"video\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/project_videos/project_690155df797f00.16373158_1761695199.mp4\",\"order\":4}]', 1, 1, '2025-10-27 12:00:00', '2025-10-28 23:46:41'),
(2, 'Dự Án Điện Mặt Trời 10kW Cho Shop Coffee', 'Lắp đặt hệ thống 10kW cho cửa hàng coffee shop tại Đà Nẵng. Giúp giảm hơn 80% chi phí điện hàng tháng.', NULL, NULL, NULL, 1, 2, '2025-10-27 12:00:00', '2025-10-27 12:00:00'),
(3, 'Hệ Thống 20kW Cho Nhà Xưởng', 'Dự án lắp đặt 20kW trên mái nhà xưởng tại Bình Dương. Công suất lớn đáp ứng nhu cầu sản xuất và còn dư điện bán lưới.', NULL, NULL, NULL, 1, 3, '2025-10-27 12:00:00', '2025-10-27 12:00:00'),
(4, 'Hệ Thống Hybrid 15kW Có Pin Lưu Trữ', 'Dự án hệ thống hybrid 15kW kết hợp pin lưu trữ tại Vũng Tàu. Tự chủ năng lượng 24/7, không lo mất điện.', NULL, NULL, NULL, 1, 4, '2025-10-27 12:00:00', '2025-10-27 12:00:00');

-- =====================================================
-- DỮ LIỆU MẪU - DICH_VU
-- =====================================================
INSERT INTO dich_vu (id, name, logo_url, description, highlight_color, link_name, link_type, link_value, is_active, display_order, created_at, updated_at) VALUES
(1, 'Bảo Duy Solar', '../assets/img/ecosystem/baoduy-solar-logo.jpg', 'Chuyên cung cấp giải pháp năng lượng mặt trời toàn diện cho hộ gia đình và doanh nghiệp. Lắp đặt pin năng lượng mặt trời chất lượng cao, tiết kiệm điện năng tối đa với chính sách bảo hành lâu dài.', '#FBBF24', 'Xem bảng giá', 'page', 'pricing.html', 1, 1, NOW(), NOW()),
(2, 'HC Travel', '../assets/img/ecosystem/hc-travel-logo.jpg', 'Dịch vụ du lịch đặc biệt dành cho khách hàng lắp đặt năng lượng mặt trời. Tận hưởng những chuyến du lịch đáng nhớ với ưu đãi đặc quyền và trải nghiệm độc đáo.', '#60A5FA', 'Liên hệ ngay', 'page', 'lien-he.html', 1, 2, NOW(), NOW()),
(3, 'HC Coffee & Restaurant', '../assets/img/ecosystem/hc-cafe-logo.jpg', 'Nhà hàng và quán cà phê phục vụ những món ăn ngon, đồ uống chất lượng cao. Môi trường thân thiện, lý tưởng cho họp mặt, làm việc và thư giãn với bạn bè, gia đình.', '#F59E0B', 'Xem thực đơn', 'page', 'pricing.html', 1, 3, NOW(), NOW()),
(4, 'C Home Build', '../assets/img/ecosystem/c-home-logo.jpg', 'Dịch vụ xây dựng và thiết kế nhà ở hiện đại, bền vững với tiêu chuẩn cao. Tích hợp công nghệ xanh, tiết kiệm năng lượng trong từng công trình.', '#10B981', 'Xem website', 'custom', 'https://c-homebuild.com/', 1, 4, NOW(), NOW());

-- =====================================================
-- HOÀN THÀNH IMPORT DỮ LIỆU
-- =====================================================
SELECT 'Sample data imported successfully!' as message;
SELECT 'Packages: 4 packages, Services: 4 services' as info;
SELECT 'Reward Templates: 6 templates' as info;
-- =====================================================
-- DATA FOR HOME_POSTS (Bài đăng trang chủ)
-- =====================================================
INSERT INTO home_posts (id, title, description, highlight_text, highlight_color, image_url, image_position, button_text, button_url, button_color, features, media_gallery, display_order, is_active, section_id, created_at, updated_at) VALUES
(1, 'Xây Dựng Tổ Ấm', 'Gói 10 Tỷ', 'Giải Pháp Tối Ưu', '#2ef548', '/assets/img/home/home_1761695132_6901559cdc38e.jpg', 'left', 'Xem Bảng Giá', 'html/pricing.html', '#b6df20', '[{\"text\":\"Giá Tốt\"},{\"text\":\"Bảo Hành 10 năm\"},{\"text\":\"Yên Tâm Sử Dụng\"}]', '[]', 1, 1, 'solutions', '2025-10-28 19:53:28', '2025-10-28 23:45:32'),
(2, 'Du Lịch Trọn Gói', 'Hà Giang', 'Gói Tiết Kiệm', '#21c4b9', '/assets/img/home/home_1761695124_690155945bdeb.jpg', 'right', 'Xem Bảng Giá', 'html/pricing.html', '#35e34c', '[{\"text\":\"Giá Tốt\"},{\"text\":\"Bảo Hành 10 năm\"},{\"text\":\"Yên Tâm Sử Dụng\"}]', '[{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/home_images/home_690155b0a904c1.99610869_1761695152.jpg\",\"order\":1},{\"type\":\"image\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/home_images/home_690155b5b1c554.57140385_1761695157.jpg\",\"order\":2},{\"type\":\"video\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/home_videos/home_690155b9b1cdc6.79866448_1761695161.mp4\",\"order\":3},{\"type\":\"video\",\"url\":\"https://api.quangphuc.iotsinhvien.io.vn/uploads/home_videos/home_690155be022c21.54257669_1761695166.mp4\",\"order\":4}]', 1, 1, 'solutions', '2025-10-28 20:10:14', '2025-10-28 23:46:07');

-- =====================================================
-- DATA FOR CONTACT_CHANNELS
-- =====================================================
INSERT INTO contact_channels (id, name, description, content, category, color, display_order, is_active) VALUES
(1, 'Hotline', 'Hỗ trợ 24/7', '0969397434', 'phone', '#16a34a', 1, 1),
(2, 'Hotline phụ', 'Hỗ trợ 24/7', '0988919868', 'phone', '#16a34a', 2, 1),
(3, 'Zalo', 'Chat trực tuyến (Chính)', '0969397434', 'zalo', '#2563eb', 3, 1),
(4, 'Zalo phụ', 'Chat trực tuyến', '0988919868', 'zalo', '#2563eb', 4, 1),
(5, 'Email', 'Phản hồi trong 24h', 'hcecosystem@gmail.com', 'email', '#9333ea', 5, 1),
(6, 'Facebook', 'Theo dõi tin tức', 'https://www.facebook.com/hceco.io.vn', 'facebook', '#1d4ed8', 6, 1),
(7, 'TikTok', 'Video về năng lượng', '@hc.channal', 'tiktok', '#ec4899', 7, 1),
(8, 'Website', 'Mã số thuế: 0123456789', 'https://hcecosystem.vn', 'website', '#4b5563', 8, 1);

-- =====================================================
-- DATA FOR ELECTRICITY_PRICES (Bảng giá điện EVN)
-- =====================================================
INSERT INTO electricity_prices (id, tier, tier_name, kwh_from, kwh_to, price_no_vat, price_with_vat, effective_date, is_active, notes) VALUES
(1, 1, 'Bậc 1: 0-50 kWh', 0, 50, 1984.00, 2143.00, '2025-05-10', 1, 'Bậc tiêu thụ thấp nhất'),
(2, 2, 'Bậc 2: 51-100 kWh', 51, 100, 2050.00, 2214.00, '2025-05-10', 1, 'Bậc tiêu thụ trung bình thấp'),
(3, 3, 'Bậc 3: 101-200 kWh', 101, 200, 2380.00, 2570.00, '2025-05-10', 1, 'Bậc tiêu thụ trung bình'),
(4, 4, 'Bậc 4: 201-300 kWh', 201, 300, 2930.00, 3164.00, '2025-05-10', 1, 'Bậc tiêu thụ cao'),
(5, 5, 'Bậc 5: 301-400 kWh', 301, 400, 3270.00, 3532.00, '2025-05-10', 1, 'Bậc tiêu thụ rất cao'),
(6, 6, 'Bậc 6: Từ 401 kWh', 401, NULL, 3460.00, 3737.00, '2025-05-10', 1, 'Bậc tiêu thụ cao nhất (không giới hạn)');

-- =====================================================
-- DATA FOR SURVEY_REGIONS (Khu vực khảo sát)
-- =====================================================
INSERT INTO survey_regions (id, region_code, region_name, display_content, sun_hours, display_order, is_active, notes) VALUES
(1, 'mien-bac', 'Miền Bắc', 'Miền Bắc (4,4 giờ nắng/ngày)', 4.4, 1, 1, 'Khu vực phía Bắc Việt Nam'),
(2, 'mien-trung', 'Miền Trung', 'Miền Trung (6,3 giờ nắng/ngày)', 6.3, 2, 1, 'Khu vực miền Trung Việt Nam'),
(3, 'mien-nam', 'Miền Nam', 'Miền Nam (6,3 giờ nắng/ngày)', 6.3, 3, 1, 'Khu vực phía Nam Việt Nam');

SELECT 'Intro Posts: 6 posts' as info;
SELECT 'Projects: 4 projects' as info;
SELECT 'Home Posts: 2 posts' as info;
SELECT 'Contact Channels: 8 channels' as info;
SELECT 'Electricity Prices: 6 tiers' as info;
SELECT 'Survey Regions: 3 regions' as info;
SELECT 'Provinces: 61 provinces/cities' as info;
SELECT 'Districts: Sample districts for major cities' as info;
SELECT 'Test users created:' as info;
SELECT '  - testuser / 123456 (Regular user)' as info;
SELECT '  - admin / admin123 (Admin user)' as info;
SELECT '' as info;
SELECT 'Ready for HC Eco System!' as status;

