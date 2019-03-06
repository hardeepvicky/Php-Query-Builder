<?php
namespace QueryBuilder;

class Join
{
    public $join_type, $primary_field, $table, $alias, $foreign_field, $wh, $fields = array(), $joins = array();
    
    public function __construct($join_type, $primary_field, $table, $alias, $foreign_field)
    {
        $this->join_type = trim($join_type);
        $this->primary_field = trim($primary_field);
        $this->table = trim($table);
        $this->alias = trim($alias);
        $this->foreign_field = trim($foreign_field);
    }
    
    public static function init($join_type, $primary_field, $table, $alias, $foreign_field)
    {
        return new Join($join_type, $primary_field, $table, $alias, $foreign_field);
    }
    
    public function join(Join $join)
    {
        $this->joins[] = $join;
        return $this;
    }
    
    public function noField()
    {
        $this->fields = null;
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
    
    public function getFields()
    {
        $fields = array();
        
        $table_alias = $this->alias ? $this->alias : $this->table;
        
        if (is_array($this->fields))
        {
            if (empty($this->fields))
            {
                $fields[] = "$table_alias.*";
            }
            else
            {
                foreach($this->fields as $ailas => $field)
                {
                    $fields[] = $table_alias . "." . $field . " AS " . $table_alias . "__" . $ailas;
                }
            }
        }
        
        foreach($this->joins as $join)
        {
            $fields = array_merge($fields, $join->getFields());
        }
        
        return $fields;
    }
    
    public function setWhere(Where $wh)
    {
        $this->wh = $wh;
        return $this;
    }
    
    public function get($table_alias)
    {
        if (is_null($this->wh))
        {
            $this->wh = new Where("AND");
        }
        
        $join_table_alias = $this->alias ? $this->alias : $this->table;
        
        $this->wh->add($table_alias . "." . $this->primary_field, $join_table_alias . "." . $this->foreign_field , "=", "");
        
        $q = $this->join_type . " " . $this->table . " AS " . $join_table_alias . " ON " . $this->wh->get();
        
        foreach($this->joins as $join)
        {
            $q .= $join->get($join_table_alias);
        }
        
        return $q;
    }
}
