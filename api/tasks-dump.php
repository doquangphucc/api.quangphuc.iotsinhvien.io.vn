<?php
header('Content-Type: application/json');
require_once 'connect.php';
$username = $_GET['u'] ?? '';
if(!$username){ echo json_encode(['error'=>'u required']); exit; }
try {
  // Database schema đầy đủ
  $st = $pdo->prepare('SELECT id,username,title,description,scheduled_date,scheduled_time,completed,created_at,updated_at FROM tasks WHERE username=? ORDER BY id');
  $st->execute([$username]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['user_found'=>true,'count'=>count($rows),'rows'=>$rows]);
} catch(PDOException $e){ echo json_encode(['error'=>'db','message'=>$e->getMessage()]); }
