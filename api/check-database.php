<?php
require_once __DIR__.'/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $conn = db_get_connection();
    
    // Show all tables
    $result = $conn->query("SHOW TABLES");
    $tables = [];
    
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo json_encode([
        'success' => true,
        'tables' => $tables,
        'database' => DB_NAME
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
