<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
    
    // Required fields
    $username = trim($input['username'] ?? '');
    $content = trim($input['content'] ?? '');
    
    // Optional fields với giá trị mặc định
    $description = trim($input['description'] ?? '');
    $category = $input['category'] ?? '';
    $priority = $input['priority'] ?? 'medium';
    $price = $input['price'] ?? null;
    $currency = $input['currency'] ?? 'VND';
    $productUrl = trim($input['product_url'] ?? '');
    $purchaseStatus = $input['purchase_status'] ?? 'researching';
    $targetDate = $input['target_date'] ?? null;
    
    // Validation
    if (empty($username)) {
        throw new Exception('Username is required');
    }
    
    if (empty($content)) {
        throw new Exception('Content is required');
    }
    
    // Validate priority
    $validPriorities = ['low', 'medium', 'high'];
    if (!in_array($priority, $validPriorities)) {
        $priority = 'medium';
    }
    
    // Validate category
    $validCategories = ['work', 'study', 'personal', 'health', 'hobby', 'family', 'other', ''];
    if (!in_array($category, $validCategories)) {
        $category = '';
    }
    
    // Validate currency
    $validCurrencies = ['VND', 'USD', 'EUR'];
    if (!in_array($currency, $validCurrencies)) {
        $currency = 'VND';
    }
    
    // Validate purchase status
    $validStatuses = ['researching', 'saving', 'ready_to_buy'];
    if (!in_array($purchaseStatus, $validStatuses)) {
        $purchaseStatus = 'researching';
    }
    
    // Validate price
    if ($price !== null) {
        $price = floatval($price);
        if ($price < 0) {
            throw new Exception('Price cannot be negative');
        }
    }
    
    // Validate date format
    if ($targetDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetDate)) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }
    
    // Validate URL if provided
    if ($productUrl && !filter_var($productUrl, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid product URL format');
    }
    
    // Generate unique item_id
    $itemId = 'wish_' . date('Ymd') . '_' . uniqid();
    
    // Get user_id from username
    $userQuery = "SELECT id FROM tai_khoan WHERE user = ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$username]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    $userId = $user['id'];
    
    // Insert wish
    $query = "INSERT INTO wishes 
              (item_id, title, description, category, priority, price, currency, product_url, purchase_status, user_id, target_date, is_completed, created_at, updated_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $itemId,
        $content,
        $description ?: null,
        $category ?: null,
        $priority,
        $price,
        $currency,
        $productUrl ?: null,
        $purchaseStatus,
        $userId,
        $targetDate
    ]);
    
    if ($result) {
        $wishId = $pdo->lastInsertId();
        
        // Format price for display
        $formattedPrice = null;
        if ($price) {
            $currencySymbols = ['VND' => 'đ', 'USD' => '$', 'EUR' => '€'];
            $symbol = $currencySymbols[$currency] ?? 'đ';
            $formattedPrice = number_format($price) . ' ' . $symbol;
        }
        
        // Return created wish info
        echo json_encode([
            'success' => true,
            'message' => 'Wish created successfully',
            'data' => [
                'id' => $wishId,
                'item_id' => $itemId,
                'title' => $content,
                'description' => $description ?: null,
                'category' => $category ?: null,
                'priority' => $priority,
                'price' => $price,
                'currency' => $currency,
                'formatted_price' => $formattedPrice,
                'product_url' => $productUrl ?: null,
                'purchase_status' => $purchaseStatus,
                'target_date' => $targetDate,
                'is_completed' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Failed to create wish');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'WISH_CREATE_ERROR'
    ]);
}
?>
