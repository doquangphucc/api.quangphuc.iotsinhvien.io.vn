<?php
// Simple MySQLi connection
require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    if (headers_sent()) {
        die("Database connection failed");
    }
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Không thể kết nối cơ sở dữ liệu'
    ]);
    exit();
}

// Set charset
$conn->set_charset("utf8mb4");
?>
