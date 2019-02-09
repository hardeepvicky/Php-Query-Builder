<?php
require_once './Query.php';
require_once './Where.php';

$qb = new \QueryBuilder\QuerySelect("orders");

$wh = \QueryBuilder\Where::init("AND")
        ->add("product_id", "1")
        ->addWhere(\QueryBuilder\Where::init("OR")->add("is_deliverd", "1")->add("is_ship", "1"));
        
echo $qb->setWhere($wh)->order("id")->get();