<?php
// Cấu hình kết nối MySQL
const DB_HOST = 'localhost';
const DB_USER = 'dongthoigiancanhan';
const DB_PASS = 'D645RzafcmX8Mtbt';
const DB_NAME = 'dongthoigian';
const DB_CHARSET = 'utf8mb4';

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
    }
    return $conn;
}

function json_response($data, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    exit;
}
?>
