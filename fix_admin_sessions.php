<?php
/**
 * Script to fix all admin API files to use proper session handling
 * Run this once then delete it
 */

$adminDir = __DIR__ . '/api/admin/';
$files = glob($adminDir . '*.php');

$oldPattern = [
    "header('Content-Type: application/json; charset=utf-8');\nheader('Access-Control-Allow-Origin: *');\nheader('Access-Control-Allow-Methods: GET, POST, OPTIONS');\nheader('Access-Control-Allow-Headers: Content-Type');\n\nif (\$_SERVER['REQUEST_METHOD'] === 'OPTIONS') {\n    http_response_code(200);\n    exit();\n}\n\nsession_start();\nrequire_once __DIR__ . '/../db_mysqli.php';",
    "header('Content-Type: application/json; charset=utf-8');\nheader('Access-Control-Allow-Origin: *');\nheader('Access-Control-Allow-Methods: GET');\nheader('Access-Control-Allow-Headers: Content-Type');\n\nsession_start();\nrequire_once __DIR__ . '/../db_mysqli.php';",
    "header('Content-Type: application/json; charset=utf-8');\nheader('Access-Control-Allow-Origin: *');\nheader('Access-Control-Allow-Methods: POST');\nheader('Access-Control-Allow-Headers: Content-Type');\n\nif (\$_SERVER['REQUEST_METHOD'] !== 'POST') {\n    http_response_code(405);\n    exit();\n}\n\nsession_start();\nrequire_once __DIR__ . '/../db_mysqli.php';",
    "session_start();\nrequire_once __DIR__ . '/../db_mysqli.php';"
];

$newPattern = "require_once __DIR__ . '/../connect.php';";

$fixedCount = 0;
$skipFiles = ['check_admin.php', 'reset_admin_password.php']; // Already fixed or special

foreach ($files as $file) {
    $filename = basename($file);
    
    if (in_array($filename, $skipFiles)) {
        echo "⏭️  Skipping: $filename\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Try each pattern
    foreach ($oldPattern as $pattern) {
        $content = str_replace($pattern, $newPattern, $content);
    }
    
    // If content changed, save it
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "✅ Fixed: $filename\n";
        $fixedCount++;
    } else {
        echo "⚪ No change: $filename\n";
    }
}

echo "\n📊 Summary: Fixed $fixedCount files\n";
echo "✅ Done! You can now delete this script.\n";
?>

