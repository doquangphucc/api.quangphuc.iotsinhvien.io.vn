<?php
/**
 * Get CSRF Token API
 * Returns a CSRF token for use in forms and AJAX requests
 */
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/helpers/csrf_helper.php';

// Allow GET and OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendError('Method not allowed', 405);
}

// Generate/get CSRF token
$token = getCSRFToken();

sendSuccess([
    'csrf_token' => $token
], 'Token generated successfully');
