<?php
// Debug session for admin
require_once __DIR__ . '/../connect.php';

header('Content-Type: application/json');

// Output all session data
echo json_encode([
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'headers' => getallheaders(),
    'server' => [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
        'HTTP_ORIGIN' => $_SERVER['HTTP_ORIGIN'] ?? '',
        'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? ''
    ]
], JSON_PRETTY_PRINT);

