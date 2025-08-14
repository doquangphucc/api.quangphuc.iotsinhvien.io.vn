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