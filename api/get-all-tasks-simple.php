<?php
// Minimal diagnostic endpoint to verify real rows in DB (no formatting, no joins duplication)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }

require_once 'connect.php';

$username = $_GET['username'] ?? '';
$debug = !empty($_GET['debug']);
if(!$username){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'Username required']); exit; }

try {
    // Database hiện tại sử dụng username trực tiếp, không cần user_id
    $stmt = $pdo->prepare('SELECT id,username,title,description,scheduled_date,scheduled_time,completed,created_at,updated_at FROM tasks WHERE username=? ORDER BY id ASC');
    $stmt->execute([$username]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // build id counts for diagnostics
    $idCounts=[]; foreach($rows as $r){ $idCounts[$r['id']] = ($idCounts[$r['id']]??0)+1; }

    $payload = [
        'success'=>true,
        'row_count'=>count($rows),
        'data'=>$rows,
    ];
    if($debug){
        $payload['id_counts']=$idCounts;
        $payload['duplicate_ids']=array_values(array_filter(array_keys($idCounts), fn($k)=>$idCounts[$k]>1));
        $payload['version']='tasks_simple_v1';
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch(PDOException $e){
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}
?>
