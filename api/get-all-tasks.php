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
            END as is_overdue
        FROM tasks t
        LEFT JOIN tai_khoan tk ON t.user_id = tk.id
        WHERE tk.user = ? $statusCondition
        ORDER BY $sort $order, t.id DESC
        LIMIT ? OFFSET ?
            $sql = "SELECT DISTINCT 
                    t.id, t.title, t.description, t.category, t.priority, 
                    t.scheduled_date as due_date, t.status as is_completed, 
                    t.created_at, t.updated_at,
                    CASE 
                        WHEN t.scheduled_date IS NOT NULL AND t.scheduled_date < CURDATE() AND t.status = 0 
                        THEN 1 
                        ELSE 0 
                    END as is_overdue
                FROM tasks t
                LEFT JOIN tai_khoan tk ON t.user_id = tk.id
                WHERE tk.user = ? $statusCondition
                ORDER BY $sort $order, t.id DESC
                LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
        $task['is_completed'] = (bool)$task['is_completed'];
        $task['is_overdue'] = (bool)$task['is_overdue'];
        if ($task['due_date']) {
            $task['due_date_formatted'] = date('d/m/Y', strtotime($task['due_date']));
            $task['due_date_relative'] = getDaysUntilDue($task['due_date']);
        } else {
            $task['due_date_formatted'] = null;
            $task['due_date_relative'] = null;
        }
        $task['created_at_formatted'] = date('d/m/Y H:i', strtotime($task['created_at']));
        if (!empty($task['updated_at'])) {
            $task['updated_at_formatted'] = date('d/m/Y H:i', strtotime($task['updated_at']));
        }
        <?php
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

        require_once 'connect.php';

        try {
            $username = $_GET['username'] ?? '';
            $status   = $_GET['status'] ?? 'all';
            $limit    = (int)($_GET['limit'] ?? 100);
            $offset   = (int)($_GET['offset'] ?? 0);
            $sort     = $_GET['sort'] ?? 'created_at';
            $order    = strtoupper($_GET['order'] ?? 'DESC');
            if (!$username) throw new Exception('Username is required');

            $validSort = ['created_at','title','due_date','priority','category'];
            if (!in_array($sort,$validSort)) $sort='created_at';
            if (!in_array($order,['ASC','DESC'])) $order='DESC';

            $statusCondition='';
            $params = [$username];
            if ($status==='completed') $statusCondition=' AND t.status=1';
            elseif ($status==='pending') $statusCondition=' AND t.status=0';

            $countStmt=$pdo->prepare("SELECT COUNT(*) total FROM tasks t LEFT JOIN tai_khoan tk ON t.user_id=tk.id WHERE tk.user=? $statusCondition");
            $countStmt->execute($params);
            $totalCount=(int)$countStmt->fetchColumn();

            $sql = "SELECT DISTINCT t.id,t.title,t.description,t.category,t.priority,
                        t.scheduled_date due_date,t.status is_completed,t.created_at,t.updated_at,
                        CASE WHEN t.scheduled_date IS NOT NULL AND t.scheduled_date < CURDATE() AND t.status=0 THEN 1 ELSE 0 END is_overdue
                    FROM tasks t
                    LEFT JOIN tai_khoan tk ON t.user_id=tk.id
                    WHERE tk.user=? $statusCondition
                    ORDER BY $sort $order, t.id DESC
                    LIMIT ? OFFSET ?";
            $stmt=$pdo->prepare($sql);
            $stmt->execute(array_merge($params,[$limit,$offset]));
            $raw=$stmt->fetchAll(PDO::FETCH_ASSOC);

            // De-duplicate & format
            $map=[]; foreach ($raw as $r){ $map[$r['id']]=$r; }
            $tasks=array_values($map);
            foreach ($tasks as &$t){
                $t['id']=(int)$t['id'];
                $t['is_completed']=(bool)$t['is_completed'];
                $t['is_overdue']=(bool)$t['is_overdue'];
                if ($t['due_date']){
                    $t['due_date_formatted']=date('d/m/Y',strtotime($t['due_date']));
                    $t['due_date_relative']=getDaysUntilDue($t['due_date']);
                } else { $t['due_date_formatted']=null; $t['due_date_relative']=null; }
                $t['created_at_formatted']=date('d/m/Y H:i',strtotime($t['created_at']));
                if ($t['updated_at']) $t['updated_at_formatted']=date('d/m/Y H:i',strtotime($t['updated_at']));
            }
            unset($t);

            $stats=['total'=>$totalCount,'completed'=>0,'pending'=>0,'overdue'=>0];
            foreach($tasks as $t){ if($t['is_completed']) $stats['completed']++; else { $stats['pending']++; if($t['is_overdue']) $stats['overdue']++; } }

            $payload=[
                'success'=>true,
                'data'=>$tasks,
                'stats'=>$stats,
                'pagination'=>[
                    'total'=>$totalCount,'limit'=>$limit,'offset'=>$offset,'has_more'=>($offset+$limit)<$totalCount
                ],
                'message'=>'Tasks loaded successfully'
            ];
            if (!empty($_GET['debug'])) {
                $payload['raw']=$raw;
                $payload['debug']=[
                    'sql'=>$sql,
                    'raw_ids'=>array_map(fn($r)=>$r['id'],$raw),
                    'unique_ids'=>array_map(fn($r)=>$r['id'],$tasks),
                    'version'=>'tasks_api_v3'
                ];
            }
            echo json_encode($payload,JSON_UNESCAPED_UNICODE);
        } catch(PDOException $e){
            echo json_encode(['success'=>false,'message'=>'Database error: '.$e->getMessage()]);
        } catch(Exception $e){
            echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
        }

        function getDaysUntilDue($dueDate){
            if(!$dueDate) return null; $today=new DateTime(); $due=new DateTime($dueDate); $diff=$today->diff($due);
            if($due<$today) return 'Quá hạn '.$diff->days.' ngày';
            if($diff->days===0) return 'Hôm nay';
            if($diff->days===1) return 'Ngày mai';
            return 'Còn '.$diff->days.' ngày';
        }
        ?>
