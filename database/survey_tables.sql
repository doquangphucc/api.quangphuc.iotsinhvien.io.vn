USE nangluongmattroi;

-- Bảng lưu thông tin khảo sát điện mặt trời
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

-- Bảng lưu kết quả tính toán chi tiết của khảo sát
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

-- Index để tăng performance
CREATE INDEX idx_surveys_user_id ON solar_surveys(user_id);
CREATE INDEX idx_surveys_created_at ON solar_surveys(created_at);
CREATE INDEX idx_survey_results_survey_id ON survey_results(survey_id);

-- Thêm comment cho bảng
ALTER TABLE solar_surveys COMMENT = 'Lưu thông tin khảo sát nhu cầu lắp đặt điện mặt trời';
ALTER TABLE survey_results COMMENT = 'Lưu kết quả tính toán chi tiết từ khảo sát';
