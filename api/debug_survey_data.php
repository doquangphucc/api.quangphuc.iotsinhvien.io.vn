<?php
// Debug: Check survey_results table
require_once 'connect.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== SURVEY RESULTS DEBUG ===\n";
    
    // Check latest survey_results
    $sql = "SELECT * FROM survey_results ORDER BY id DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Latest 5 survey_results:\n";
    foreach ($results as $result) {
        echo "ID: {$result['id']}, Survey ID: {$result['survey_id']}\n";
        echo "  monthly_kwh: {$result['monthly_kwh']}\n";
        echo "  panels_needed: {$result['panels_needed']}\n";
        echo "  total_cost: {$result['total_cost']}\n";
        echo "  panel_name: {$result['panel_name']}\n";
        echo "---\n";
    }
    
    // Check latest solar_surveys
    echo "\n=== SOLAR SURVEYS DEBUG ===\n";
    $sql = "SELECT * FROM solar_surveys ORDER BY id DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Latest 5 solar_surveys:\n";
    foreach ($surveys as $survey) {
        echo "ID: {$survey['id']}, User ID: {$survey['user_id']}\n";
        echo "  region: {$survey['region']}\n";
        echo "  phase: {$survey['phase']}\n";
        echo "  solar_panel_type: {$survey['solar_panel_type']}\n";
        echo "  monthly_bill: {$survey['monthly_bill']}\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
