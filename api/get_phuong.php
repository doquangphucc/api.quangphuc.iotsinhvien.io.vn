<?php
require_once 'connect.php';

// Get id_tinh from query parameter
$id_tinh = isset($_GET['id_tinh']) ? (int)$_GET['id_tinh'] : 0;

if ($id_tinh <= 0) {
    // Return empty array if no id_tinh is provided, instead of an error
    sendSuccess(['phuong' => []]);
    exit;
}

try {
    $db = Database::getInstance();
    $phuong_data = $db->select('phuong', ['id_tinh' => $id_tinh]);
    sendSuccess(['phuong' => $phuong_data]);
} catch (Exception $e) {
    error_log("Get Phuong error: " . $e->getMessage());
    sendError('Lỗi hệ thống, không thể lấy danh sách phường xã.', 500);
}
?>