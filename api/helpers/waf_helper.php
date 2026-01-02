<?php
/**
 * Web Application Firewall (WAF) Helper
 * Provides protection against common web attacks
 */

/**
 * Get raw request body (safe to call multiple times)
 * This function caches the body so it can be read multiple times
 * @return string
 */
function getRawRequestBody(): string {
    global $__RAW_REQUEST_BODY;
    if (!isset($__RAW_REQUEST_BODY)) {
        $__RAW_REQUEST_BODY = file_get_contents('php://input');
    }
    return $__RAW_REQUEST_BODY ?? '';
}

class WAF {
    // Attack patterns
    private static $sqlInjectionPatterns = [
        '/(\%27)|(\')|(\-\-)|(\%23)|(#)/i',
        '/((\%3D)|(=))[^\n]*((\%27)|(\')|(\-\-)|(\%3B)|(;))/i',
        '/\w*((\%27)|(\'))((\%6F)|o|(\%4F))((\%72)|r|(\%52))/i',
        '/((\%27)|(\'))union/i',
        '/exec(\s|\+)+(s|x)p\w+/i',
        '/UNION(\s+)ALL(\s+)SELECT/i',
        '/UNION(\s+)SELECT/i',
        '/INSERT(\s+)INTO/i',
        '/DELETE(\s+)FROM/i',
        '/DROP(\s+)TABLE/i',
        '/DROP(\s+)DATABASE/i',
        '/TRUNCATE(\s+)TABLE/i',
        '/LOAD_FILE/i',
        '/INTO(\s+)OUTFILE/i',
        '/INTO(\s+)DUMPFILE/i',
        '/BENCHMARK\s*\(/i',
        '/SLEEP\s*\(/i',
        '/WAITFOR(\s+)DELAY/i',
    ];
    
