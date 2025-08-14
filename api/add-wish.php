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
    $description = $input['description'] ?? '';
    $price = $input['price'] ?? null;
    $priority = $input['priority'] ?? 'medium';
    $targetDate = $input['target_date'] ?? date('Y-m-d');

    if (empty($username) || empty($title)) {
        throw new Exception('Username and title are required');
    }

    $query = "INSERT INTO wishes (username, title, description, price, priority, target_date, is_completed, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([$username, $title, $description, $price, $priority, $targetDate]);

    if ($result) {
        $wishId = $pdo->lastInsertId();
        
        // Lấy thông tin wish vừa tạo
        $getWishQuery = "SELECT id, title, description, price, priority, target_date, is_completed, created_at 
                         FROM wishes WHERE id = ?";
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
        'message' => $e->getMessage()
    ]);
}
?>
