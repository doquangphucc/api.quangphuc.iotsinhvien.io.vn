<?php
// Test API with auth_helpers.php
require_once 'connect.php';
require_once 'auth_helpers.php';

// Mock session for testing
session_start();
$_SESSION['user_id'] = 1; // Use first user

try {
    requireAuth();
    $userId = getCurrentUserId();
    
    sendSuccess(['user_id' => $userId], 'Auth test successful');
    
} catch (Exception $e) {
    sendError('Auth test failed: ' . $e->getMessage(), 500);
}
?>
