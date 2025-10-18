<?php
require_once 'connect.php';

$cityId = $_GET['city_id'] ?? 0;

if ($cityId <= 0) {
    sendError('ID tỉnh/thành không hợp lệ');
}

try {
    $db = Database::getInstance();
    
    $sql = "SELECT id, ten_phuong FROM phuong WHERE id_tinh = ? ORDER BY ten_phuong";
    $stmt = $db->query($sql, [$cityId]);
    $districts = $stmt->fetchAll();
    
    sendSuccess(['districts' => $districts]);
    
} catch (Exception $e) {
    error_log("Get districts error: " . $e->getMessage());
    sendError('Không thể lấy danh sách phường/xã: ' . $e->getMessage(), 500);
}
?>
