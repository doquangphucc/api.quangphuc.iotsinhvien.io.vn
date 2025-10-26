<?php
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TESTING connect.php ===\n\n";

try {
    echo "Step 1: Including connect.php...\n";
    require_once __DIR__ . '/api/connect.php';
    echo "✅ connect.php loaded successfully\n\n";
    
    echo "Step 2: Checking variables...\n";
    echo "- \$conn exists: " . (isset($conn) ? "YES" : "NO") . "\n";
    echo "- \$conn type: " . (isset($conn) ? gettype($conn) : "N/A") . "\n";
    echo "- Session started: " . (session_status() === PHP_SESSION_ACTIVE ? "YES" : "NO") . "\n";
    echo "- Session ID: " . session_id() . "\n";
    echo "- Session data: " . print_r($_SESSION, true) . "\n\n";
    
    if (isset($conn)) {
        echo "Step 3: Testing database query...\n";
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            echo "✅ Database query successful\n";
            $row = $result->fetch_assoc();
            echo "Test result: " . $row['test'] . "\n\n";
        } else {
            echo "❌ Database query failed: " . $conn->error . "\n\n";
        }
        
        echo "Step 4: Testing user query (like check_admin.php does)...\n";
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            echo "User ID from session: $user_id\n";
            
            $stmt = $conn->prepare("SELECT is_admin, full_name, username FROM users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                echo "✅ User query successful\n";
                echo "User data: " . print_r($user, true) . "\n";
                $stmt->close();
            } else {
                echo "❌ Failed to prepare statement: " . $conn->error . "\n";
            }
        } else {
            echo "⚠️ No user_id in session\n";
        }
    } else {
        echo "❌ \$conn variable not set!\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

