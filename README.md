# PHP MySQL Query Builder

This library required when you need to create MySQL Query using classes and arrays. This Library only for creating Select Query For MySQL Databse.

i have used SqlFormatter Library built by **Jeremy Dorn** <jeremy@jeremydorn.com> to format query in browser.

Feel Free to comment and if any one want to countribute then please contact me **Hardeep Singh** Contact me at <hardeepvicky1@gmail.com> OR at <hardeep.singh417@gmail.com>

## Installation

Install Library using composer

```bash
  composer require hardeep-vicky/php-query-builder
```

## Basic Usage/Examples

- First include composer's autoload file.
- QuerySelect class is main class for generating query which accpet a argument of class Table
- Table class accept three arguments
  - First Table name (required)
  - Second Alias (optional)
  - primary field name (optional) (defaull id)

```php
require_once './vendor/autoload.php';

use HardeepVicky\QueryBuilder\QuerySelect;
use HardeepVicky\QueryBuilder\Table;
use HardeepVicky\QueryBuilder\Join;
use HardeepVicky\QueryBuilder\Condition;
use HardeepVicky\QueryBuilder\SqlFormatter;

$querySelect = new QuerySelect(new Table("countries"));
# OR
$querySelect = new QuerySelect(new Table("countries", "C"));
# OR 
$querySelect = new QuerySelect(new Table("countries", "C", "id"));

$querySelect->setWhere(
        Condition::init("OR")->add("C.name", "%india%", "like")
);

$q = $querySelect->get();

echo $q;

```

## Output

SELECT
C.*
FROM
`countries` AS C
WHERE
(C.name like '%india%')

## Use with Complex Conditions

```php
require_once './vendor/autoload.php';
use HardeepVicky\QueryBuilder\QuerySelect;
use HardeepVicky\QueryBuilder\Table;
use HardeepVicky\QueryBuilder\Join;
use HardeepVicky\QueryBuilder\Condition;
use HardeepVicky\QueryBuilder\SqlFormatter;

$querySelect = new QuerySelect(new Table("countries", "C"));

$querySelect->setWhere(
        Condition::init("AND")
                ->add("region", "Asia")
                ->addCondition(
                        Condition::init("OR")->add("C.name", "%india%", "like")->add("C.name", "%pakistan%", "like")
                )
);

$q = $querySelect->get();

echo $q;
```

## Output

```
SELECT
  C.*
FROM
  `countries` AS C
WHERE
  (
    region = 'Asia'
    AND (
        C.name like '%india%' OR C.name like '%pakistan%'
    )
)

```

## Example with Join

```php
$querySelect = new QuerySelect(new Table("countries", "C"));

$join_state = new Join(Join::INNER, new Table("states", "S"), "country_id");

$join_state->field("name");

$querySelect->join($join_state);

$querySelect->setWhere(
        Condition::init("AND")->add("C.name", "india")
);

$querySelect->setLimit(10);

$q = $querySelect->get();

echo SqlFormatter::format($q);

```

## Output

```
SELECT 
  C.*, 
  S.name 
FROM 
  `countries` AS C 
  INNER JOIN `states` AS S ON S.country_id = C.id 
WHERE 
  (C.name = 'india') 
LIMIT 
  10

```

In above example we use Join class , Join class construct function is below

```php
class Join
{
    const INNER = 'INNER JOIN';
    const LEFT = 'LEFT JOIN';
    const OUTER = 'OUTER JOIN';
  
    /**
     * @param String $join_type
     * @param Table $table
     * @param String $foreign_field
     */
    public function __construct(String $join_type, Table $table, String $foreign_field)
    {
```

And we call

```
$join_state->field("name"); 
```

this statement make select `name` field of states table. This function `field()` has three option

```
$join_state->field("name");   //output S.name
$join_state->field("name", "state_name");  //output S.name as state_name
$join_state->field("name", null, true);  //output S.name as S__name

```

Above options also avialable in QuerySelect class

```
$querySelect->field("name");   //output S.name
$querySelect->field("name", "country_name");  //output C.name as country_name
$querySelect->field("name", null, true);  //output C.name as C__name
```

you can set also no field as below

```
$join_state->noField();
```

```
$querySelect->noField();
```

this statement make no field selction in query

## Multiple Join

