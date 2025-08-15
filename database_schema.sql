-- Create tai_khoan table
CREATE TABLE tai_khoan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(15)
);

-- Create muon_lam table
CREATE TABLE muon_lam (
    id INT PRIMARY KEY AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    content TEXT
);

-- Create muon_mua table
CREATE TABLE muon_mua (
    id INT PRIMARY KEY AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    content TEXT
);

-- Create tasks table for tracking task completion status
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL COMMENT 'Mô tả chi tiết công việc',
    category VARCHAR(50) DEFAULT NULL COMMENT 'Danh mục: Học tập, Công việc, Cá nhân, Sức khỏe, etc',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium' COMMENT 'Độ ưu tiên',
    status TINYINT(1) DEFAULT 0 COMMENT '0 = pending, 1 = completed',
    user_id INT DEFAULT NULL,
    scheduled_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến thực hiện (do user chọn)',
    scheduled_time TIME DEFAULT NULL COMMENT 'Giờ dự kiến thực hiện (do user chọn)', 
    created_at DATETIME NOT NULL COMMENT 'Thời gian tạo record (chỉ set 1 lần)',
    updated_at DATETIME NOT NULL COMMENT 'Thời gian cập nhật lần cuối (cùng lúc với created_at khi tạo mới)',
    completed_at DATETIME DEFAULT NULL COMMENT 'Thời gian hoàn thành thực tế',
    INDEX idx_item_id (item_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_scheduled_date (scheduled_date),
    FOREIGN KEY (user_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
);

-- Create wishes table for tracking wish completion status  
CREATE TABLE wishes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL COMMENT 'Mô tả chi tiết sản phẩm',
    category VARCHAR(50) DEFAULT NULL COMMENT 'Danh mục: Điện tử, Thời trang, Gia dụng, Sách, etc',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium' COMMENT 'Độ ưu tiên mua',
    price DECIMAL(15,2) DEFAULT NULL COMMENT 'Giá tiền dự kiến',
    currency VARCHAR(3) DEFAULT 'VND' COMMENT 'Đơn vị tiền tệ',
    product_url TEXT DEFAULT NULL COMMENT 'Link sản phẩm',
    status TINYINT(1) DEFAULT 0 COMMENT '0 = pending, 1 = completed/purchased',
    purchase_status ENUM('researching', 'saving', 'ready_to_buy', 'purchased') DEFAULT 'researching' COMMENT 'Trạng thái mua sắm',
    user_id INT DEFAULT NULL,
    target_date DATE DEFAULT NULL COMMENT 'Ngày dự kiến mua (do user chọn)',
    created_at DATETIME NOT NULL COMMENT 'Thời gian tạo record (chỉ set 1 lần)',
    updated_at DATETIME NOT NULL COMMENT 'Thời gian cập nhật lần cuối (cùng lúc với created_at khi tạo mới)',
    purchased_at DATETIME DEFAULT NULL COMMENT 'Thời gian mua thực tế',
    actual_price DECIMAL(15,2) DEFAULT NULL COMMENT 'Giá mua thực tế',
    purchase_note TEXT DEFAULT NULL COMMENT 'Ghi chú khi mua',
    INDEX idx_item_id (item_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_purchase_status (purchase_status),
    INDEX idx_price (price),
    INDEX idx_target_date (target_date),
    FOREIGN KEY (user_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
);