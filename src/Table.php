<?php
namespace HardeepVicky\QueryBuilder;

class Table
{
    public String $name;

    public String | Null $alias; 

    public String $primary_field;

    /**
     * @param String $name
     * @param String | Null $alias = NULL
     * @param String $primary_field = "id"
     */
    public function __construct(String $name, String | Null $alias = NULL, String $primary_field = "id")
    {
        $this->name = $name;
        $this->alias = $alias;
        $this->primary_field = $primary_field;
    }

    /**
     * @return String
     */
    public function getRefName()
    {
        return $this->alias ? $this->alias : $this->name;
    }
}