```php
$querySelect = new QuerySelect(new Table("countries", "Country"));

$join_city = new Join(Join::LEFT, new Table("cities", "City"), "state_id");
$join_city->field("name");

$join_state = new Join(Join::LEFT, new Table("states", "State"), "country_id");
$join_state->join($join_city);
$join_state->field("name");

$querySelect->join($join_state);

$querySelect->field("id");
$querySelect->field("name");  
  
$q = $querySelect->get();

```

## Output

```
SELECT 
  Country.id, 
  Country.name, 
  State.name, 
  City.name 
FROM 
  `countries` AS Country 
  LEFT JOIN `states` AS State ON State.country_id = Country.id 
  LEFT JOIN `cities` AS City ON City.state_id = State.id
```

You can get query without alias as below

```php
$querySelect = new QuerySelect(new Table("countries"));

$join_city = new Join(Join::LEFT, new Table("cities"), "state_id");
$join_city->field("name");

$join_state = new Join(Join::LEFT, new Table("states"), "country_id");
$join_state->join($join_city);
$join_state->field("name");

$querySelect->join($join_state);

$querySelect->field("id");
$querySelect->field("name");  
  
$q = $querySelect->get();
```

## Output

```
SELECT 
  `countries`.id, 
  `countries`.name, 
  `states`.name, 
  `cities`.name 
FROM 
  `countries` 
  LEFT JOIN `states` ON `states`.country_id = `countries`.id 
  LEFT JOIN `cities` ON `cities`.state_id = `states`.id
```

## Join With Condition

We have two options here

```php
$join_city->setWhere(
        Condition::init("OR")
                ->add("name", "%ludhiana%", "like")
                ->add("name", "%delhi%", "like")

);
```

## Output

```
SELECT 
  C.name, 
  S.name, 
  City.name 
FROM 
  `countries` AS C 
  INNER JOIN `states` AS S ON S.country_id = C.id 
  INNER JOIN `cities` AS City ON City.state_id = S.id AND (
      City.name like '%ludhiana%' OR City.name like '%delhi%'
  )
```

### we can concat raw where string as below

```php
$join_city->addRawWhere("AND (S.name = City.name)");
```

### Output

```
SELECT 
  C.name AS C__name, 
  S.name AS S__name, 
  City.name AS City__name 
FROM 
  `countries` AS C 
  INNER JOIN `states` AS S ON S.country_id = C.id 
  INNER JOIN `cities` AS City ON City.state_id = S.id AND (S.name = City.name)
```

## Another Examples are below

```php
$querySelect = new QuerySelect(new Table("countries"));

        $join_state = new Join(Join::LEFT, new Table("states", "S"), "country_id");
        $join_state->noField();

$querySelect->join($join_state);

$querySelect->field("name");

$querySelect->addCustomField("COUNT(S.id) AS state_count");

$querySelect->groupBy("countries.id");

$querySelect->setHaving(Condition::init("AND")->add("state_count", 5, ">"));

$querySelect->order("state_count", "desc");

$querySelect->setLimit(10);
  
$q = $querySelect->get();

echo SqlFormatter::format($q);
```

In above example we use custom field `$querySelect->addCustomField("COUNT(S.id) AS state_count");`
and conditions in having clause `$querySelect->setHaving(Condition::init("AND")->add("state_count", 5, ">"));`

## Output

```
SELECT 
  `countries`.name, 
  COUNT(S.id) AS state_count 
FROM 
  `countries` 
  LEFT JOIN `states` AS S ON S.country_id = `countries`.id 
GROUP BY 
  countries.id 
HAVING 
  (state_count > 5) 
ORDER BY 
  state_count DESC 
LIMIT 
  10
```

```php
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
```

## Output

```
SELECT 
  C.name AS C__name, 
  COUNT(S.name) as same_name_count 
FROM 
  `countries` AS C 
  INNER JOIN `states` AS S ON S.country_id = C.id 
  INNER JOIN `cities` AS City ON City.state_id = S.id 
  AND (S.name = City.name) 
GROUP BY 
  C.id 
HAVING 
  (C__name like '%india%') 
ORDER BY 
  same_name_count DESC
```

## 🚀 About Me

I'm a PHP Developer creating web applications and php libraries since 2014. Contact me at <hardeepvicky1@gmail.com> OR at <hardeep.singh417@gmail.com>

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Authors

- [Hardeep Singh](https://www.github.com/hardeepvicky)
