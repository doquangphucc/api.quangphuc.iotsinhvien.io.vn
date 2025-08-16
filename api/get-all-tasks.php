<?php
// get-all-tasks.php (single clean version)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Tasks-Api-Version: tasks_api_clean_final');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }

require_once 'connect.php';

function rel_days($due){
    if(!$due) return null; $today=new DateTime('today'); $d=new DateTime($due); $diff=$today->diff($d);
    if($d<$today) return 'Quá hạn '.$diff->days.' ngày';
    if($diff->days===0) return 'Hôm nay';
    if($diff->days===1) return 'Ngày mai';
    return 'Còn '.$diff->days.' ngày';
}

try {
    $username = $_GET['username'] ?? '';
    $status   = $_GET['status'] ?? 'all';
    $limit    = max(1,(int)($_GET['limit'] ?? 100));
    $offset   = max(0,(int)($_GET['offset'] ?? 0));
    $sort     = $_GET['sort'] ?? 'created_at';
    $order    = strtoupper($_GET['order'] ?? 'DESC');
    $debug    = !empty($_GET['debug']);
    if(!$username) throw new Exception('Username is required');

    $validSort=['created_at','title','scheduled_date'];
    if(!in_array($sort,$validSort,true)) $sort='created_at';
    if(!in_array($order,['ASC','DESC'],true)) $order='DESC';

    $statusCond='';
    if($status==='completed') $statusCond=' AND completed=1';
    elseif($status==='pending') $statusCond=' AND completed=0';

    $countSql="SELECT COUNT(*) FROM tasks WHERE username=? $statusCond";
    $c=$pdo->prepare($countSql); $c->execute([$username]); $total=(int)$c->fetchColumn();

    $sql="SELECT id,title,description,
                 scheduled_date due_date,scheduled_time,completed is_completed,
                 created_at,updated_at,
                 CASE WHEN scheduled_date IS NOT NULL AND scheduled_date < CURDATE() AND completed=0 THEN 1 ELSE 0 END is_overdue
          FROM tasks
          WHERE username=? $statusCond
          ORDER BY $sort $order, id DESC
          LIMIT ? OFFSET ?";
    $st=$pdo->prepare($sql); $st->execute([$username,$limit,$offset]);
    $raw=$st->fetchAll(PDO::FETCH_ASSOC);

    $idCounts=[]; foreach($raw as $r){ $id=$r['id']; $idCounts[$id]=($idCounts[$id]??0)+1; }
    $byId=[]; foreach($raw as $r){ $byId[$r['id']]=$r; }
    $tasks=array_values($byId);

    foreach($tasks as &$t){
        $t['id']=(int)$t['id'];
        $t['is_completed']=(bool)$t['is_completed'];
        $t['is_overdue']=(bool)$t['is_overdue'];
        if($t['due_date']){ $t['due_date_formatted']=date('d/m/Y',strtotime($t['due_date'])); $t['due_date_relative']=rel_days($t['due_date']); }
        else { $t['due_date_formatted']=null; $t['due_date_relative']=null; }
        $t['created_at_formatted']=date('d/m/Y H:i',strtotime($t['created_at']));
        if(!empty($t['updated_at'])) $t['updated_at_formatted']=date('d/m/Y H:i',strtotime($t['updated_at']));
    }
    unset($t);

    $stats=['total'=>$total,'completed'=>0,'pending'=>0,'overdue'=>0];
    foreach($tasks as $t){ if($t['is_completed']) $stats['completed']++; else { $stats['pending']++; if($t['is_overdue']) $stats['overdue']++; } }

    $payload=[
        'success'=>true,
        'data'=>$tasks,
        'stats'=>$stats,
        'pagination'=>['total'=>$total,'limit'=>$limit,'offset'=>$offset,'has_more'=>($offset+$limit)<$total],
        'message'=>'Tasks loaded successfully'
    ];
    if($debug){
        $payload['debug']=[
            'version'=>'tasks_api_clean_final',
            'sql'=>$sql,
            'count_sql'=>$countSql,
            'raw_count'=>count($raw),
            'unique_after_php'=>count($tasks),
            'id_counts'=>$idCounts,
            'duplicate_ids'=>array_values(array_filter(array_keys($idCounts),fn($k)=>$idCounts[$k]>1))
        ];
        $payload['raw']=$raw;
    }
    echo json_encode($payload,JSON_UNESCAPED_UNICODE);
}catch(PDOException $e){
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}catch(Exception $e){
    http_response_code(400); echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
