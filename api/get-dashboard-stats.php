<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'connect.php';

try {
    // Get username from query parameters
    $username = $_GET['username'] ?? '';
    
    if (empty($username)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username is required'
        ]);
        exit;
    }
    
    // Get overall statistics
    $stats = [];
    
    // Tasks statistics
    $taskQuery = "SELECT 
        COUNT(*) as total_tasks,
        SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_tasks
        FROM tasks WHERE username = ?";
    
    $stmt = $pdo->prepare($taskQuery);
    $stmt->execute([$username]);
    $taskResult = $stmt->fetch();
    
    // Wishes statistics  
    $wishQuery = "SELECT 
        COUNT(*) as total_wishes,
        SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_wishes
        FROM wishes WHERE username = ?";
    
    $stmt = $pdo->prepare($wishQuery);
    $stmt->execute([$username]);
    $wishResult = $stmt->fetch();
    
    // Calculate overall stats
    $totalItems = ($taskResult['total_tasks'] ?? 0) + ($wishResult['total_wishes'] ?? 0);
    $completedItems = ($taskResult['completed_tasks'] ?? 0) + ($wishResult['completed_wishes'] ?? 0);
    $completionRate = $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 1) : 0;
    
    $stats = [
        'overall' => [
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
            'pending_items' => $totalItems - $completedItems,
            'overall_completion_rate' => $completionRate
        ],
        'tasks' => [
            'total' => $taskResult['total_tasks'] ?? 0,
            'completed' => $taskResult['completed_tasks'] ?? 0,
            'pending' => ($taskResult['total_tasks'] ?? 0) - ($taskResult['completed_tasks'] ?? 0),
            'completion_rate' => ($taskResult['total_tasks'] ?? 0) > 0 ? 
                round((($taskResult['completed_tasks'] ?? 0) / ($taskResult['total_tasks'] ?? 0)) * 100, 1) : 0
        ],
        'wishes' => [
            'total' => $wishResult['total_wishes'] ?? 0,
            'completed' => $wishResult['completed_wishes'] ?? 0,
            'pending' => ($wishResult['total_wishes'] ?? 0) - ($wishResult['completed_wishes'] ?? 0),
            'completion_rate' => ($wishResult['total_wishes'] ?? 0) > 0 ? 
                round((($wishResult['completed_wishes'] ?? 0) / ($wishResult['total_wishes'] ?? 0)) * 100, 1) : 0
        ]
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $stats,
        'message' => 'Statistics loaded successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'data' => [
            'overall' => [
                'total_items' => 0,
                'completed_items' => 0,
                'pending_items' => 0,
                'overall_completion_rate' => 0
            ],
            'tasks' => [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'completion_rate' => 0
            ],
            'wishes' => [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'completion_rate' => 0
            ]
        ]
    ]);
}

// No need to close PDO connection
?>
