<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== CHECKING check_admin.php FILE ===\n\n";

$file = __DIR__ . '/api/admin/check_admin.php';

if (!file_exists($file)) {
    echo "❌ FILE NOT FOUND: $file\n";
    exit;
}

echo "✅ File exists\n";
echo "File path: $file\n";
echo "File size: " . filesize($file) . " bytes\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n\n";

echo "=== FILE CONTENT ===\n";
$content = file_get_contents($file);
echo $content;
echo "\n\n=== END OF FILE ===\n\n";

echo "=== HEX DUMP (first 200 bytes) ===\n";
$hex = substr($content, 0, 200);
for ($i = 0; $i < strlen($hex); $i++) {
    printf("%02x ", ord($hex[$i]));
    if (($i + 1) % 16 === 0) echo "\n";
}
echo "\n\n=== HEX DUMP (last 100 bytes) ===\n";
$hex = substr($content, -100);
for ($i = 0; $i < strlen($hex); $i++) {
    printf("%02x ", ord($hex[$i]));
    if (($i + 1) % 16 === 0) echo "\n";
}

echo "\n\n=== PHP SYNTAX CHECK ===\n";
$output = [];
$return = 0;
exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
echo implode("\n", $output);

echo "\n\n=== PHP ERROR LOG (last 50 lines) ===\n";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    echo "Error log: $errorLog\n";
    $lines = file($errorLog);
    echo implode('', array_slice($lines, -50));
} else {
    echo "Error log not found or not configured\n";
    echo "Trying /var/log/php*.log...\n";
    exec("tail -50 /var/log/php*.log 2>&1", $output);
    echo implode("\n", $output);
}

