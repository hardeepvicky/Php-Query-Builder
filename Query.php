<?php
namespace QueryBuilder;

class QuerySelect
{
    private $table, $alias, $fields = array(), $orders = array(), $where = null;
    
    public function __construct($table, $alias = NULL)
    {
        $this->table = $table;
        $this->alias = $alias;
    }
    
    public function setWhere($wh)
    {
        $this->where = $wh;
        return $this;
    }
    
    public function field($field, $alias = "")
    {
        if (!$alias)
        {
            $alias = $field;
        }
        
        $this->fields[$alias] = $field;
        return $this;
    }
    
    public function order($field, $order = "ASC")
    {
        $this->orders[$field] = $order;
        return $this;
    }
    
    public function get()
    {
        $fields = array();
        
        $table_alias = $this->alias ? $this->alias : $this->table;
        foreach($this->fields as  $ailas => $field)
        {
            $fields[] = $table_alias . "." . $field . " AS " . $table_alias . "." . $ailas;
        }
        
        if ($fields)
        {
            $fields = implode(", ", $fields);
        }
        else
        {
            $fields = "*";
        }
        
        if ($this->alias)
        {
            $q = "SELECT $fields FROM " . $this->table . " AS " . $table_alias;
        }
        else
        {
            $q = "SELECT $fields FROM " . $this->table;
        }
        
        $wh = "";
        if ($this->where)
        {
            $wh = " WHERE " . $this->where->get($table_alias . ".");
        }
        
        $list = array();
        $order = "";
        foreach($this->orders as $field => $dir)
        {
            $list[] = $table_alias . "." . $field . " " . $dir;
        }
        
        if ($list)
        {
            $order = " ORDER BY " . implode(", ", $list);
        }
        
        return $q . $wh . $order . ";";
    }
}