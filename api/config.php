<?php
/**
 * Application Configuration
 * SECURITY: Credentials are loaded from .env file
 * If .env doesn't exist, fallback to defaults (for development only)
 */

// Load .env file if exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set as environment variable if not already set
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Helper function to get env with fallback
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Database configuration - loaded from .env
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'nangluongmattroi'));
define('DB_USER', env('DB_USER', 'nangluongmattroi'));
define('DB_PASS', env('DB_PASS', '')); // No default password for security

// Notification email settings
define('ORDER_NOTIFICATION_EMAIL', env('ORDER_NOTIFICATION_EMAIL', ''));
define('ORDER_NOTIFICATION_FORM_SUBMIT', env('FORMSUBMIT_URL', ''));

// Business info (used in emails, footers, etc.)
define('SITE_NAME', env('SITE_NAME', 'HC Eco System'));
define('SITE_EMAIL', env('SITE_EMAIL', 'noreply@example.com'));
define('SITE_WEBSITE', env('SITE_WEBSITE', 'example.com'));
