<?php
$file = 'assets/js/admin.js';
$content = file_get_contents($file);

// Add credentials: 'include' to all fetch calls
$patterns = [
    // Pattern 1: fetch with just URL
    '/(\$\{API_BASE\}[^`]+`)(,\s*\{)/' => '$1, {credentials: \'include\', ',
    '/(await fetch\(`[^`]+`)\)/' => '$1, {credentials: \'include\'})',
    
    // Pattern 2: fetch already has options
    '/(await fetch\(`[^`]+`,\s*\{)(\s*method:)/' => '$1credentials: \'include\', $2',
];

// Count changes
$count = 0;

// Fix GET requests without options
$content = preg_replace_callback(
    '/(await fetch\(`\$\{API_BASE\}[^`]+`\))/',
    function($matches) use (&$count) {
        if (!str_contains($matches[0], 'credentials')) {
            $count++;
            return str_replace(')', ', {credentials: \'include\'})', $matches[0]);
        }
        return $matches[0];
    },
    $content
);

// Fix POST/PUT requests - add credentials if not exists
$content = preg_replace_callback(
    '/(await fetch\(`\$\{API_BASE\}[^`]+`,\s*\{)([^}]+)\}/',
    function($matches) use (&$count) {
        if (!str_contains($matches[0], 'credentials')) {
            $count++;
            return $matches[1] . "credentials: 'include', " . $matches[2] . '}';
        }
        return $matches[0];
    },
    $content
);

file_put_contents($file, $content);
echo "✅ Fixed $count fetch calls in admin.js\n";

// Also check login.html
$loginFile = 'html/login.html';
if (file_exists($loginFile)) {
    $loginContent = file_get_contents($loginFile);
    $loginOriginal = $loginContent;
    
    $loginContent = preg_replace_callback(
        '/(await fetch\([^,]+)(,\s*\{)/',
        function($matches) {
            if (!str_contains($matches[0], 'credentials')) {
                return $matches[1] . ', {credentials: \'include\', ';
            }
            return $matches[0];
        },
        $loginContent
    );
    
    if ($loginContent !== $loginOriginal) {
        file_put_contents($loginFile, $loginContent);
        echo "✅ Fixed login.html\n";
    }
}

echo "\n🎉 Done! Commit and push now.\n";
?>

