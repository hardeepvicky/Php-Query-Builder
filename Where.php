<?php
namespace QueryBuilder;

class Where
{
    private $op = null, $fields = array(), $where_list = array();
    public function __construct($op)
    {
        $this->op = " " . $op . " ";
    }
    
    public static function init($op)
    {
        return new Where($op);
    }
    
    public function add($field, $value, $operator = "=")
    {
        $this->fields[] = array(
            "field" => trim($field),
            "value" => $value,
            "op" => trim($operator)
        );
        
        return $this;
    }
    
    public function addWhere(Where $wh)
    {
        $this->where_list[] = $wh;
        return $this;
    }
    
    public function get($table = "")
    {
        $list = array();
        
        foreach($this->fields as $field)
        {
            $list[$table . $field["field"] . " " . $field["op"]] = $field["value"];
        }
        
        $list = $this->_listToStr($list);
        
        foreach($this->where_list as $wh)
        {
            $list[] = $wh->get($table);
        }
        
        return "(" . implode($this->op, $list) . ")";
    }
    
    private function _listToStr($data)
    {
        $list = array();
        
        foreach($data as $k => $v)
        {
            $list[] = $k . "'" . $v . "'";
        }
        
        return $list;
    }
}

