<?php
require_once './Query.php';
require_once './Where.php';
require_once './Join.php';

$qb = new \QueryBuilder\QuerySelect("orders");

$qb->field("id");
$qb->join(\QueryBuilder\Join::init("INNER JOIN", "id", "order_details", "OD", "order_id")->field("product_id")->field);
        
echo $qb->get();