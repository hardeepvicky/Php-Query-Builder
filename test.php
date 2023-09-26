<?php
require_once './vendor/autoload.php';


use HardeepVicky\QueryBuilder\QuerySelect;
use HardeepVicky\QueryBuilder\Join;
use HardeepVicky\QueryBuilder\Table;
use HardeepVicky\QueryBuilder\Condition;
use HardeepVicky\QueryBuilder\SqlFormatter;

$querySelect = new QuerySelect(new Table("countries", "Country"));

$join_state = new Join(Join::LEFT, new Table("states", "S"), "country_id");

                $join_city = new Join(Join::LEFT, new Table("cities", "City"), "state_id");
                $join_city->field("id");
                $join_city->field("name", NULL, true);

        $join_state->join($join_city);
        $join_state->field("name", "state_name");

$querySelect->join($join_state);
        
$q = $querySelect->get();

echo SqlFormatter::format($q);


$querySelect = new QuerySelect(new Table("countries"));

        $join_state = new Join(Join::LEFT, new Table("states", "S"), "country_id");
        $join_state->noField();

$querySelect->join($join_state);

$querySelect->field("name", "country");

$querySelect->addCustomField("SUM(S.id) AS state_count");

$querySelect->groupBy("countries.id");

$querySelect->setHaving(Condition::init("AND")->add("state_count", 5, ">"));

$querySelect->order("state_count", "desc");

$querySelect->setLimit(10);
        
$q = $querySelect->get();

echo SqlFormatter::format($q);


$querySelect = new QuerySelect(new Table("countries", "C"));

                $join_city = new Join(Join::INNER, new Table("cities", "City"), "state_id");
                $join_city->field("name", null, true);                
                $join_city->setWhere(Condition::init("OR"));
                $join_city->addRawWhere("AND (S.name = City.name)");

        $join_state = new Join(Join::INNER, new Table("states", "S"), "country_id");
        $join_state->field("name", null, true);
        $join_state->join($join_city);

$querySelect->join($join_state);

$querySelect->field("name", null, true);
        
$q = $querySelect->get();

echo SqlFormatter::format($q);



$querySelect = new QuerySelect(new Table("countries", "C"));

                $join_city = new Join(Join::INNER, new Table("cities", "City"), "state_id");
                $join_city->noField();
                $join_city->setWhere(Condition::init("OR"));
                $join_city->addRawWhere("AND (S.name = City.name)");

        $join_state = new Join(Join::INNER, new Table("states", "S"), "country_id");
        $join_state->noField();
        $join_state->join($join_city);

$querySelect->join($join_state);

$querySelect->field("name", null, true);

$querySelect->addCustomField("COUNT(S.name) as same_name_count");

$querySelect->groupBy("C.id");

$querySelect->order("same_name_count", "DESC");

$querySelect->setHaving(Condition::init("AND")->add("C__name", "%india%", "like"));
        
$q = $querySelect->get();

echo SqlFormatter::format($q);