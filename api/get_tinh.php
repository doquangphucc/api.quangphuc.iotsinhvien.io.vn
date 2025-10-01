<?php
require_once 'connect.php';

try {
    $db = Database::getInstance();
    $tinh_data = $db->select('tinh');
    sendSuccess(['tinh' => $tinh_data]);
} catch (Exception $e) {
    error_log("Get Tinh error: " . $e->getMessage());
    sendError('Lá»—i há»‡ thá»‘ng, khÃ´ng thá»ƒ láº¥y danh sÃ¡ch tá»‰nh thÃ nh.', 500);
}
?>
