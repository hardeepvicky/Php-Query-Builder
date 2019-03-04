<?php
namespace QueryBuilder;

class QuerySelect
{
    private $table, $alias, $fields = array(), $orders = array(), $where = null, $joins = array();
    
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
            $fields[] = $table_alias . "." . $field . " AS " . $table_alias . "__" . $ailas;
        }
        
        if (!$fields)
        {
            $fields[] = "$table_alias.*";
            
        }
        
        foreach($this->joins as $join)
        {
            $fields = array_merge($fields, $join["join"]->getFields());
        }
        
        $fields = implode(", ", $fields);
        
        $q = "SELECT $fields FROM " . $this->table . " AS " . $table_alias;
        
        foreach($this->joins as $join)
        {
            $str = $this->getJoin($join["join"], $join["where"]);
            
            if ($str)
            {
                $q .= " " . $str;
            }
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
    
    public function join(Join $join, Where $wh = null)
    {
        $this->joins[] = array(
            "join" => $join,
            "where" => $wh
        );
        
        return $this;
    }
    
    public function getJoin(Join $join, Where $wh = null)
    {
        if (is_null($wh))
        {
            $wh = new Where("AND");
        }
        
        $table_alias = $this->alias ? $this->alias : $this->table;
        $other_table_alias = $join->alias ? $join->alias : $join->table;
        
        $wh->add($table_alias . "." . $join->primary_field, $other_table_alias . "." . $join->foreign_field , "=", "");
        
        $q = $join->join_type . " " . $join->table . " AS " . $other_table_alias . " ON " . $wh->get();
        
        return $q;
    }
}