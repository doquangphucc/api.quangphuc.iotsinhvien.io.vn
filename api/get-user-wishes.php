<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config.php';
require_once 'connect.php';

try {
    // Get parameters
    $username = $_GET['username'] ?? '';
    $status = $_GET['status'] ?? 'all'; // all, completed, pending
    $category = $_GET['category'] ?? '';
    $priority = $_GET['priority'] ?? '';
    $purchaseStatus = $_GET['purchase_status'] ?? '';
    $limit = min(intval($_GET['limit'] ?? 50), 100); // Max 100 items
    $offset = max(intval($_GET['offset'] ?? 0), 0);
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }
    
    // Get user_id
    $userQuery = "SELECT id FROM tai_khoan WHERE user = ?";
    $userStmt = $pdo->prepare($userQuery);
    $userStmt->execute([$username]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    $userId = $user['id'];
    
    // Build query
    $whereClause = ['user_id = ?'];
    $params = [$userId];
    
    // Filter by status
    if ($status === 'completed') {
        $whereClause[] = 'is_completed = 1';
    } elseif ($status === 'pending') {
        $whereClause[] = 'is_completed = 0';
    }
    
    // Filter by category
    if (!empty($category)) {
        $whereClause[] = 'category = ?';
        $params[] = $category;
    }
    
    // Filter by priority
    if (!empty($priority)) {
        $whereClause[] = 'priority = ?';
        $params[] = $priority;
    }
    
    // Filter by purchase status
    if (!empty($purchaseStatus)) {
        $whereClause[] = 'purchase_status = ?';
        $params[] = $purchaseStatus;
    }
    
    // Main query
    $query = "SELECT 
                id, item_id, title, description, category, priority, 
                price, currency, product_url, purchase_status, target_date,
                is_completed, created_at, updated_at
              FROM wishes 
              WHERE " . implode(' AND ', $whereClause) . "
              ORDER BY 
                CASE priority 
                    WHEN 'high' THEN 1 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 3 
                END,
                target_date ASC,
                created_at DESC
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $wishes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count total
    $countQuery = "SELECT COUNT(*) as total FROM wishes WHERE " . implode(' AND ', $whereClause);
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute(array_slice($params, 0, -2)); // Remove limit and offset
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Format data
    foreach ($wishes as &$wish) {
        $wish['is_completed'] = (bool)$wish['is_completed'];
        $wish['price'] = $wish['price'] ? floatval($wish['price']) : null;
        
        // Add priority icon
        $priorityIcons = ['low' => '🟢', 'medium' => '🟡', 'high' => '🔴'];
        $wish['priority_icon'] = $priorityIcons[$wish['priority']] ?? '🟡';
        
        // Add purchase status icon
        $statusIcons = ['researching' => '🔍', 'saving' => '💰', 'ready_to_buy' => '✅'];
        $wish['purchase_status_icon'] = $statusIcons[$wish['purchase_status']] ?? '🔍';
        
        // Format price
        if ($wish['price']) {
            $currencySymbols = ['VND' => 'đ', 'USD' => '$', 'EUR' => '€'];
            $symbol = $currencySymbols[$wish['currency']] ?? 'đ';
            $wish['formatted_price'] = number_format($wish['price']) . ' ' . $symbol;
        } else {
            $wish['formatted_price'] = null;
        }
        
        // Format dates
        if ($wish['target_date']) {
            $wish['formatted_date'] = date('d/m/Y', strtotime($wish['target_date']));
        }
        
        $wish['formatted_created'] = date('d/m/Y H:i', strtotime($wish['created_at']));
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'wishes' => $wishes,
            'pagination' => [
                'total' => (int)$total,
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < $total
            ],
            'filters' => [
                'status' => $status,
                'category' => $category,
                'priority' => $priority,
                'purchase_status' => $purchaseStatus
            ],
            'statistics' => [
                'total_wishes' => (int)$total,
                'total_value' => array_sum(array_column($wishes, 'price'))
            ]
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'WISHES_FETCH_ERROR'
    ]);
}
?>
