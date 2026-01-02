<?php
/**
 * Audit Logger Helper
 * Logs all admin actions for security auditing and compliance
 */

class AuditLogger {
    private static $pdo = null;
    private static $tableName = 'audit_logs';
    
    // Action types
    const ACTION_CREATE = 'CREATE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_LOGIN = 'LOGIN';
    const ACTION_LOGOUT = 'LOGOUT';
    const ACTION_LOGIN_FAILED = 'LOGIN_FAILED';
    const ACTION_PERMISSION_CHANGE = 'PERMISSION_CHANGE';
    const ACTION_SETTINGS_CHANGE = 'SETTINGS_CHANGE';
    const ACTION_EXPORT = 'EXPORT';
    const ACTION_UPLOAD = 'UPLOAD';
    const ACTION_APPROVE = 'APPROVE';
    const ACTION_REJECT = 'REJECT';
    
    // Severity levels
    const SEVERITY_INFO = 'INFO';
    const SEVERITY_WARNING = 'WARNING';
    const SEVERITY_CRITICAL = 'CRITICAL';
    
    /**
     * Initialize database connection
     */
    private static function init() {
        if (self::$pdo === null) {
            try {
                require_once __DIR__ . '/../config.php';
                self::$pdo = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                self::createTableIfNotExists();
            } catch (PDOException $e) {
                error_log('AuditLogger DB Error: ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }
    
    /**
     * Create audit_logs table if it doesn't exist
     */
    private static function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS " . self::$tableName . " (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            username VARCHAR(100) NULL,
            action VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50) NULL,
            entity_id VARCHAR(100) NULL,
            old_value JSON NULL,
            new_value JSON NULL,
            description TEXT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            severity ENUM('INFO', 'WARNING', 'CRITICAL') DEFAULT 'INFO',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created_at (created_at),
            INDEX idx_severity (severity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        self::$pdo->exec($sql);
    }
    
    /**
     * Log an action
     * 
     * @param string $action - Action type (use class constants)
     * @param string|null $entityType - Type of entity (e.g., 'product', 'user', 'order')
     * @param string|null $entityId - ID of the entity
     * @param mixed $oldValue - Previous value (will be JSON encoded)
     * @param mixed $newValue - New value (will be JSON encoded)
     * @param string|null $description - Human-readable description
     * @param string $severity - Severity level
     * @return bool
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?string $entityId = null,
        $oldValue = null,
        $newValue = null,
        ?string $description = null,
        string $severity = self::SEVERITY_INFO
    ): bool {
        if (!self::init()) {
            return false;
        }
        
        try {
            // Get user info from session
            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['fullname'] ?? $_SESSION['email'] ?? 'Anonymous';
            
            // Get request info
            $ipAddress = self::getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            // Prepare values
            $oldValueJson = $oldValue !== null ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null;
            $newValueJson = $newValue !== null ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null;
            
            // Insert log
            $sql = "INSERT INTO " . self::$tableName . " 
                    (user_id, username, action, entity_type, entity_id, old_value, new_value, description, ip_address, user_agent, severity)
                    VALUES (:user_id, :username, :action, :entity_type, :entity_id, :old_value, :new_value, :description, :ip_address, :user_agent, :severity)";
            
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':username' => $username,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':old_value' => $oldValueJson,
                ':new_value' => $newValueJson,
                ':description' => $description,
                ':ip_address' => $ipAddress,
                ':user_agent' => substr($userAgent, 0, 500),
                ':severity' => $severity
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log('AuditLogger Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Quick log methods for common actions
     */
    public static function logCreate(string $entityType, string $entityId, $data = null, ?string $description = null) {
        return self::log(self::ACTION_CREATE, $entityType, $entityId, null, $data, $description ?? "Tạo mới $entityType #$entityId");
    }
    
    public static function logUpdate(string $entityType, string $entityId, $oldData = null, $newData = null, ?string $description = null) {
        return self::log(self::ACTION_UPDATE, $entityType, $entityId, $oldData, $newData, $description ?? "Cập nhật $entityType #$entityId");
    }
    
    public static function logDelete(string $entityType, string $entityId, $data = null, ?string $description = null) {
        return self::log(self::ACTION_DELETE, $entityType, $entityId, $data, null, $description ?? "Xóa $entityType #$entityId", self::SEVERITY_WARNING);
    }
    
    public static function logLogin(bool $success, ?string $email = null, ?string $reason = null) {
        $action = $success ? self::ACTION_LOGIN : self::ACTION_LOGIN_FAILED;
        $severity = $success ? self::SEVERITY_INFO : self::SEVERITY_WARNING;
        $description = $success ? "Đăng nhập thành công: $email" : "Đăng nhập thất bại: $email - $reason";
        return self::log($action, 'user', null, null, ['email' => $email], $description, $severity);
    }
    
    public static function logLogout() {
        return self::log(self::ACTION_LOGOUT, 'user', null, null, null, 'Đăng xuất');
    }
    
    public static function logPermissionChange(string $userId, $oldPermissions, $newPermissions) {
        return self::log(
            self::ACTION_PERMISSION_CHANGE, 
            'user_permissions', 
            $userId, 
            $oldPermissions, 
            $newPermissions, 
            "Thay đổi quyền user #$userId",
            self::SEVERITY_WARNING
        );
    }
    
    public static function logUpload(string $entityType, string $filename, ?string $description = null) {
        return self::log(self::ACTION_UPLOAD, $entityType, null, null, ['filename' => $filename], $description ?? "Upload file: $filename");
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIP(): string {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (X-Forwarded-For)
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
     * Get audit logs with filtering
     * 
     * @param array $filters - ['user_id', 'action', 'entity_type', 'severity', 'from_date', 'to_date']
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getLogs(array $filters = [], int $limit = 50, int $offset = 0): array {
        if (!self::init()) {
            return [];
        }
        
        try {
            $where = ['1=1'];
            $params = [];
            
            if (!empty($filters['user_id'])) {
                $where[] = 'user_id = :user_id';
                $params[':user_id'] = $filters['user_id'];
            }
            
            if (!empty($filters['action'])) {
                $where[] = 'action = :action';
                $params[':action'] = $filters['action'];
            }
            
            if (!empty($filters['entity_type'])) {
                $where[] = 'entity_type = :entity_type';
                $params[':entity_type'] = $filters['entity_type'];
            }
            
            if (!empty($filters['severity'])) {
                $where[] = 'severity = :severity';
                $params[':severity'] = $filters['severity'];
            }
            
            if (!empty($filters['from_date'])) {
                $where[] = 'created_at >= :from_date';
                $params[':from_date'] = $filters['from_date'];
            }
            
            if (!empty($filters['to_date'])) {
                $where[] = 'created_at <= :to_date';
                $params[':to_date'] = $filters['to_date'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = '(description LIKE :search OR username LIKE :search2)';
                $params[':search'] = '%' . $filters['search'] . '%';
                $params[':search2'] = '%' . $filters['search'] . '%';
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM " . self::$tableName . " WHERE $whereClause";
            $countStmt = self::$pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Get logs
            $sql = "SELECT * FROM " . self::$tableName . " WHERE $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = self::$pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON values
            foreach ($logs as &$log) {
                if ($log['old_value']) {
                    $log['old_value'] = json_decode($log['old_value'], true);
                }
                if ($log['new_value']) {
                    $log['new_value'] = json_decode($log['new_value'], true);
                }
            }
            
            return [
                'total' => (int)$total,
                'logs' => $logs
            ];
        } catch (PDOException $e) {
            error_log('AuditLogger getLogs Error: ' . $e->getMessage());
            return ['total' => 0, 'logs' => []];
        }
    }
    
    /**
     * Clean old logs (retention policy)
     * 
     * @param int $daysToKeep - Number of days to keep logs (default 90)
     * @return int - Number of deleted records
     */
    public static function cleanOldLogs(int $daysToKeep = 90): int {
        if (!self::init()) {
            return 0;
        }
        
        try {
            $sql = "DELETE FROM " . self::$tableName . " WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([':days' => $daysToKeep]);
            
            $deleted = $stmt->rowCount();
            
            if ($deleted > 0) {
                self::log(
                    self::ACTION_DELETE,
                    'audit_logs',
                    null,
                    null,
                    ['deleted_count' => $deleted, 'retention_days' => $daysToKeep],
                    "Xóa $deleted log cũ (giữ lại $daysToKeep ngày)",
                    self::SEVERITY_INFO
                );
            }
            
            return $deleted;
        } catch (PDOException $e) {
            error_log('AuditLogger cleanOldLogs Error: ' . $e->getMessage());
            return 0;
        }
    }
}
