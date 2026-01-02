<?php
/**
 * CORS Helper - Dynamic CORS handling for any domain
 * This allows the API to work with ANY domain it's deployed on
 */

function setupCORS() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    // Get current domain dynamically
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    $currentScheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $currentDomain = $currentScheme . '://' . $currentHost;
    
    // Build allowed origins dynamically
    $allowedOrigins = [
        'http://localhost',
        'http://127.0.0.1',
        $currentDomain,
        str_replace('http://', 'https://', $currentDomain),
        str_replace('https://', 'http://', $currentDomain),
    ];
    
    // Check if origin is allowed
    $originAllowed = false;
    if (!empty($origin)) {
        foreach ($allowedOrigins as $allowedOrigin) {
            if (!empty($allowedOrigin) && strpos($origin, $allowedOrigin) === 0) {
                $originAllowed = true;
                break;
            }
        }
    }
    
    if ($originAllowed) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    } else if (empty($origin)) {
        // Same-origin requests
        header('Access-Control-Allow-Credentials: true');
    } else {
        // Allow other origins without credentials
        header('Access-Control-Allow-Origin: *');
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

/**
 * Get the base URL of current server dynamically
 */
function getBaseUrl() {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

/**
 * Handle preflight OPTIONS request
 */
function handlePreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
