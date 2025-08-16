<?php
// Cấu hình kết nối MySQL
const DB_HOST = 'localhost';
const DB_USER = 'dongthoigiancanhan';
const DB_PASS = 'D645RzafcmX8Mtbt';
const DB_NAME = 'dongthoigian';
const DB_CHARSET = 'utf8mb4';

// Đặt timezone cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

function db_get_connection(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Kết nối thất bại: '.$conn->connect_error]);
            exit;
        }
        $conn->set_charset(DB_CHARSET);
        
        // Đặt timezone cho MySQL connection - sử dụng offset thay vì named timezone
        $conn->query("SET time_zone = '+07:00'");
        $conn->query("SET SESSION sql_mode = 'TRADITIONAL'");
    }
    return $conn;
}

function db_get_pdo_connection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00', sql_mode = 'TRADITIONAL'"
            ]);
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'PDO connection failed: '.$e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

function json_response($data, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    exit;
}
?>
