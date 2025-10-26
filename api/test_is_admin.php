<?php
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/db_mysqli.php';
require_once __DIR__ . '/auth_helpers.php';

echo json_encode([
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'user_id_isset' => isset($_SESSION['user_id']),
    'is_admin_result' => is_admin(),
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);

