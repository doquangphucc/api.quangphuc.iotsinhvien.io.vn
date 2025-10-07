<?php
echo "PHP is working! Version: " . phpversion();
echo "<br>Time: " . date('Y-m-d H:i:s');
echo "<br>Server pulled code at: " . filemtime(__FILE__);
echo "<br>Current file: " . __FILE__;
?>
