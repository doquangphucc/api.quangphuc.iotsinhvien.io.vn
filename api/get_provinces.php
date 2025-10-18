<?php
require_once 'connect.php';

try {
    $db = Database::getInstance();
    
    $sql = "SELECT id, ten_tinh FROM tinh ORDER BY ten_tinh";
    $stmt = $db->query($sql);
    $provinces = $stmt->fetchAll();
    
    sendSuccess(['provinces' => $provinces]);
    
} catch (Exception $e) {
    error_log("Get provinces error: " . $e->getMessage());
    sendError('Không thể lấy danh sách tỉnh/thành: ' . $e->getMessage(), 500);
}
?>
