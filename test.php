<?php
require_once './Query.php';
require_once './Where.php';
require_once './Join.php';

$qb = new \QueryBuilder\QuerySelect("legder_sales", "Legder");

$qb->field("id");
$qb->join(\QueryBuilder\Join::init("INNER JOIN", "legder_voucher_type_id", "legder_voucher_types", "LegderVoucherType", "id"));

$legder_detail_join = \QueryBuilder\Join::init("LEFT JOIN", "id", "legder_sale_details", "LegderDetail", "legder_sale_id")->noField();

$product_join = \QueryBuilder\Join::init("LEFT JOIN", "product_id", "products", "Product", "id");
$product_join->join(
        \QueryBuilder\Join::init("LEFT JOIN", "id", "product_files", "ProductFile", "product_id")->noField()
        ->join(\QueryBuilder\Join::init("LEFT JOIN", "image_id", "images", "ProductFileImage", "id"))
);

$product_join->join(\QueryBuilder\Join::init("LEFT JOIN", "category_id", "categories", "Category", "id")->field("name"));

$legder_detail_join->join($product_join);
$legder_detail_join->join(\QueryBuilder\Join::init("LEFT JOIN", "item_id", "items", "Item", "id"));

$qb->join($legder_detail_join);
        
echo $qb->get();