    private static $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/on\w+\s*=/i',
        '/<\s*iframe/i',
        '/<\s*object/i',
        '/<\s*embed/i',
        '/<\s*applet/i',
        '/<\s*meta/i',
        '/<\s*style/i',
        '/<\s*form/i',
        '/<\s*input/i',
        '/<\s*body/i',
        '/expression\s*\(/i',
        '/url\s*\(/i',
        '/data\s*:/i',
    ];
    
    private static $pathTraversalPatterns = [
        '/\.\.\//i',
        '/\.\.\\\/i',
        '/%2e%2e%2f/i',
        '/%2e%2e\//i',
        '/\.%2e\//i',
        '/%2e\.\//i',
        '/\.\.%2f/i',
        '/%2e%2e%5c/i',
        '/etc\/passwd/i',
        '/etc\/shadow/i',
        '/proc\/self/i',
        '/var\/log/i',
    ];
    
    private static $commandInjectionPatterns = [
        '/;\s*\w+/i',
        '/\|\s*\w+/i',
        '/`[^`]+`/i',
        '/\$\([^)]+\)/i',
        '/&&\s*\w+/i',
        '/\|\|\s*\w+/i',
        '/>\s*\/\w+/i',
        '/<\s*\/\w+/i',
        '/\bnc\b.*\-e/i',
        '/\bwget\b/i',
        '/\bcurl\b.*\|/i',
        '/\bchmod\b/i',
        '/\brm\b.*\-rf/i',
    ];
    
    private static $badUserAgents = [
        'sqlmap',
        'nikto',
        'nmap',
        'masscan',
        'zgrab',
        'gobuster',
        'dirbuster',
        'wpscan',
        'havij',
        'acunetix',
        'netsparker',
        'burp',
        'owasp',
        'w3af',
        'arachni',
    ];
    
    // Blocked IPs cache file
    private static $blockedIPsFile = null;
    private static $blockDuration = 3600; // 1 hour
    
    /**
     * Initialize WAF
     */
    public static function init() {
        self::$blockedIPsFile = sys_get_temp_dir() . '/waf_blocked_ips.json';
    }
    
    /**
     * Main protection method - call this at the start of every request
     */
    public static function protect(): array {
        self::init();
        
        $result = [
            'blocked' => false,
            'reason' => null,
            'severity' => 'INFO'
        ];
        
        // Check if IP is blocked
        if (self::isIPBlocked()) {
            $result['blocked'] = true;
            $result['reason'] = 'IP temporarily blocked due to suspicious activity';
            $result['severity'] = 'CRITICAL';
            return $result;
        }
        
        // Check User-Agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (self::isBadUserAgent($userAgent)) {
            self::blockIP('Bad User-Agent: ' . $userAgent);
            $result['blocked'] = true;
            $result['reason'] = 'Suspicious User-Agent detected';
            $result['severity'] = 'WARNING';
            return $result;
        }
        
        // Check all input sources
        $inputs = [
            'GET' => $_GET,
            'POST' => $_POST,
            'COOKIE' => $_COOKIE,
        ];
        
        foreach ($inputs as $source => $data) {
            foreach ($data as $key => $value) {
                $checkResult = self::checkInput($value, $key);
                if ($checkResult['blocked']) {
                    self::logAttack($checkResult['reason'], $source, $key, $value);
                    $result = $checkResult;
                    return $result;
                }
            }
        }
        
        // Check request URI
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $uriResult = self::checkInput($uri, 'REQUEST_URI');
        if ($uriResult['blocked']) {
            self::logAttack($uriResult['reason'], 'URI', 'REQUEST_URI', $uri);
            return $uriResult;
        }
        
        // Check raw POST body for JSON requests
        // Store in global so other code can reuse it (php://input can only be read once)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $rawBody = getRawRequestBody();
            if ($rawBody) {
                $jsonResult = self::checkInput($rawBody, 'JSON_BODY');
                if ($jsonResult['blocked']) {
                    self::logAttack($jsonResult['reason'], 'BODY', 'JSON', $rawBody);
                    return $jsonResult;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Check a single input value for attacks
     */
    private static function checkInput($value, string $key = ''): array {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $result = self::checkInput($v, $k);
                if ($result['blocked']) {
                    return $result;
                }
            }
            return ['blocked' => false, 'reason' => null, 'severity' => 'INFO'];
        }
        
        if (!is_string($value)) {
            return ['blocked' => false, 'reason' => null, 'severity' => 'INFO'];
        }
        
        $value = urldecode($value);
        
        // Check SQL Injection
        foreach (self::$sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                self::incrementSuspiciousCount();
                return [
                    'blocked' => true,
                    'reason' => 'SQL Injection attempt detected',
                    'severity' => 'CRITICAL',
                    'pattern' => $pattern
                ];
            }
        }
        
        // Check XSS
        foreach (self::$xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                self::incrementSuspiciousCount();
                return [
                    'blocked' => true,
                    'reason' => 'XSS attempt detected',
                    'severity' => 'WARNING',
                    'pattern' => $pattern
                ];
            }
        }
        
        // Check Path Traversal
        foreach (self::$pathTraversalPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                self::incrementSuspiciousCount();
                return [
                    'blocked' => true,
                    'reason' => 'Path Traversal attempt detected',
                    'severity' => 'CRITICAL',
                    'pattern' => $pattern
                ];
            }
        }
        
        // Check Command Injection (only for certain fields)
        $dangerousFields = ['cmd', 'command', 'exec', 'shell', 'system', 'file', 'path', 'filename', 'url'];
        $shouldCheckCmd = false;
        foreach ($dangerousFields as $field) {
            if (stripos($key, $field) !== false) {
                $shouldCheckCmd = true;
                break;
            }
        }
        
        if ($shouldCheckCmd) {
            foreach (self::$commandInjectionPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    self::incrementSuspiciousCount();
                    return [
                        'blocked' => true,
                        'reason' => 'Command Injection attempt detected',
                        'severity' => 'CRITICAL',
                        'pattern' => $pattern
                    ];
                }
            }
        }
        
        return ['blocked' => false, 'reason' => null, 'severity' => 'INFO'];
    }
    
    /**
     * Check if User-Agent is from known attack tools
     */
    private static function isBadUserAgent(string $userAgent): bool {
        $userAgentLower = strtolower($userAgent);
        foreach (self::$badUserAgents as $bad) {
            if (strpos($userAgentLower, $bad) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP(): string {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Check if current IP is blocked
     */
    private static function isIPBlocked(): bool {
        $ip = self::getClientIP();
        $blockedIPs = self::getBlockedIPs();
        
        if (isset($blockedIPs[$ip])) {
            if ($blockedIPs[$ip]['expires'] > time()) {
                return true;
            } else {
                // Expired, remove from list
                unset($blockedIPs[$ip]);
                self::saveBlockedIPs($blockedIPs);
            }
        }
        
        return false;
    }
    
    /**
     * Block an IP address
     */
    public static function blockIP(string $reason, int $duration = null): void {
        $ip = self::getClientIP();
        $duration = $duration ?? self::$blockDuration;
        
        $blockedIPs = self::getBlockedIPs();
        $blockedIPs[$ip] = [
            'blocked_at' => time(),
            'expires' => time() + $duration,
            'reason' => $reason
        ];
        
        self::saveBlockedIPs($blockedIPs);
        self::logAttack('IP Blocked: ' . $reason, 'SYSTEM', 'IP', $ip);
    }
    
    /**
     * Unblock an IP address
     */
    public static function unblockIP(string $ip): bool {
        $blockedIPs = self::getBlockedIPs();
        if (isset($blockedIPs[$ip])) {
            unset($blockedIPs[$ip]);
            self::saveBlockedIPs($blockedIPs);
            return true;
        }
        return false;
    }
    
    /**
     * Get list of blocked IPs
     */
    private static function getBlockedIPs(): array {
        if (!file_exists(self::$blockedIPsFile)) {
            return [];
        }
        
        $content = file_get_contents(self::$blockedIPsFile);
        $data = json_decode($content, true);
        
        return is_array($data) ? $data : [];
    }
    
    /**
     * Save blocked IPs to file
     */
    private static function saveBlockedIPs(array $blockedIPs): void {
        file_put_contents(
            self::$blockedIPsFile, 
            json_encode($blockedIPs, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }
    
    /**
     * Increment suspicious activity count for IP
     */
    private static function incrementSuspiciousCount(): void {
        $ip = self::getClientIP();
        $countFile = sys_get_temp_dir() . '/waf_suspicious_' . md5($ip) . '.json';
        
        $data = ['count' => 0, 'first_seen' => time()];
        
        if (file_exists($countFile)) {
            $content = file_get_contents($countFile);
            $data = json_decode($content, true) ?: $data;
            
            // Reset if first seen is more than 1 hour ago
            if ($data['first_seen'] < time() - 3600) {
                $data = ['count' => 0, 'first_seen' => time()];
            }
        }
        
        $data['count']++;
        
        // Auto-block after 5 suspicious requests in 1 hour
        if ($data['count'] >= 5) {
            self::blockIP('Too many suspicious requests', 7200); // 2 hours block
        }
        
        file_put_contents($countFile, json_encode($data), LOCK_EX);
    }
    
    /**
     * Log attack attempt
     */
    private static function logAttack(string $reason, string $source, string $key, $value): void {
        $ip = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        
        $logMessage = sprintf(
            "[WAF ALERT] %s | IP: %s | Source: %s | Key: %s | URI: %s | UA: %s",
            $reason,
            $ip,
            $source,
            $key,
            $uri,
            substr($userAgent, 0, 100)
        );
        
        error_log($logMessage);
        
        // Also log to audit_logger if available
        if (file_exists(__DIR__ . '/audit_logger.php')) {
            require_once __DIR__ . '/audit_logger.php';
            AuditLogger::log(
                'SECURITY_ALERT',
                'waf',
                null,
                null,
                [
                    'reason' => $reason,
                    'source' => $source,
                    'key' => $key,
                    'ip' => $ip,
                    'uri' => $uri
                ],
                $reason,
                'CRITICAL'
            );
        }
    }
    
    /**
     * Send blocked response and exit
     */
    public static function block(string $reason = 'Access Denied'): void {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Access denied',
            'code' => 'WAF_BLOCKED'
        ]);
        exit;
    }
    
    /**
     * Sanitize input (removes dangerous content instead of blocking)
     */
    public static function sanitize($value) {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }
        
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove null bytes
        $value = str_replace(chr(0), '', $value);
        
        // Remove dangerous HTML tags
        $value = strip_tags($value, '<p><br><b><i><u><strong><em><ul><ol><li><a><span><div>');
        
        // Encode HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
    
    /**
     * Get blocked IPs list (for admin panel)
     */
    public static function getBlockedIPsList(): array {
        self::init();
        $blockedIPs = self::getBlockedIPs();
        
        // Clean expired entries
        $now = time();
        $active = [];
        foreach ($blockedIPs as $ip => $data) {
            if ($data['expires'] > $now) {
                $active[$ip] = $data;
                $active[$ip]['remaining'] = $data['expires'] - $now;
            }
        }
        
        return $active;
    }
}

/**
 * Quick function to apply WAF protection
 */
function waf_protect(): void {
    $result = WAF::protect();
    
    if ($result['blocked']) {
        WAF::block($result['reason']);
    }
}
