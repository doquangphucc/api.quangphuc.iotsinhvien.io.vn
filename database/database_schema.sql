-- =====================================================
-- HC ECO SYSTEM - DATABASE SCHEMA
-- File: database_schema.sql
-- Description: Tạo cấu trúc bảng cho hệ thống HC Eco
-- Usage: Import file này TRƯỚC để tạo các bảng
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
-- 2. BẢNG PRODUCT_CATEGORIES (Danh mục sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    logo_url VARCHAR(500),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 3. BẢNG PRODUCTS (Sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL COMMENT 'Tiêu đề/Tên sản phẩm',
    market_price DECIMAL(15, 2) NOT NULL COMMENT 'Giá thị trường',
    category_price DECIMAL(15, 2) DEFAULT NULL COMMENT 'Giá theo danh mục',
    technical_description TEXT COMMENT 'Mô tả kỹ thuật (gộp mô tả ngắn và thông số kỹ thuật)',
    image_url VARCHAR(500) COMMENT 'Đường dẫn ảnh sản phẩm',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Trạng thái hiển thị',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE CASCADE
);

-- =====================================================
-- 3.1. BẢNG SURVEY_PRODUCT_CONFIGS (Cấu hình sản phẩm cho khảo sát)
-- =====================================================
CREATE TABLE IF NOT EXISTS survey_product_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL COMMENT 'ID sản phẩm',
    survey_category ENUM('solar_panel', 'inverter', 'battery', 'electrical_cabinet', 'accessory') NOT NULL COMMENT 'Loại sản phẩm trong khảo sát',
    phase_type ENUM('1_phase', '3_phase', 'both', 'none') DEFAULT 'none' COMMENT 'Loại pha (chỉ dùng cho inverter)',
    price_type ENUM('market_price', 'category_price') DEFAULT 'market_price' COMMENT 'Loại giá sử dụng',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Có hiển thị trong khảo sát',
    display_order INT DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    notes TEXT COMMENT 'Ghi chú',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_survey (product_id)
) COMMENT='Cấu hình sản phẩm cho trang khảo sát';

-- =====================================================
-- 4. BẢNG PACKAGE_CATEGORIES (Danh mục gói)
-- =====================================================
CREATE TABLE IF NOT EXISTS package_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    logo_url VARCHAR(500) COMMENT 'URL logo của danh mục',
    badge_text VARCHAR(50) DEFAULT NULL COMMENT 'Văn bản badge (VD: PHỔ BIẾN, HOT, ƯU ĐÃI)',
    badge_color VARCHAR(50) DEFAULT 'blue' COMMENT 'Màu badge (blue, green, red, yellow, purple, orange)',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 5. BẢNG PACKAGES (Gói sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15, 2) NOT NULL,
    savings_per_month VARCHAR(100) COMMENT 'Deprecated: Use highlights field instead',
    payback_period VARCHAR(100) COMMENT 'Deprecated: Use highlights field instead',
    highlights TEXT COMMENT 'JSON array of highlights: [{"title":"...", "content":"..."}]',
    badge_text VARCHAR(100),
    badge_color VARCHAR(50) DEFAULT 'green',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES package_categories(id) ON DELETE CASCADE
);

-- =====================================================
-- 6. BẢNG PACKAGE_ITEMS (Chi tiết gói sản phẩm)
-- =====================================================
CREATE TABLE IF NOT EXISTS package_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_description TEXT,
    display_order INT DEFAULT 0,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);

-- =====================================================
-- 7. BẢNG TINH (Tỉnh/Thành phố)
-- =====================================================
CREATE TABLE IF NOT EXISTS tinh (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_tinh VARCHAR(255) NOT NULL UNIQUE
);

-- =====================================================
-- 8. BẢNG PHUONG (Phường/Xã)
-- =====================================================
CREATE TABLE IF NOT EXISTS phuong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_phuong VARCHAR(255) NOT NULL,
    id_tinh INT NOT NULL,
    FOREIGN KEY (id_tinh) REFERENCES tinh(id)
);

