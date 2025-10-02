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
    monthly_kwh DECIMAL(10, 2) NOT NULL COMMENT 'Điện tiêu thụ hàng tháng (kWh)',
    sun_hours DECIMAL(3, 1) NOT NULL COMMENT 'Giờ nắng trung bình/ngày',
    panels_needed INT NOT NULL COMMENT 'Số tấm pin cần thiết',
    panel_cost DECIMAL(15, 2) NOT NULL COMMENT 'Tổng tiền tấm pin',
    inverter_id INT NOT NULL COMMENT 'ID biến tần được chọn',
    inverter_name VARCHAR(255) NOT NULL,
    inverter_price DECIMAL(15, 2) NOT NULL,
    cabinet_id INT NOT NULL COMMENT 'ID tủ điện được chọn',
    cabinet_name VARCHAR(255) NOT NULL,
    cabinet_price DECIMAL(15, 2) NOT NULL,
    battery_needed DECIMAL(10, 2) NOT NULL COMMENT 'Dung lượng pin cần (kWh)',
    battery_type VARCHAR(50) NOT NULL COMMENT '8cell or 16cell',
    battery_quantity INT NOT NULL,
    battery_cost DECIMAL(15, 2) NOT NULL,
    accessories_cost DECIMAL(15, 2) NOT NULL COMMENT 'Tổng phụ kiện',
    labor_cost DECIMAL(15, 2) NOT NULL COMMENT 'Công thợ',
    dc_cable_cost DECIMAL(15, 2) NOT NULL COMMENT 'Dây DC',
    total_cost DECIMAL(15, 2) NOT NULL COMMENT 'Tổng chi phí dự án',
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
