<?php
// Test API with connect.php only
require_once 'connect.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    sendSuccess(['message' => 'Connect.php test successful'], 'Database connection OK');
    
} catch (Exception $e) {
    sendError('Connect.php test failed: ' . $e->getMessage(), 500);
}
?>
