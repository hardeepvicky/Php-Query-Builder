<?php
require_once './vendor/autoload.php';

use HardeepVicky\QueryBuilder\QuerySelect;
use HardeepVicky\QueryBuilder\Join;
use HardeepVicky\QueryBuilder\Table;
use HardeepVicky\QueryBuilder\Where;
use Symfony\Component\VarDumper\VarDumper;

$queryBuilder = new QuerySelect(new Table("categories", "C"));

$join_product = new Join(Join::LEFT, new Table("products", "P"), "category_id");

        $join_order = new Join(Join::LEFT, new Table("orders", "O"), "product_id");
        $join_order->field("order_no");
        
$join_product->join($join_order);
$join_product->field("sku");

$queryBuilder->join($join_product);

$queryBuilder->setWhere(
        Where::init("AND")
        ->add("C.id", "0", ">", "")
        ->addWhere
        (
                Where::init("OR")
                ->add("P.id", NULL, "", "")
                ->add("O.id", NULL, "", "")
        )
);

//$queryBuilder->addRawWhere("AND C.id IN (SELECT id from categories)");
        
VarDumper::dump($queryBuilder->get());