<?php
// CLEAN SINGLE VERSION wishes API (removed duplicated nested PHP)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Wishes-Api-Version: wishes_api_clean_final');
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }

require_once 'connect.php';

try {
    $username = $_GET['username'] ?? '';
    $status   = $_GET['status'] ?? 'all';
    $limit    = max(1,(int)($_GET['limit'] ?? 100));
    $offset   = max(0,(int)($_GET['offset'] ?? 0));
    $sort     = $_GET['sort'] ?? 'created_at';
    $order    = strtoupper($_GET['order'] ?? 'DESC');
    $debug    = !empty($_GET['debug']);
    if(!$username) throw new Exception('Username is required');

    $validSort=['created_at','title','price','priority','category'];
    if(!in_array($sort,$validSort,true)) $sort='created_at';
    if(!in_array($order,['ASC','DESC'],true)) $order='DESC';

    $statusCond='';
    if($status==='completed') $statusCond=' AND completed=1';
    elseif($status==='pending') $statusCond=' AND completed=0';

    $countSql="SELECT COUNT(*) FROM wishes WHERE username=? $statusCond";
    $c=$pdo->prepare($countSql); $c->execute([$username]); $total=(int)$c->fetchColumn();

    $sql="SELECT id,title,description,category,priority,
                 product_url store_location,product_url purchase_link,
                 completed is_completed,created_at,updated_at
          FROM wishes
          WHERE username=? $statusCond
          ORDER BY $sort $order, id DESC
          LIMIT ? OFFSET ?";
    $st=$pdo->prepare($sql); $st->execute([$username,$limit,$offset]);
    $raw=$st->fetchAll(PDO::FETCH_ASSOC);

    $idCounts=[]; foreach($raw as $r){ $id=$r['id']; $idCounts[$id]=($idCounts[$id]??0)+1; }
    $byId=[]; foreach($raw as $r){ $byId[$r['id']]=$r; }
    $wishes=array_values($byId);

    foreach($wishes as &$w){
        $w['id']=(int)$w['id'];
        $w['is_completed']=(bool)$w['is_completed'];
        $w['created_at_formatted']=date('d/m/Y H:i',strtotime($w['created_at']));
        if(!empty($w['updated_at'])) $w['updated_at_formatted']=date('d/m/Y H:i',strtotime($w['updated_at']));
        $w['status_text']=$w['is_completed']?'Đã mua':'Chưa mua';
    }
    unset($w);

    $stats=[
        'total'=>$total,'completed'=>0,'pending'=>0
    ];
    foreach($wishes as $w){
        if($w['is_completed']) $stats['completed']++; else $stats['pending']++;
    }

    $payload=[
        'success'=>true,
        'data'=>$wishes,
        'stats'=>$stats,
        'pagination'=>['total'=>$total,'limit'=>$limit,'offset'=>$offset,'has_more'=>($offset+$limit)<$total],
        'message'=>'Wishes loaded successfully'
    ];
    if($debug){
        $payload['debug']=[
            'version'=>'wishes_api_clean_final',
            'sql'=>$sql,
            'count_sql'=>$countSql,
            'raw_count'=>count($raw),
            'unique_after_php'=>count($wishes),
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
