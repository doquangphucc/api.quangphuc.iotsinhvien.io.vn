<?php
// Test session và authentication
require_once 'connect.php';

echo "=== SESSION TEST ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";

echo "\n=== AUTH TEST ===\n";
echo "isLoggedIn(): " . (isLoggedIn() ? 'TRUE' : 'FALSE') . "\n";
echo "getCurrentUserId(): " . getCurrentUserId() . "\n";

echo "\n=== COOKIE TEST ===\n";
echo "PHPSESSID cookie: " . ($_COOKIE['PHPSESSID'] ?? 'NOT SET') . "\n";
echo "All cookies: " . print_r($_COOKIE, true) . "\n";

echo "\n=== HEADERS TEST ===\n";
echo "Content-Type: " . (header('Content-Type: application/json') ? 'SET' : 'NOT SET') . "\n";
?>
