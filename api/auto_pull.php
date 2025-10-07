<?php
// Script tự động pull code từ GitHub
// CẢNH BÁO: Chỉ dùng cho development, không dùng trên production!

header('Content-Type: text/plain; charset=utf-8');

$repoPath = '/www/wwwroot/api.quangphuc.iotsinhvien.io.vn';

echo "=== AUTO PULL FROM GITHUB ===\n\n";

// Kiểm tra quyền
if (!is_writable($repoPath)) {
    die("❌ Directory not writable: $repoPath\n");
}

// Chuyển đến thư mục repo
chdir($repoPath);
echo "✅ Changed directory to: " . getcwd() . "\n\n";

// Git pull
echo "=== Running: git pull origin main ===\n";
exec('git pull origin main 2>&1', $output, $returnCode);

foreach ($output as $line) {
    echo "$line\n";
}

if ($returnCode === 0) {
    echo "\n✅ SUCCESS: Code pulled successfully!\n";
    
    // Kiểm tra lại type string
    echo "\n=== VERIFICATION ===\n";
    $saveFile = $repoPath . '/api/save_survey.php';
    $content = file_get_contents($saveFile);
    
    preg_match_all('/bind_param\s*\(\s*["\']([ids]+)["\']/s', $content, $matches);
    $longestLength = 0;
    foreach ($matches[1] as $typeString) {
        if (strlen($typeString) > $longestLength) {
            $longestLength = strlen($typeString);
            $longestType = $typeString;
        }
    }
    
    echo "Type string length: $longestLength\n";
    if ($longestLength === 45) {
        echo "✅ CORRECT: Type string is now 45 characters!\n";
    } else {
        echo "❌ STILL WRONG: Type string is $longestLength characters\n";
    }
    
    echo "\nFile modified: " . date('Y-m-d H:i:s', filemtime($saveFile)) . "\n";
    
} else {
    echo "\n❌ FAILED: Git pull failed with code $returnCode\n";
    echo "You may need to SSH into server and run:\n";
    echo "  cd $repoPath\n";
    echo "  git reset --hard origin/main\n";
    echo "  git pull origin main\n";
}

echo "\n=== DONE ===\n";
?>
