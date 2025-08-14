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
    status TINYINT(1) DEFAULT 0 COMMENT '0 = pending, 1 = completed',
    user_id INT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_item_id (item_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
);

-- Create wishes table for tracking wish completion status  
CREATE TABLE wishes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    status TINYINT(1) DEFAULT 0 COMMENT '0 = pending, 1 = completed/purchased',
    user_id INT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_item_id (item_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES tai_khoan(id) ON DELETE SET NULL
);