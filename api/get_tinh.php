<?php
require_once 'connect.php';

try {
    $db = Database::getInstance();
    $tinh_data = $db->select('tinh');
    sendSuccess(['tinh' => $tinh_data]);
} catch (Exception $e) {
    error_log("Get Tinh error: " . $e->getMessage());
    sendError('Lỗi hệ thống, không thể lấy danh sách tỉnh thành.', 500);
}
?>