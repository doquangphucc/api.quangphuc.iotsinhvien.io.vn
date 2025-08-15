<?php
// Đặt timezone cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $username = $input['username'] ?? '';
    $title = $input['title'] ?? '';
    $description = $input['description'] ?? null;
    $category = $input['category'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    $price = $input['price'] ?? null;
    $currency = $input['currency'] ?? 'VND';
    $productUrl = $input['product_url'] ?? null;
    $purchaseStatus = $input['purchase_status'] ?? 'researching';
    
    // Handle both old datetime format and new target_date
    $targetDate = $input['target_date'] ?? null;
    
    // Legacy datetime support
    $datetime = $input['datetime'] ?? null;
    if ($datetime && !$targetDate) {
        $dt = new DateTime($datetime);
        $targetDate = $dt->format('Y-m-d');
    }

    if (empty($username) || empty($title)) {
        throw new Exception('Username and title are required');
    }

    // Generate unique item_id
    $itemId = 'wish_' . uniqid() . '_' . time();
    
    // Get user_id from username
    $userQuery = "SELECT id FROM tai_khoan WHERE user = ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$username]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $userId = $user ? $user['id'] : null;

    $query = "INSERT INTO wishes 
              (item_id, title, description, category, priority, price, currency, product_url, purchase_status, user_id, target_date, status, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $itemId,
        $title,
        $description,
        $category,
        $priority,
        $price,
        $currency,
        $productUrl,
        $purchaseStatus,
        $userId,
        $targetDate
    ]);

    if ($result) {
        $wishId = $pdo->lastInsertId();
        
        // Lấy thông tin wish vừa tạo
        $getWishQuery = "SELECT * FROM wishes WHERE id = ?";
        $getWishStmt = $pdo->prepare($getWishQuery);
        $getWishStmt->execute([$wishId]);
        $newWish = $getWishStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Wish added successfully',
            'data' => $newWish
        ]);
    } else {
        throw new Exception('Failed to add wish');
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_type' => 'PDO_ERROR',
        'debug' => [
            'input' => $input ?? null,
            'sql_error' => $e->errorInfo ?? null
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'GENERAL_ERROR',
        'debug' => [
            'input' => $input ?? null,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
