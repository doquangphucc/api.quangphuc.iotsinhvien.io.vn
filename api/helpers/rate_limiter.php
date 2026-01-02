<?php
/**
 * Rate Limiting Helper
 * Prevents brute force attacks by limiting request frequency
 */

class RateLimiter {
    private $conn;
    private $tableName = 'rate_limits';
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->ensureTableExists();
    }
    
    /**
     * Create rate_limits table if not exists
     */
    private function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS `rate_limits` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `identifier` VARCHAR(255) NOT NULL COMMENT 'IP address or user identifier',
            `action` VARCHAR(50) NOT NULL COMMENT 'Action being rate limited (login, register, api)',
            `attempts` INT DEFAULT 1,
            `first_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `blocked_until` TIMESTAMP NULL,
            UNIQUE KEY `unique_identifier_action` (`identifier`, `action`),
            KEY `idx_blocked_until` (`blocked_until`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->conn->query($sql);
    }
    
    /**
     * Check if request is allowed
     * @param string $identifier - IP or user ID
     * @param string $action - Action type (login, register, api)
     * @param int $maxAttempts - Max attempts allowed
     * @param int $windowSeconds - Time window in seconds
     * @param int $blockSeconds - Block duration when limit exceeded
     * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int]
     */
    public function check($identifier, $action, $maxAttempts = 5, $windowSeconds = 300, $blockSeconds = 900) {
        // Clean old records first
        $this->cleanup($windowSeconds * 2);
        
        // Check if currently blocked
        $stmt = $this->conn->prepare(
            "SELECT attempts, first_attempt, blocked_until 
             FROM rate_limits 
             WHERE identifier = ? AND action = ?"
        );
        $stmt->bind_param("ss", $identifier, $action);
        $stmt->execute();
        $result = $stmt->get_result();
        $record = $result->fetch_assoc();
        $stmt->close();
        
        $now = time();
        
        if ($record) {
            // Check if blocked
            if ($record['blocked_until'] && strtotime($record['blocked_until']) > $now) {
                $retryAfter = strtotime($record['blocked_until']) - $now;
                return [
                    'allowed' => false,
                    'remaining' => 0,
                    'retry_after' => $retryAfter,
                    'message' => "Quá nhiều yêu cầu. Vui lòng thử lại sau " . ceil($retryAfter / 60) . " phút."
                ];
            }
            
            // Check if within time window
            $firstAttempt = strtotime($record['first_attempt']);
            if (($now - $firstAttempt) > $windowSeconds) {
                // Reset counter - window expired
                $this->resetCounter($identifier, $action);
                return ['allowed' => true, 'remaining' => $maxAttempts - 1, 'retry_after' => 0];
            }
            
            // Check attempt count
            if ($record['attempts'] >= $maxAttempts) {
                // Block the user
                $blockedUntil = date('Y-m-d H:i:s', $now + $blockSeconds);
                $this->block($identifier, $action, $blockedUntil);
                return [
                    'allowed' => false,
                    'remaining' => 0,
                    'retry_after' => $blockSeconds,
                    'message' => "Quá nhiều yêu cầu. Vui lòng thử lại sau " . ceil($blockSeconds / 60) . " phút."
                ];
            }
            
            return [
                'allowed' => true,
                'remaining' => $maxAttempts - $record['attempts'],
                'retry_after' => 0
            ];
        }
        
        return ['allowed' => true, 'remaining' => $maxAttempts, 'retry_after' => 0];
    }
    
    /**
     * Record an attempt
     */
    public function recordAttempt($identifier, $action) {
        $stmt = $this->conn->prepare(
            "INSERT INTO rate_limits (identifier, action, attempts, first_attempt) 
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE 
             attempts = attempts + 1,
             last_attempt = NOW()"
        );
        $stmt->bind_param("ss", $identifier, $action);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Reset counter after successful action (e.g., successful login)
     */
    public function resetCounter($identifier, $action) {
        $stmt = $this->conn->prepare(
            "DELETE FROM rate_limits WHERE identifier = ? AND action = ?"
        );
        $stmt->bind_param("ss", $identifier, $action);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Block identifier for specified duration
     */
    private function block($identifier, $action, $blockedUntil) {
        $stmt = $this->conn->prepare(
            "UPDATE rate_limits SET blocked_until = ? WHERE identifier = ? AND action = ?"
        );
        $stmt->bind_param("sss", $blockedUntil, $identifier, $action);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Clean up old records
     */
    private function cleanup($olderThanSeconds) {
        $threshold = date('Y-m-d H:i:s', time() - $olderThanSeconds);
        $stmt = $this->conn->prepare(
            "DELETE FROM rate_limits WHERE last_attempt < ? AND (blocked_until IS NULL OR blocked_until < NOW())"
        );
        $stmt->bind_param("s", $threshold);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}

/**
 * Quick rate limit check function
 * @param mysqli $conn - Database connection
 * @param string $action - Action type
 * @param int $maxAttempts - Max attempts (default: 5)
 * @param int $windowSeconds - Time window (default: 5 minutes)
 * @return bool - True if allowed, exits with error if not
 */
function checkRateLimit($conn, $action, $maxAttempts = 5, $windowSeconds = 300) {
    $limiter = new RateLimiter($conn);
    $ip = RateLimiter::getClientIP();
    $result = $limiter->check($ip, $action, $maxAttempts, $windowSeconds);
    
    if (!$result['allowed']) {
        http_response_code(429);
        header('Retry-After: ' . $result['retry_after']);
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
            'retry_after' => $result['retry_after']
        ]);
        exit;
    }
    
    // Record this attempt
    $limiter->recordAttempt($ip, $action);
    
    return true;
}

/**
 * Reset rate limit after successful action
 */
function resetRateLimit($conn, $action) {
    $limiter = new RateLimiter($conn);
    $ip = RateLimiter::getClientIP();
    $limiter->resetCounter($ip, $action);
}
