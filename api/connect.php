<?php
require_once __DIR__.'/config.php';

// Test kết nối
$conn = db_get_connection();
json_response(['status'=>'success','database'=>DB_NAME]);
?>
