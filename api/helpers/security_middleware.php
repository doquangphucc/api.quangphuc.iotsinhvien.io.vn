<?php
/**
 * Security Middleware
 * Integrates all security helpers into one easy-to-use middleware
 * Call this at the beginning of API endpoints for full protection
 */

// Load all security helpers
require_once __DIR__ . '/security_headers.php';
require_once __DIR__ . '/waf_helper.php';
require_once __DIR__ . '/csrf_helper.php';
require_once __DIR__ . '/rate_limiter.php';

/**
 * Apply full security middleware
 * 
 * @param array $options Configuration options:
 *   - 'csrf' => bool (default: true for POST/PUT/DELETE)
 *   - 'waf' => bool (default: true)
 *   - 'rate_limit' => bool (default: true)
 *   - 'rate_limit_requests' => int (default: 60)
 *   - 'rate_limit_window' => int (default: 60 seconds)
 *   - 'headers' => bool (default: true)
 *   - 'audit' => bool (default: false) - Log all requests
 */
function applySecurityMiddleware(array $options = []): void {
    // Default options
    $defaults = [
        'csrf' => true,
        'waf' => true,
        'rate_limit' => true,
        'rate_limit_requests' => 60,
        'rate_limit_window' => 60,
        'headers' => true,
        'audit' => false
    ];
    
    $options = array_merge($defaults, $options);
    
    // 1. Apply security headers
    if ($options['headers']) {
        setAllSecurityHeaders();
    }
    
    // 2. WAF protection
    if ($options['waf']) {
        $wafResult = WAF::protect();
        if ($wafResult['blocked']) {
            WAF::block($wafResult['reason']);
        }
    }
    
    // 3. Rate limiting
    if ($options['rate_limit']) {
        $endpoint = $_SERVER['REQUEST_URI'] ?? '/';
        $rateLimiter = new RateLimiter($endpoint, $options['rate_limit_requests'], $options['rate_limit_window']);
        
        if (!$rateLimiter->check()) {
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: ' . $options['rate_limit_window']);
            echo json_encode([
                'success' => false,
                'message' => 'Quá nhiều yêu cầu. Vui lòng thử lại sau.',
                'code' => 'RATE_LIMITED',
                'retry_after' => $options['rate_limit_window']
            ]);
            exit;
        }
    }
    
    // 4. CSRF protection (only for state-changing methods)
    if ($options['csrf'] && !CSRFProtection::isSafeMethod()) {
        CSRFProtection::verify();
    }
    
    // 5. Audit logging (optional)
    if ($options['audit']) {
        if (file_exists(__DIR__ . '/audit_logger.php')) {
            require_once __DIR__ . '/audit_logger.php';
            $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
            $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
            AuditLogger::log(
                'API_REQUEST',
                'api',
                null,
                null,
                [
                    'method' => $method,
                    'uri' => $uri,
                    'query' => $_GET,
                    'has_body' => !empty($_POST) || !empty(getRawRequestBody())
                ],
                "$method $uri",
                'INFO'
            );
        }
    }
}

/**
 * Apply security for public GET endpoints (no CSRF needed)
 */
function applyPublicGetSecurity(): void {
    applySecurityMiddleware([
        'csrf' => false,
        'rate_limit_requests' => 100,
        'rate_limit_window' => 60
    ]);
}

/**
 * Apply security for authenticated POST/PUT/DELETE endpoints
 */
function applyAuthenticatedSecurity(): void {
    applySecurityMiddleware([
        'csrf' => true,
        'rate_limit_requests' => 30,
        'rate_limit_window' => 60
    ]);
}

/**
 * Apply security for login/register endpoints (stricter rate limiting)
 */
function applyAuthEndpointSecurity(): void {
    applySecurityMiddleware([
        'csrf' => false, // Login forms get token separately
        'rate_limit_requests' => 5,
        'rate_limit_window' => 300, // 5 requests per 5 minutes
        'audit' => true
    ]);
}

/**
 * Apply security for admin endpoints
 */
function applyAdminSecurity(): void {
    applySecurityMiddleware([
        'csrf' => true,
        'rate_limit_requests' => 60,
        'rate_limit_window' => 60,
        'audit' => true // Always audit admin actions
    ]);
}

/**
 * Quick security check for specific threats
 */
class SecurityCheck {
    /**
     * Check if request appears to be from a bot/scanner
     */
    public static function isBot(): bool {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $botPatterns = [
            'bot', 'spider', 'crawler', 'scraper',
            'curl', 'wget', 'python', 'java', 'perl',
            'nikto', 'sqlmap', 'nmap', 'masscan'
        ];
        
        $uaLower = strtolower($userAgent);
        foreach ($botPatterns as $pattern) {
            if (strpos($uaLower, $pattern) !== false) {
                return true;
            }
        }
        
        return empty($userAgent);
    }
    
    /**
     * Check if request is from a known proxy/VPN
     */
    public static function isProxy(): bool {
        $proxyHeaders = [
            'HTTP_VIA',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_X_FORWARDED_HOST',
            'HTTP_X_FORWARDED_PROTO',
            'HTTP_CLIENT_IP',
            'HTTP_CF_CONNECTING_IP' // Cloudflare (legitimate)
        ];
        
        foreach ($proxyHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                // Allow Cloudflare
                if ($header === 'HTTP_CF_CONNECTING_IP') {
                    continue;
                }
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Validate that referrer is from allowed domains
     */
    public static function validateReferrer(array $allowedDomains = []): bool {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if (empty($referrer)) {
            return true; // No referrer is OK (direct access)
        }
        
        $parsedUrl = parse_url($referrer);
        $host = $parsedUrl['host'] ?? '';
        
        if (empty($allowedDomains)) {
            // Default allowed domains
            $allowedDomains = [
                'localhost',
                '127.0.0.1',
                'quangphuc.iotsinhvien.io.vn',
                'api.quangphuc.iotsinhvien.io.vn',
                'hceco.io.vn',
                'www.hceco.io.vn'
            ];
        }
        
        foreach ($allowedDomains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get security score for current request (0-100)
     */
    public static function getRequestSecurityScore(): int {
        $score = 100;
        
        // Deduct for missing security indicators
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $score -= 20;
        }
        
        if (self::isBot()) {
            $score -= 30;
        }
        
        if (self::isProxy()) {
            $score -= 10;
        }
        
        if (!self::validateReferrer()) {
            $score -= 15;
        }
        
        // Check for suspicious patterns in request
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $suspiciousPatterns = [
            '/\.\./',           // Path traversal
            '/select.*from/i',  // SQL
            '/<script/i',       // XSS
            '/union.*select/i', // SQL injection
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                $score -= 25;
            }
        }
        
        return max(0, $score);
    }
}
