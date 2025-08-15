<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'connect.php';

try {
    // Lấy tham số từ query string
    $username = $_GET['username'] ?? '';
    $status = $_GET['status'] ?? 'all'; // 'all', 'completed', 'pending'
    $limit = (int)($_GET['limit'] ?? 100);
    $offset = (int)($_GET['offset'] ?? 0);
    $sort = $_GET['sort'] ?? 'created_at'; // 'created_at', 'title', 'price', 'priority'
    $order = strtoupper($_GET['order'] ?? 'DESC'); // 'ASC' hoặc 'DESC'
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Validate sort column
    $validSortColumns = ['created_at', 'title', 'price', 'priority', 'category'];
    if (!in_array($sort, $validSortColumns)) {
        $sort = 'created_at';
    }
            $sql = "SELECT DISTINCT 
                    w.id, w.title, w.description, w.category, w.priority, w.price, 
                    w.product_url as store_location, w.product_url as purchase_link, 
                    w.status as is_completed, w.created_at, w.updated_at
                <?php
                // get-all-wishes.php (clean)
                header('Content-Type: application/json');
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type');
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
                    if($status==='completed') $statusCond=' AND w.status=1';
                    elseif($status==='pending') $statusCond=' AND w.status=0';

                    $countSql="SELECT COUNT(*) FROM wishes w INNER JOIN tai_khoan u ON w.user_id=u.id WHERE u.user=? $statusCond";
                    $cStmt=$pdo->prepare($countSql); $cStmt->execute([$username]); $total=(int)$cStmt->fetchColumn();

                    $sql="SELECT w.id,w.title,w.description,w.category,w.priority,w.price,
                                 w.product_url store_location,w.product_url purchase_link,
                                 w.status is_completed,w.created_at,w.updated_at
                          FROM wishes w INNER JOIN tai_khoan u ON w.user_id=u.id
                          WHERE u.user=? $statusCond
                          ORDER BY $sort $order, w.id DESC
                          LIMIT ? OFFSET ?";
                    $stmt=$pdo->prepare($sql); $stmt->execute([$username,$limit,$offset]);
                    $raw=$stmt->fetchAll(PDO::FETCH_ASSOC);

                    $idCounts=[]; foreach($raw as $r){ $id=$r['id']; $idCounts[$id]=($idCounts[$id]??0)+1; }
                    $byId=[]; foreach($raw as $r){ $byId[$r['id']]=$r; }
                    $wishes=array_values($byId);

                    foreach($wishes as &$w){
                        $w['id']=(int)$w['id'];
                        $w['is_completed']=(bool)$w['is_completed'];
                        $w['price']=$w['price']!==null ? (float)$w['price'] : null;
                        $w['price_formatted']=$w['price']!==null?number_format($w['price'],0,',','.').' VND':'Chưa có giá';
                        $w['created_at_formatted']=date('d/m/Y H:i',strtotime($w['created_at']));
                        if(!empty($w['updated_at'])) $w['updated_at_formatted']=date('d/m/Y H:i',strtotime($w['updated_at']));
                        $w['status_text']=$w['is_completed']?'Đã mua':'Chưa mua';
                    }
                    unset($w);

                    $stats=['total'=>$total,'completed'=>0,'pending'=>0,'total_price'=>0,'completed_price'=>0,'pending_price'=>0];
                    foreach($wishes as $w){
                        if($w['price']!==null){ $stats['total_price']+=$w['price']; if($w['is_completed']) $stats['completed_price']+=$w['price']; else $stats['pending_price']+=$w['price']; }
                        if($w['is_completed']) $stats['completed']++; else $stats['pending']++; }
                    $stats['total_price_formatted']=number_format($stats['total_price'],0,',','.').' VND';
                    $stats['completed_price_formatted']=number_format($stats['completed_price'],0,',','.').' VND';
                    $stats['pending_price_formatted']=number_format($stats['pending_price'],0,',','.').' VND';

                    $payload=[
                        'success'=>true,
                        'data'=>$wishes,
                        'stats'=>$stats,
                        'pagination'=>['total'=>$total,'limit'=>$limit,'offset'=>$offset,'has_more'=>($offset+$limit)<$total],
                        'message'=>'Wishes loaded successfully'
                    ];
                    <?php
                    // get-all-wishes.php (clean standalone)
                    header('Content-Type: application/json');
                    header('Access-Control-Allow-Origin: *');
                    header('Access-Control-Allow-Methods: GET, OPTIONS');
                    header('Access-Control-Allow-Headers: Content-Type');
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
                        if($status==='completed') $statusCond=' AND w.status=1';
                        elseif($status==='pending') $statusCond=' AND w.status=0';

                        $countSql="SELECT COUNT(*) FROM wishes w INNER JOIN tai_khoan u ON w.user_id=u.id WHERE u.user=? $statusCond";
                        $cStmt=$pdo->prepare($countSql); $cStmt->execute([$username]); $total=(int)$cStmt->fetchColumn();

                        $sql="SELECT w.id,w.title,w.description,w.category,w.priority,w.price,
                                     w.product_url store_location,w.product_url purchase_link,
                                     w.status is_completed,w.created_at,w.updated_at
                              FROM wishes w INNER JOIN tai_khoan u ON w.user_id=u.id
                              WHERE u.user=? $statusCond
                              ORDER BY $sort $order, w.id DESC
                              LIMIT ? OFFSET ?";
                        $stmt=$pdo->prepare($sql); $stmt->execute([$username,$limit,$offset]);
                        $raw=$stmt->fetchAll(PDO::FETCH_ASSOC);

                        $idCounts=[]; foreach($raw as $r){ $id=$r['id']; $idCounts[$id]=($idCounts[$id]??0)+1; }
                        $byId=[]; foreach($raw as $r){ $byId[$r['id']]=$r; }
                        $wishes=array_values($byId);

                        foreach($wishes as &$w){
                            $w['id']=(int)$w['id'];
                            $w['is_completed']=(bool)$w['is_completed'];
                            $w['price']=$w['price']!==null ? (float)$w['price'] : null;
                            $w['price_formatted']=$w['price']!==null?number_format($w['price'],0,',','.').' VND':'Chưa có giá';
                            $w['created_at_formatted']=date('d/m/Y H:i',strtotime($w['created_at']));
                            if(!empty($w['updated_at'])) $w['updated_at_formatted']=date('d/m/Y H:i',strtotime($w['updated_at']));
                            $w['status_text']=$w['is_completed']?'Đã mua':'Chưa mua';
                        }
                        unset($w);

                        $stats=['total'=>$total,'completed'=>0,'pending'=>0,'total_price'=>0,'completed_price'=>0,'pending_price'=>0];
                        foreach($wishes as $w){
                            if($w['price']!==null){ $stats['total_price']+=$w['price']; if($w['is_completed']) $stats['completed_price']+=$w['price']; else $stats['pending_price']+=$w['price']; }
                            if($w['is_completed']) $stats['completed']++; else $stats['pending']++; }
                        $stats['total_price_formatted']=number_format($stats['total_price'],0,',','.').' VND';
                        $stats['completed_price_formatted']=number_format($stats['completed_price'],0,',','.').' VND';
                        $stats['pending_price_formatted']=number_format($stats['pending_price'],0,',','.').' VND';

                        $payload=[
                            'success'=>true,
                            'data'=>$wishes,
                            'stats'=>$stats,
                            'pagination'=>['total'=>$total,'limit'=>$limit,'offset'=>$offset,'has_more'=>($offset+$limit)<$total],
                            'message'=>'Wishes loaded successfully'
                        ];
                        if($debug){
                            $payload['debug']=[
                                'sql'=>$sql,
                                'count_sql'=>$countSql,
                                'raw_count'=>count($raw),
                                'unique_after_php'=>count($wishes),
                                'id_counts'=>$idCounts,
                                'duplicate_ids'=>array_values(array_filter(array_keys($idCounts),fn($k)=>$idCounts[$k]>1)),
                                'version'=>'wishes_api_clean_v2'
                            ];
                            $payload['raw']=$raw;
                        }
                        echo json_encode($payload,JSON_UNESCAPED_UNICODE);
                    } catch(PDOException $e){
                        http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
                    } catch(Exception $e){
                        http_response_code(400); echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
                    }
                    ?>
