<?php
// Minimal test API - no dependencies
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Minimal API test successful',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