-- =====================================================
-- 9. BẢNG VOUCHERS (Mã giảm giá)
-- =====================================================
CREATE TABLE IF NOT EXISTS vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    is_used BOOLEAN DEFAULT FALSE,
    used_by_user_id INT DEFAULT NULL,
    used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (used_by_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- 10. BẢNG ORDERS (Đơn hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    city VARCHAR(255) NOT NULL COMMENT 'Tỉnh/Thành phố',
    district VARCHAR(255) NOT NULL COMMENT 'Quận/Huyện',
    ward VARCHAR(255) DEFAULT NULL COMMENT 'Phường/Xã',
    address VARCHAR(500) NOT NULL COMMENT 'Địa chỉ chi tiết',
    notes TEXT,
    subtotal DECIMAL(15, 2) NOT NULL COMMENT 'Tổng tiền trước giảm giá',
    voucher_code VARCHAR(50) DEFAULT NULL,
    discount_amount DECIMAL(15, 2) DEFAULT 0,
    total_amount DECIMAL(15, 2) NOT NULL COMMENT 'Tổng tiền sau giảm giá',
    order_status ENUM('pending', 'approved', 'processing', 'shipping', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    approved_by INT DEFAULT NULL COMMENT 'Admin ID duyệt đơn',
    approved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- 11. BẢNG ORDER_ITEMS (Chi tiết đơn hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    image_url VARCHAR(500),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =====================================================
-- 12. BẢNG CART_ITEMS (Giỏ hàng)
-- =====================================================
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, product_id)
);

-- =====================================================
-- 13. BẢNG LOTTERY_TICKETS (Vé quay may mắn)
-- =====================================================
CREATE TABLE IF NOT EXISTS lottery_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT DEFAULT NULL,
    ticket_type ENUM('purchase', 'bonus', 'promotion') DEFAULT 'purchase',
    status ENUM('active', 'used', 'expired') DEFAULT 'active',
    pre_assigned_reward_id INT DEFAULT NULL COMMENT 'Phần thưởng được admin set trước',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- =====================================================
-- 14. BẢNG REWARD_TEMPLATES (Mẫu phần thưởng do admin tạo)
-- =====================================================
CREATE TABLE IF NOT EXISTS reward_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reward_name VARCHAR(255) NOT NULL,
    reward_type ENUM('voucher', 'cash', 'gift') NOT NULL,
    reward_value DECIMAL(15, 2) DEFAULT NULL COMMENT 'Giá trị voucher/tiền mặt',
    reward_description TEXT COMMENT 'Mô tả chi tiết quà tặng',
    reward_quantity INT DEFAULT NULL COMMENT 'Số lượng (dùng cho quà tặng)',
    reward_image VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- 15. BẢNG LOTTERY_REWARDS (Phần thưởng vòng quay)
-- =====================================================
CREATE TABLE IF NOT EXISTS lottery_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reward_template_id INT DEFAULT NULL COMMENT 'Mẫu phần thưởng từ admin',
    reward_name VARCHAR(255) NOT NULL,
    reward_type ENUM('voucher', 'cash', 'gift') NOT NULL,
    reward_value DECIMAL(15, 2) DEFAULT NULL COMMENT 'Giá trị voucher/tiền mặt',
    reward_description TEXT COMMENT 'Mô tả chi tiết',
    voucher_code VARCHAR(50) DEFAULT NULL COMMENT 'Mã voucher nếu là loại voucher',
    reward_image VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'used', 'expired') DEFAULT 'pending',
    ticket_id INT DEFAULT NULL COMMENT 'ID của vé số đã sử dụng',
    won_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_template_id) REFERENCES reward_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (ticket_id) REFERENCES lottery_tickets(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_won_at (won_at),
    INDEX idx_voucher_code (voucher_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. BẢNG SOLAR_SURVEYS (Khảo sát điện mặt trời)
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
-- 17. BẢNG SURVEY_RESULTS (Kết quả khảo sát)
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
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_is_active ON products(is_active);
CREATE INDEX idx_surveys_user_id ON solar_surveys(user_id);
CREATE INDEX idx_surveys_created_at ON solar_surveys(created_at);
CREATE INDEX idx_survey_results_survey_id ON survey_results(survey_id);

-- =====================================================
-- COMMENTS CHO CÁC BẢNG
-- =====================================================
ALTER TABLE solar_surveys COMMENT = 'Lưu thông tin khảo sát nhu cầu lắp đặt điện mặt trời';
ALTER TABLE survey_results COMMENT = 'Lưu kết quả tính toán chi tiết từ khảo sát';
ALTER TABLE lottery_rewards COMMENT = 'Lưu phần thưởng từ vòng quay may mắn';
ALTER TABLE lottery_tickets COMMENT = 'Lưu vé quay may mắn của người dùng';

-- =====================================================
-- THÊM TRƯỜNG is_admin VÀO BẢNG USERS
-- =====================================================
ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT FALSE AFTER password;

-- =====================================================
-- INDEXES CHO CÁC BẢNG MỚI
-- =====================================================
CREATE INDEX idx_product_categories_active ON product_categories(is_active);
CREATE INDEX idx_packages_category ON packages(category_id);
CREATE INDEX idx_packages_active ON packages(is_active);
CREATE INDEX idx_orders_status ON orders(order_status);
CREATE INDEX idx_orders_approved ON orders(approved_by);
CREATE INDEX idx_vouchers_code ON vouchers(code);
CREATE INDEX idx_vouchers_used ON vouchers(is_used);
CREATE INDEX idx_reward_templates_type ON reward_templates(reward_type);
CREATE INDEX idx_lottery_tickets_reward ON lottery_tickets(pre_assigned_reward_id);

-- =====================================================
-- 18. BẢNG INTRO_POSTS (Bài viết trang giới thiệu)
-- =====================================================
CREATE TABLE IF NOT EXISTS intro_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL COMMENT 'Tiêu đề bài viết',
    description TEXT COMMENT 'Mô tả/ nội dung bài viết',
    image_url VARCHAR(500) COMMENT 'URL ảnh đại diện',
    video_url VARCHAR(500) COMMENT 'URL video (tùy chọn)',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Trạng thái hiển thị',
    display_order INT DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_intro_posts_active ON intro_posts(is_active);
CREATE INDEX idx_intro_posts_display_order ON intro_posts(display_order);

-- =====================================================
-- 19. BẢNG PROJECTS (Dự án)
-- =====================================================
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(500) NOT NULL COMMENT 'Tiêu đề dự án',
    description TEXT COMMENT 'Mô tả dự án',
    image_url VARCHAR(500) COMMENT 'URL ảnh dự án',
    video_url VARCHAR(500) COMMENT 'URL video dự án (tùy chọn)',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Trạng thái hiển thị',
    display_order INT DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_projects_active ON projects(is_active);
CREATE INDEX idx_projects_display_order ON projects(display_order);

-- =====================================================
-- 20. BẢNG DICH_VU (Dịch vụ hệ sinh thái)
-- =====================================================
CREATE TABLE IF NOT EXISTS dich_vu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Tên dịch vụ',
    logo_url VARCHAR(500) COMMENT 'URL logo/ảnh dịch vụ',
    description TEXT COMMENT 'Mô tả dịch vụ',
    highlight_color VARCHAR(50) DEFAULT '#3FA34D' COMMENT 'Màu nổi bật (hex color)',
    link_name VARCHAR(100) COMMENT 'Tên link hiển thị (ví dụ: "Xem bảng giá")',
    link_type ENUM('page', 'custom') DEFAULT 'page' COMMENT 'Loại link: page hoặc custom',
    link_value VARCHAR(500) COMMENT 'Giá trị link (tên trang hoặc URL)',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Trạng thái hiển thị',
    display_order INT DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_dich_vu_active ON dich_vu(is_active);
CREATE INDEX idx_dich_vu_display_order ON dich_vu(display_order);

-- =====================================================
-- 21. BẢNG USER_PERMISSIONS (Phân quyền user)
-- =====================================================
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID người dùng',
    permission_key VARCHAR(50) NOT NULL COMMENT 'Khóa quyền (categories, products, survey, packages, orders, tickets, rewards, intro-posts, projects, dich-vu)',
    can_view BOOLEAN DEFAULT FALSE COMMENT 'Quyền xem',
    can_create BOOLEAN DEFAULT FALSE COMMENT 'Quyền tạo mới',
    can_edit BOOLEAN DEFAULT FALSE COMMENT 'Quyền sửa',
    can_delete BOOLEAN DEFAULT FALSE COMMENT 'Quyền xóa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_permission (user_id, permission_key)
) COMMENT='Lưu quyền truy cập các module của user';

CREATE INDEX idx_user_permissions_user ON user_permissions(user_id);
CREATE INDEX idx_user_permissions_key ON user_permissions(permission_key);

-- =====================================================
-- HOÀN THÀNH TẠO BẢNG
-- =====================================================
SELECT 'Database schema created successfully!' as message;
SELECT 'Total tables created: 21' as info;
SELECT 'Next: Import database_data.sql to insert sample data' as next_step;

