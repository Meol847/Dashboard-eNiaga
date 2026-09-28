<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');
function jsonResponse($data,$status=200){http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE);exit;}
try{
  $method=$_SERVER['REQUEST_METHOD'];
  if($method==='POST'){
    $input=json_decode(file_get_contents('php://input'),true)??[];
    foreach(['expense_date','category','description','amount'] as $field){if(!isset($input[$field])||$input[$field]==='')jsonResponse(['success'=>false,'message'=>"Missing field: $field"],400);}
    if((float)$input['amount']<0)jsonResponse(['success'=>false,'message'=>'Expense amount cannot be negative.'],400);
    $stmt=$pdo->prepare('INSERT INTO expenses (expense_date,category,description,amount) VALUES (?,?,?,?)');
    $stmt->execute([$input['expense_date'],trim($input['category']),trim($input['description']),(float)$input['amount']]);
    jsonResponse(['success'=>true,'id'=>$pdo->lastInsertId(),'message'=>'Expense added.']);
  }
  jsonResponse(['success'=>false,'message'=>'Unsupported request method.'],405);
}catch(Throwable $e){jsonResponse(['success'=>false,'message'=>'Server error: '.$e->getMessage()],500);}
