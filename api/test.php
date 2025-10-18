<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simple test without any dependencies
echo json_encode([
    'success' => true,
    'message' => 'API is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION
]);
?>
