<?php
require_once __DIR__ . '/../config/database.php';

function jsonResponse($data, $status=200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'sales';
        $year = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : null;

        if ($action === 'monthly') {
            if (!$year) { $year = (int)date('Y'); }
            $stmt = $pdo->prepare("SELECT MONTH(sale_date) month_no, MONTHNAME(sale_date) month_name, COALESCE(SUM(sales_amount),0) sales, COALESCE(SUM(orders),0) orders, 0 refunds FROM sales WHERE YEAR(sale_date)=:year GROUP BY MONTH(sale_date), MONTHNAME(sale_date) ORDER BY MONTH(sale_date)");
            $stmt->execute([':year'=>$year]);
            $raw = $stmt->fetchAll();
            $months = [];
            for ($i=1; $i<=12; $i++) {
                $months[$i] = ['month_no'=>$i,'month_name'=>date('F', mktime(0,0,0,$i,1)),'sales'=>0,'orders'=>0,'refunds'=>0];
            }
            foreach ($raw as $r) {
                $m=(int)$r['month_no'];
                $months[$m]['sales']=(float)$r['sales'];
                $months[$m]['orders']=(int)$r['orders'];
                $months[$m]['refunds']=(float)$r['refunds'];
            }
            $months=array_values($months);
            $annualSales=array_sum(array_column($months,'sales'));
            $annualOrders=array_sum(array_column($months,'orders'));
            
            foreach ($months as $i=>&$m) {
                $exp=$pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE YEAR(expense_date)=? AND MONTH(expense_date)=?"); $exp->execute([$year,$m['month_no']]); $m['expenses']=(float)$exp->fetchColumn(); $m['profit']=$m['sales']-$m['expenses'];
                if ($i===0) $m['growth']=null;
                else {
                    $previous=$months[$i-1]['sales'];
                    $m['growth']=$previous > 0 ? (($m['sales']-$previous)/$previous)*100 : ($m['sales']>0 ? 100 : 0);
                }
            }
            unset($m);
            $best=$months[0];
            foreach($months as $m) if($m['sales']>$best['sales']) $best=$m;
            jsonResponse(['success'=>true,'year'=>$year,'months'=>$months,'annual'=>['sales'=>$annualSales,'orders'=>$annualOrders,'expenses'=>array_sum(array_column($months,'expenses')),'average_monthly'=>$annualSales/12,'best_month'=>$best['month_name'],'best_sales'=>$best['sales']]]);
        }

        $month = isset($_GET['month']) && $_GET['month'] !== '' ? (int)$_GET['month'] : null;
        $search = trim($_GET['search'] ?? '');
        $where = []; $params = [];
        if ($year) { $where[]='YEAR(sale_date)=:year'; $params[':year']=$year; }
        if ($month) { $where[]='MONTH(sale_date)=:month'; $params[':month']=$month; }
        if ($search !== '') { $where[]='(product LIKE :search_product OR channel LIKE :search_channel)'; $params[':search_product']='%'.$search.'%'; $params[':search_channel']='%'.$search.'%'; }
        $whereSql = $where ? 'WHERE '.implode(' AND ',$where) : '';

        $stmt=$pdo->prepare("SELECT id,sale_date,product,channel,orders,sales_amount FROM sales $whereSql ORDER BY sale_date DESC,id DESC"); $stmt->execute($params); $rows=$stmt->fetchAll();
        $stmt=$pdo->prepare("SELECT COALESCE(SUM(sales_amount),0) total_sales,COALESCE(SUM(orders),0) total_orders,0 refunds,COALESCE(SUM(cost_amount),0) total_costs,COALESCE(SUM(sales_amount-refund_amount-cost_amount),0) total_profit FROM sales $whereSql"); $stmt->execute($params); $kpi=$stmt->fetch();
        $expenseWhere=[]; $expenseParams=[];
        if ($year) { $expenseWhere[]='YEAR(expense_date)=:year'; $expenseParams[':year']=$year; }
        if ($month) { $expenseWhere[]='MONTH(expense_date)=:month'; $expenseParams[':month']=$month; }
        $expenseWhereSql=$expenseWhere?'WHERE '.implode(' AND ',$expenseWhere):'';
        $stmt=$pdo->prepare("SELECT COALESCE(SUM(amount),0) total_expenses FROM expenses $expenseWhereSql"); $stmt->execute($expenseParams); $expense=$stmt->fetch();
        $kpi['total_expenses']=(float)$expense['total_expenses'];
        $kpi['total_profit']=(float)$kpi['total_sales']-$kpi['total_expenses'];
        $range = strtolower($_GET['range'] ?? 'daily');
        if (!in_array($range, ['daily','weekly','monthly'], true)) { $range = 'daily'; }
        if ($range === 'weekly') {
            $trendSql = "SELECT YEARWEEK(sale_date,1) period_key, DATE_FORMAT(MIN(sale_date),'%Y-%m-%d') label, SUM(sales_amount) value FROM sales $whereSql GROUP BY YEARWEEK(sale_date,1) ORDER BY MIN(sale_date) ASC";
        } elseif ($range === 'monthly') {
            $trendSql = "SELECT DATE_FORMAT(sale_date,'%Y-%m') period_key, DATE_FORMAT(MIN(sale_date),'%M %Y') label, SUM(sales_amount) value FROM sales $whereSql GROUP BY DATE_FORMAT(sale_date,'%Y-%m') ORDER BY MIN(sale_date) ASC";
        } else {
            $trendSql = "SELECT DATE_FORMAT(sale_date,'%Y-%m-%d') label, SUM(sales_amount) value FROM sales $whereSql GROUP BY sale_date ORDER BY sale_date ASC";
        }
        $stmt=$pdo->prepare($trendSql); $stmt->execute($params); $trend=$stmt->fetchAll();
        $stmt=$pdo->prepare("SELECT channel label,SUM(sales_amount) value FROM sales $whereSql GROUP BY channel ORDER BY value DESC"); $stmt->execute($params); $channels=$stmt->fetchAll();
        $stmt=$pdo->prepare("SELECT product label,SUM(orders) orders,SUM(sales_amount) value FROM sales $whereSql GROUP BY product ORDER BY value DESC LIMIT 5"); $stmt->execute($params); $products=$stmt->fetchAll();
        jsonResponse(['success'=>true,'data'=>$rows,'kpi'=>$kpi,'trend'=>$trend,'range'=>$range,'channels'=>$channels,'products'=>$products]);
    }

    $input=json_decode(file_get_contents('php://input'),true)??[];
    if($method==='POST'){
        $required=['sale_date','product','channel','orders','sales_amount'];
        foreach($required as $field) if(!isset($input[$field])||$input[$field]==='') jsonResponse(['success'=>false,'message'=>"Missing field: $field"],400);
        $stmt=$pdo->prepare('INSERT INTO sales (sale_date,product,channel,orders,sales_amount,refund_amount,cost_amount) VALUES (?,?,?,?,?,0,0)');
        $stmt->execute([$input['sale_date'],trim($input['product']),trim($input['channel']),(int)$input['orders'],(float)$input['sales_amount']]);
        jsonResponse(['success'=>true,'id'=>$pdo->lastInsertId(),'message'=>'Sale added.']);
    }
    if($method==='PUT'){
        if(empty($input['id'])) jsonResponse(['success'=>false,'message'=>'Missing sale ID.'],400);
        $stmt=$pdo->prepare('UPDATE sales SET sale_date=?,product=?,channel=?,orders=?,sales_amount=? WHERE id=?');
        $stmt->execute([$input['sale_date'],trim($input['product']),trim($input['channel']),(int)$input['orders'],(float)$input['sales_amount'],(int)$input['id']]);
        jsonResponse(['success'=>true,'message'=>'Sale updated.']);
    }
    if($method==='DELETE'){
        if(empty($input['id'])) jsonResponse(['success'=>false,'message'=>'Missing sale ID.'],400);
        $stmt=$pdo->prepare('DELETE FROM sales WHERE id=?'); $stmt->execute([(int)$input['id']]);
        jsonResponse(['success'=>true,'message'=>'Sale deleted.']);
    }
    jsonResponse(['success'=>false,'message'=>'Unsupported request method.'],405);
} catch(Throwable $e){ jsonResponse(['success'=>false,'message'=>'Server error: '.$e->getMessage()],500); }
