<?php
// Direct test for check_admin.php output
header('Content-Type: text/plain');

echo "=== TESTING check_admin.php OUTPUT ===\n\n";

// Capture output
ob_start();
include 'api/admin/check_admin.php';
$output = ob_get_clean();

echo "Raw Output Length: " . strlen($output) . " bytes\n";
echo "Raw Output (with visible whitespace):\n";
echo "START>>>" . $output . "<<<END\n\n";

// Check for BOM
if (substr($output, 0, 3) === "\xEF\xBB\xBF") {
    echo "⚠️ WARNING: UTF-8 BOM detected at start of output!\n\n";
}

// Check for leading/trailing whitespace
if (preg_match('/^\s+/', $output)) {
    echo "⚠️ WARNING: Leading whitespace detected!\n\n";
}
if (preg_match('/\s+$/', $output)) {
    echo "⚠️ WARNING: Trailing whitespace detected!\n\n";
}

// Try to decode JSON
echo "Attempting JSON decode...\n";
$decoded = json_decode($output, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ JSON Error: " . json_last_error_msg() . "\n";
    echo "JSON Error Code: " . json_last_error() . "\n";
} else {
    echo "✅ JSON is valid!\n";
    echo "Decoded data:\n";
    print_r($decoded);
}

