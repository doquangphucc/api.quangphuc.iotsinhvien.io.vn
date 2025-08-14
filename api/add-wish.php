<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';
require_once 'connect.php';

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
    $datetime = $input['datetime'] ?? null;
    $targetDate = null;

    // Parse datetime if provided
    if ($datetime) {
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
              (item_id, title, description, category, priority, price, currency, product_url, purchase_status, user_id, target_date, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
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

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'input' => $input ?? null,
            'error_details' => $e->getTraceAsString()
        ]
    ]);
}
?>
