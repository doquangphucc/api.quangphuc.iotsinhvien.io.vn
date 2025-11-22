<?php
// Include config first
require_once 'config.php';

// Start session before any output
require_once 'session.php';

// Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set content type and CORS headers
header('Content-Type: application/json; charset=utf-8');

// Get the origin of the request
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// List of allowed origins
$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    'https://hceco.io.vn',
    'http://hceco.io.vn',
    'https://api.quangphuc.iotsinhvien.io.vn',
    'http://api.quangphuc.iotsinhvien.io.vn'
];

// Check if the origin is allowed or matches the allowed pattern
$originAllowed = false;
foreach ($allowedOrigins as $allowedOrigin) {
    if (strpos($origin, $allowedOrigin) === 0) {
        $originAllowed = true;
        break;
    }
}

if ($originAllowed) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else if (empty($origin)) {
    // Same-origin requests (no CORS needed)
    header('Access-Control-Allow-Credentials: true');
} else {
    // For other origins, allow without credentials
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include config (removed from below since it's now at the top)
// require_once 'config.php';

class Database {
    private $connection;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Không thể kết nối cơ sở dữ liệu'
            ]);
            exit();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function insert($table, $data) {
        $fields = array_keys($data);
        $fieldList = implode(',', $fields);
        $paramList = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO {$table} ({$fieldList}) VALUES ({$paramList})";
        
        try {
            $stmt = $this->query($sql, $data);
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update($table, $data, $conditions) {
        $dataFields = [];
        foreach ($data as $key => $value) {
            $dataFields[] = "{$key} = :data_{$key}";
        }
        $dataList = implode(', ', $dataFields);

        $whereClause = [];
        foreach ($conditions as $key => $value) {
            $whereClause[] = "{$key} = :cond_{$key}";
        }
        $whereList = implode(' AND ', $whereClause);

        $sql = "UPDATE {$table} SET {$dataList} WHERE {$whereList}";

        $params = [];
        foreach ($data as $key => $value) {
            $params[":data_{$key}"] = $value;
        }
        foreach ($conditions as $key => $value) {
            $params[":cond_{$key}"] = $value;
        }

        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Update failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function select($table, $conditions = [], $fields = '*', $orderBy = '') {
        $sql = "SELECT {$fields} FROM {$table}";
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = :{$field}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }

        if (!empty($orderBy)) {
            $sql .= " " . $orderBy;
        }
        
        try {
            $stmt = $this->query($sql, $conditions);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Select failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function selectOne($table, $conditions = [], $fields = '*') {
        $sql = "SELECT {$fields} FROM {$table}";
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $field => $value) {
                $whereClause[] = "{$field} = :{$field}";
            }
            $sql .= " WHERE " . implode(' AND ', $whereClause);
        }
        
        $sql .= " LIMIT 1";
        
        try {
            $stmt = $this->query($sql, $conditions);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("SelectOne failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete($table, $conditions = []) {
        if (empty($conditions)) {
            throw new Exception("Delete operation requires conditions to prevent accidental full table deletion");
        }
        
        $whereClause = [];
        foreach ($conditions as $field => $value) {
            $whereClause[] = "{$field} = :{$field}";
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);
        
        try {
            $stmt = $this->query($sql, $conditions);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Delete failed: " . $e->getMessage());
            throw $e;
        }
    }
}

// Utility functions
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function sendError($message, $statusCode = 400) {
    sendJsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

function sendSuccess($data = [], $message = '') {
    $response = ['success' => true];
    if (!empty($message)) {
        $response['message'] = $message;
    }
    if (!empty($data)) {
        $response['data'] = $data;
    }
    sendJsonResponse($response);
}

function validateRequired($data, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field])) {
            $missing[] = $field;
        } else {
            // Handle different types: string, array, etc.
            $value = $data[$field];
            if (is_string($value)) {
                // For strings, check if empty after trimming
                if (trim($value) === '') {
                    $missing[] = $field;
                }
            } elseif (is_array($value)) {
                // For arrays, check if empty
                if (empty($value)) {
                    $missing[] = $field;
                }
            } else {
                // For other types (int, bool, etc.), just check if set
                // They are considered valid if set
            }
        }
    }
    return $missing;
}

function sanitizeInput($input) {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}
?>
