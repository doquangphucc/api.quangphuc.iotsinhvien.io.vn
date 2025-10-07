<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== CHECK save_survey.php ON SERVER ===\n\n";

$file = __DIR__ . '/save_survey.php';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$content = file_get_contents($file);

// Tìm dòng bind_param cho survey_results (dòng dài nhất)
preg_match_all('/bind_param\s*\(\s*["\']([ids]+)["\']/s', $content, $matches, PREG_OFFSET_CAPTURE);

if (empty($matches[1])) {
    die("❌ Could not find any bind_param in file\n");
}

// Tìm type string dài nhất (đó là cho survey_results INSERT)
$longestTypeString = '';
$longestLength = 0;
$longestOffset = 0;

foreach ($matches[1] as $match) {
    $typeString = $match[0];
    $length = strlen($typeString);
    if ($length > $longestLength) {
        $longestLength = $length;
        $longestTypeString = $typeString;
        $longestOffset = $match[1];
    }
}

echo "✅ Found " . count($matches[1]) . " bind_param statements\n";
echo "✅ Longest type string (for survey_results):\n";
echo "Type: $longestTypeString\n";
echo "Length: $longestLength characters\n\n";

if ($longestLength === 45) {
    echo "✅ CORRECT: Type string has 45 characters (MATCH!)\n";
} else {
    echo "❌ WRONG: Type string has $longestLength characters (should be 45)\n";
    echo "\nExpected: iddsiisdddddisdddisddddsisdddiddddddddiddddddds (45 chars)\n";
    echo "Actual:   $longestTypeString ($longestLength chars)\n";
}

echo "\n\n=== GIT INFO ===\n";
echo "Current directory: " . getcwd() . "\n";
echo "File path: $file\n";
echo "File modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";

// Hiển thị context xung quanh dòng bind_param dài nhất
echo "\n\n=== CODE SNIPPET (around longest bind_param) ===\n";
$lines = explode("\n", $content);
$currentPos = 0;
foreach ($lines as $num => $line) {
    $currentPos += strlen($line) + 1; // +1 for \n
    if ($currentPos > $longestOffset) {
        $start = max(0, $num - 2);
        $end = min(count($lines) - 1, $num + 8);
        for ($i = $start; $i <= $end; $i++) {
            $marker = ($i === $num) ? '>>> ' : '    ';
            echo $marker . ($i + 1) . ": " . substr($lines[$i], 0, 120) . (strlen($lines[$i]) > 120 ? '...' : '') . "\n";
        }
        break;
    }
}
?>
