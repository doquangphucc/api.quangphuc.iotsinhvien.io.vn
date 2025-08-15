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
header('X-Tasks-Api-Version: tasks_api_clean_v1');

try {
    // Lấy tham số từ query string
    $username = $_GET['username'] ?? '';
    $status = $_GET['status'] ?? 'all'; // 'all', 'completed', 'pending'
    $limit = (int)($_GET['limit'] ?? 100);
    $offset = (int)($_GET['offset'] ?? 0);
    $sort = $_GET['sort'] ?? 'created_at'; // 'created_at', 'title', 'due_date', 'priority'
    $order = strtoupper($_GET['order'] ?? 'DESC'); // 'ASC' hoặc 'DESC'
    
    if (empty($username)) {
        throw new Exception('Username is required');
    }

    // Validate sort column
    $validSortColumns = ['created_at', 'title', 'due_date', 'priority', 'category'];
    if (!in_array($sort, $validSortColumns)) {
        $sort = 'created_at';
    }

    // Validate order
    if (!in_array($order, ['ASC', 'DESC'])) {
        $order = 'DESC';
    }

    // Xây dựng điều kiện WHERE cho status
    $statusCondition = '';
    $params = [$username];
    
    if ($status === 'completed') {
        $statusCondition = ' AND t.status = 1';
    } elseif ($status === 'pending') {
        $statusCondition = ' AND t.status = 0';
    }

    // Đếm tổng số tasks
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM tasks t
        LEFT JOIN tai_khoan tk ON t.user_id = tk.id
        WHERE tk.user = ? $statusCondition
    ");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Lấy danh sách tasks với phân trang (thêm t.id cuối ORDER BY để tránh mập mờ khi cùng timestamp)
    $stmt = $pdo->prepare("
        SELECT 
            t.id, t.title, t.description, t.category, t.priority, 
            t.scheduled_date as due_date, t.status as is_completed, 
            t.created_at, t.updated_at,
            CASE 
                WHEN t.scheduled_date IS NOT NULL AND t.scheduled_date < CURDATE() AND t.status = 0 
                THEN 1 
                ELSE 0 
            <?php
            // Clean rewritten tasks API with duplicate diagnostics
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');

            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

            require_once 'connect.php';

            function fmt_relative_days($dueDate){
                if(!$dueDate) return null; $today=new DateTime('today'); $due=new DateTime($dueDate); $diff=$today->diff($due);
                if($due < $today) return 'Quá hạn '.$diff->days.' ngày';
                if($diff->days === 0) return 'Hôm nay';
                if($diff->days === 1) return 'Ngày mai';
                return 'Còn '.$diff->days.' ngày';
            }

            try {
                $username = $_GET['username'] ?? '';
                $status   = $_GET['status'] ?? 'all'; // all|completed|pending
                $limit    = max(1,(int)($_GET['limit'] ?? 100));
                $offset   = max(0,(int)($_GET['offset'] ?? 0));
                $sort     = $_GET['sort'] ?? 'created_at';
                $order    = strtoupper($_GET['order'] ?? 'DESC');
                $debug    = !empty($_GET['debug']);

                if(!$username) throw new Exception('Username is required');

                $validSort = ['created_at','title','due_date','priority','category'];
                if(!in_array($sort,$validSort, true)) $sort = 'created_at';
                if(!in_array($order,['ASC','DESC'], true)) $order = 'DESC';

                $statusCondition='';
                if($status==='completed') $statusCondition=' AND t.status=1';
                elseif($status==='pending') $statusCondition=' AND t.status=0';

                // Count
                $countSql = "SELECT COUNT(*) FROM tasks t INNER JOIN tai_khoan u ON t.user_id=u.id WHERE u.user=? $statusCondition";
                $countStmt=$pdo->prepare($countSql);
                $countStmt->execute([$username]);
                $total=(int)$countStmt->fetchColumn();

                // Main fetch (DISTINCT to guard, then we still de-dup in PHP)
                $sql = "SELECT DISTINCT t.id, t.title, t.description, t.category, t.priority,
                            t.scheduled_date AS due_date, t.status AS is_completed,
                            t.created_at, t.updated_at,
                            CASE WHEN t.scheduled_date IS NOT NULL AND t.scheduled_date < CURDATE() AND t.status=0 THEN 1 ELSE 0 END AS is_overdue
                        FROM tasks t
                        INNER JOIN tai_khoan u ON t.user_id=u.id
                        WHERE u.user=? $statusCondition
                        ORDER BY $sort $order, t.id DESC
                        LIMIT ? OFFSET ?";
                $stmt=$pdo->prepare($sql);
                $stmt->execute([$username,$limit,$offset]);
                $raw=$stmt->fetchAll(PDO::FETCH_ASSOC);

                // Duplicate diagnostics before de-dup
                $idCounts=[]; foreach($raw as $r){ $id=$r['id']; $idCounts[$id]=($idCounts[$id]??0)+1; }

                // De-dup by id (last one wins)
                $byId=[]; foreach($raw as $r){ $byId[$r['id']]=$r; }
                $tasks=array_values($byId);

                // Format
                foreach($tasks as &$t){
                    $t['id']=(int)$t['id'];
                    $t['is_completed']=(bool)$t['is_completed'];
                    $t['is_overdue']=(bool)$t['is_overdue'];
                    if($t['due_date']){ $t['due_date_formatted']=date('d/m/Y',strtotime($t['due_date'])); $t['due_date_relative']=fmt_relative_days($t['due_date']); }
                    else { $t['due_date_formatted']=null; $t['due_date_relative']=null; }
                    $t['created_at_formatted']=date('d/m/Y H:i',strtotime($t['created_at']));
                    if(!empty($t['updated_at'])) $t['updated_at_formatted']=date('d/m/Y H:i',strtotime($t['updated_at']));
                }
                unset($t);

                // Stats
                $stats=['total'=>$total,'completed'=>0,'pending'=>0,'overdue'=>0];
                foreach($tasks as $t){ if($t['is_completed']) $stats['completed']++; else { $stats['pending']++; if($t['is_overdue']) $stats['overdue']++; } }

                $payload=[
                    'success'=>true,
                    'data'=>$tasks,
                    'stats'=>$stats,
                    'pagination'=>[
                        'total'=>$total,
                        'limit'=>$limit,
                        <?php
                        // get-all-tasks.php (clean)
                        header('Content-Type: application/json');
                        header('Access-Control-Allow-Origin: *');
                        header('Access-Control-Allow-Methods: GET, OPTIONS');
                        header('Access-Control-Allow-Headers: Content-Type');

                        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

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

                            $validSort=['created_at','title','due_date','priority','category'];
                            if(!in_array($sort,$validSort,true)) $sort='created_at';
                            if(!in_array($order,['ASC','DESC'],true)) $order='DESC';

                            $statusCond='';
                            if($status==='completed') $statusCond=' AND t.status=1';
                            elseif($status==='pending') $statusCond=' AND t.status=0';

                            $countSql="SELECT COUNT(*) FROM tasks t INNER JOIN tai_khoan u ON t.user_id=u.id WHERE u.user=? $statusCond";
                            $cStmt=$pdo->prepare($countSql); $cStmt->execute([$username]); $total=(int)$cStmt->fetchColumn();

                            $sql="SELECT t.id,t.title,t.description,t.category,t.priority,
                                         t.scheduled_date due_date,t.status is_completed,t.created_at,t.updated_at,
                                         CASE WHEN t.scheduled_date IS NOT NULL AND t.scheduled_date < CURDATE() AND t.status=0 THEN 1 ELSE 0 END is_overdue
                                  FROM tasks t INNER JOIN tai_khoan u ON t.user_id=u.id
                                  WHERE u.user=? $statusCond
                                  ORDER BY $sort $order, t.id DESC
                                  LIMIT ? OFFSET ?";
                            $stmt=$pdo->prepare($sql); $stmt->execute([$username,$limit,$offset]);
                            $raw=$stmt->fetchAll(PDO::FETCH_ASSOC);

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
                                    'sql'=>$sql,
                                    'count_sql'=>$countSql,
                                    'raw_count'=>count($raw),
                                    'unique_after_php'=>count($tasks),
                                    'id_counts'=>$idCounts,
                                    'duplicate_ids'=>array_values(array_filter(array_keys($idCounts),fn($k)=>$idCounts[$k]>1)),
                                    'version'=>'tasks_api_clean_v1'
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
