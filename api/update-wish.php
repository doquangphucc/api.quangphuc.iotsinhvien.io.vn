<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';

// Get PDO connection
$pdo = db_get_pdo_connection();

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only PUT/POST method allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $wishId = $input['id'] ?? '';
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

    if (empty($wishId) || empty($title)) {
        throw new Exception('Wish ID and title are required');
    }

    // Check if wish exists
    $checkQuery = "SELECT id FROM wishes WHERE id = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$wishId]);
    
    if (!$checkStmt->fetch()) {
        throw new Exception('Wish not found');
    }

    // Update wish
    $query = "UPDATE wishes 
              SET title = ?, description = ?, category = ?, priority = ?, 
                  price = ?, currency = ?, product_url = ?, purchase_status = ?, 
                  target_date = ?, updated_at = NOW()
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        $title,
        $description,
        $category,
        $priority,
        $price,
        $currency,
        $productUrl,
        $purchaseStatus,
        $targetDate,
        $wishId
    ]);

    if ($result) {
        // Lấy thông tin wish sau khi update
        $getWishQuery = "SELECT * FROM wishes WHERE id = ?";
        $getWishStmt = $pdo->prepare($getWishQuery);
        $getWishStmt->execute([$wishId]);
        $updatedWish = $getWishStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Wish updated successfully',
            'data' => $updatedWish
        ]);
    } else {
        throw new Exception('Failed to update wish');
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
