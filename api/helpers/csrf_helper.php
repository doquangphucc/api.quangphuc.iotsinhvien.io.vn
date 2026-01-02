<?php
/**
 * CSRF Protection Helper
 * Generates and validates CSRF tokens for forms and API requests
 */

class CSRFProtection {
    const TOKEN_NAME = 'csrf_token';
    const TOKEN_LENGTH = 32;
    const TOKEN_LIFETIME = 3600; // 1 hour
    
    /**
     * Generate a new CSRF token
     * @return string
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = [
            'token' => $token,
            'expires' => time() + self::TOKEN_LIFETIME
        ];
        
        return $token;
    }
    
    /**
     * Get current token or generate new one
     * @return string
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists and is still valid
        if (isset($_SESSION[self::TOKEN_NAME]) && 
            isset($_SESSION[self::TOKEN_NAME]['expires']) &&
            $_SESSION[self::TOKEN_NAME]['expires'] > time()) {
            return $_SESSION[self::TOKEN_NAME]['token'];
        }
        
        // Generate new token
        return self::generateToken();
    }
    
    /**
     * Validate CSRF token
     * @param string $token - Token to validate
     * @return bool
     */
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($token) || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        
        $sessionToken = $_SESSION[self::TOKEN_NAME];
        
        // Check expiration
        if (!isset($sessionToken['expires']) || $sessionToken['expires'] < time()) {
            unset($_SESSION[self::TOKEN_NAME]);
            return false;
        }
        
        // Constant-time comparison to prevent timing attacks
        return hash_equals($sessionToken['token'], $token);
    }
    
    /**
     * Get token from request (header or body)
     * @return string|null
     */
    public static function getTokenFromRequest() {
        // Check header first (X-CSRF-Token)
        $headers = getallheaders();
        if (isset($headers['X-CSRF-Token'])) {
            return $headers['X-CSRF-Token'];
        }
        if (isset($headers['X-Csrf-Token'])) {
            return $headers['X-Csrf-Token'];
        }
        
        // Check POST data
        if (isset($_POST[self::TOKEN_NAME])) {
            return $_POST[self::TOKEN_NAME];
        }
        
        // Check JSON body
        $input = file_get_contents('php://input');
        if ($input) {
            $data = json_decode($input, true);
            if (isset($data[self::TOKEN_NAME])) {
                return $data[self::TOKEN_NAME];
            }
        }
        
        return null;
    }
    
    /**
     * Verify CSRF token from request
     * Exits with 403 if invalid
     * @param bool $softFail - If true, returns false instead of exiting
     * @return bool
     */
    public static function verify(bool $softFail = false): bool {
        $token = self::getTokenFromRequest();
        
        if (!self::validateToken($token)) {
            if ($softFail) {
                return false;
            }
            
            // Log the failed attempt
            if (file_exists(__DIR__ . '/audit_logger.php')) {
                require_once __DIR__ . '/audit_logger.php';
                AuditLogger::log(
                    'CSRF_FAILED',
                    'security',
                    null,
                    null,
                    [
                        'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
                    ],
                    'CSRF validation failed',
                    'WARNING'
                );
            }
            
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'CSRF token không hợp lệ hoặc đã hết hạn',
                'code' => 'CSRF_INVALID'
            ]);
            exit;
        }
        
        return true;
    }
    
    /**
     * Regenerate token (call after successful form submission)
     */
    public static function regenerate() {
        return self::generateToken();
    }
    
    /**
     * Get HTML hidden input for forms
     * @return string
     */
    public static function getHiddenInput() {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Get meta tag for JavaScript access
     * @return string
     */
    public static function getMetaTag() {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Check if current request is a safe method that doesn't need CSRF
     * @return bool
     */
    public static function isSafeMethod(): bool {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        return in_array($method, ['GET', 'HEAD', 'OPTIONS']);
    }
    
    /**
     * Verify CSRF only for state-changing methods (POST, PUT, PATCH, DELETE)
     * @param bool $softFail
     * @return bool
     */
    public static function verifyIfNeeded(bool $softFail = false): bool {
        if (self::isSafeMethod()) {
            return true;
        }
        return self::verify($softFail);
    }
}

/**
 * Quick CSRF check function
 * Use at the beginning of state-changing API endpoints (POST, PUT, DELETE)
 */
function verifyCSRF() {
    CSRFProtection::verify();
}

/**
 * Verify CSRF only for POST/PUT/DELETE methods
 */
function verifyCSRFIfNeeded() {
    CSRFProtection::verifyIfNeeded();
}

/**
 * Get CSRF token for API responses
 */
function getCSRFToken() {
    return CSRFProtection::getToken();
}
