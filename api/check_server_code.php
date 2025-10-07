<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== CHECK save_survey.php ON SERVER ===\n\n";

$file = __DIR__ . '/save_survey.php';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$content = file_get_contents($file);

// Tìm dòng bind_param
if (preg_match('/bind_param\s*\(\s*["\']([ids]+)["\']/s', $content, $matches)) {
    $typeString = $matches[1];
    echo "✅ Found bind_param type string:\n";
    echo "Type: $typeString\n";
    echo "Length: " . strlen($typeString) . " characters\n\n";
    
    if (strlen($typeString) === 45) {
        echo "✅ CORRECT: Type string has 45 characters (MATCH!)\n";
    } else {
        echo "❌ WRONG: Type string has " . strlen($typeString) . " characters (should be 45)\n";
        echo "\nExpected: iddsiisdddddisdddisddddsisdddiddddddddiddddddds\n";
        echo "Actual:   $typeString\n";
    }
} else {
    echo "❌ Could not find bind_param in file\n";
}

echo "\n\n=== GIT INFO ===\n";
echo "Current directory: " . getcwd() . "\n";
echo "File path: $file\n";
echo "File modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";

// Hiển thị 5 dòng xung quanh bind_param
echo "\n\n=== CODE SNIPPET (around bind_param) ===\n";
$lines = explode("\n", $content);
foreach ($lines as $num => $line) {
    if (stripos($line, 'bind_param') !== false) {
        $start = max(0, $num - 2);
        $end = min(count($lines) - 1, $num + 5);
        for ($i = $start; $i <= $end; $i++) {
            echo ($i + 1) . ": " . $lines[$i] . "\n";
        }
        break;
    }
}
?>
