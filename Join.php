<?php
namespace QueryBuilder;

class Join
{
    public $join_type, $primary_field, $table, $alias, $foreign_field, $fields = array();
    
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
        foreach($this->fields as  $ailas => $field)
        {
            $fields[] = $table_alias . "." . $field . " AS " . $table_alias . "__" . $ailas;
        }
        
        if (!$fields)
        {
            $fields[] = "$table_alias.*";
        }
        
        return $fields;
    }
}
