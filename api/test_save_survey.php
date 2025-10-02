<?php
// Test file để debug save_survey.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Save Survey API</h1>";

// Test 1: Check session
echo "<h2>1. Test Session</h2>";
require_once 'session.php';
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>✅ Session active</p>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ Session not active</p>";
}

// Test 2: Check database connection
echo "<h2>2. Test Database Connection</h2>";
try {
    require_once 'config.php';
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connected</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 3: Check tables exist
echo "<h2>3. Test Tables</h2>";
$tables = ['solar_surveys', 'survey_results'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        
        // Show structure
        echo "<details><summary>Show structure</summary><pre>";
        $cols = $conn->query("SHOW COLUMNS FROM $table");
        while ($col = $cols->fetch_assoc()) {
            echo $col['Field'] . " - " . $col['Type'] . "\n";
        }
        echo "</pre></details>";
    } else {
        echo "<p style='color: red;'>❌ Table '$table' NOT exists</p>";
    }
}

// Test 4: Test insert
echo "<h2>4. Test Insert (if logged in)</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User logged in: " . $_SESSION['full_name'] . "</p>";
    
    $test_data = [
        'region' => 'mien-nam',
        'phase' => 1,
        'solarPanel' => 590,
        'monthlyBill' => 1000000,
        'usageTime' => 'day'
    ];
    
    echo "<p>Test data:</p><pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO solar_surveys 
            (user_id, full_name, phone, region, phase, solar_panel_type, monthly_bill, usage_time) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "isssiiis",
            $_SESSION['user_id'],
            $_SESSION['full_name'],
            $_SESSION['phone'],
            $test_data['region'],
            $test_data['phase'],
            $test_data['solarPanel'],
            $test_data['monthlyBill'],
            $test_data['usageTime']
        );
        
        if ($stmt->execute()) {
            $survey_id = $conn->insert_id;
            echo "<p style='color: green;'>✅ Test insert SUCCESS! Survey ID: $survey_id</p>";
            
            // Delete test data
            $conn->query("DELETE FROM solar_surveys WHERE id = $survey_id");
            echo "<p style='color: orange;'>🗑️ Test data deleted</p>";
        } else {
            echo "<p style='color: red;'>❌ Insert failed: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Not logged in. Please <a href='../html/login.html'>login</a> first.</p>";
}

$conn->close();
?>
