<?php
header('Content-Type: application/json');
header('X-Debug-Ping-Version: 1');
echo json_encode([
  'ping'=>'ok',
  'time'=>date('Y-m-d H:i:s'),
  'script'=>__FILE__
]);
