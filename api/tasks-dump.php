<?php
header('Content-Type: application/json');
require_once 'connect.php';
$username = $_GET['u'] ?? '';
if(!$username){ echo json_encode(['error'=>'u required']); exit; }
try {
  $u = $pdo->prepare('SELECT id FROM tai_khoan WHERE user=? LIMIT 1');
  $u->execute([$username]);
  $uid = $u->fetchColumn();
  if(!$uid){ echo json_encode(['user_found'=>false,'rows'=>[]]); exit; }
  $st = $pdo->prepare('SELECT id,item_id,title,status,created_at,updated_at FROM tasks WHERE user_id=? ORDER BY id');
  $st->execute([$uid]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['user_found'=>true,'count'=>count($rows),'rows'=>$rows]);
} catch(PDOException $e){ echo json_encode(['error'=>'db','message'=>$e->getMessage()]); }
