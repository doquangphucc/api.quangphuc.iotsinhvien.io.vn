<?php
// Debug script for add_to_cart session issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'session.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://api.quangphuc.iotsinhvien.io.vn');
header('Access-Control-Allow-Credentials: true');

$debug = [
    'session_id' => session_id(),
    'session_name' => session_name(),
    'session_data' => $_SESSION ?? [],
    'cookies' => $_COOKIE ?? [],
    'is_logged_in' => isLoggedIn(),
    'current_user_id' => isLoggedIn() ? getCurrentUserId() : null,
    'headers' => [
        'Cookie' => $_SERVER['HTTP_COOKIE'] ?? 'No cookie header',
        'Origin' => $_SERVER['HTTP_ORIGIN'] ?? 'No origin',
        'Referer' => $_SERVER['HTTP_REFERER'] ?? 'No referer'
    ]
];

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

