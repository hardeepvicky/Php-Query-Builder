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
    
    public function add($field, $value, $operator = "=", $value_type = "string")
    {
        if (
                strpos($field, "=") !== FALSE 
                || strpos($field, ">") !== FALSE 
                || strpos($field, "<") !== FALSE 
                || strpos(strtoupper($field, "NOT")) !== FALSE 
                || strpos(strtoupper($field, "IN")) !== FALSE
            )
        {
            $operator = "";
        }
        
        switch(gettype($value))
        {
            case "string":
                switch($value_type)
                {
                    case "string":
                        $value = "'" . $value . "'";
                        break;

                    case "date":
                    case "datetime":
                        $value = date("Y-m-d H:i:s", strtotime($value));
                        $value = "'" . $value . "'";
                        break;

                    case "bool":
                    case "boolean":
                        $value = (int) $value;
                        break;
                }
            break;
        
            case "boolean":
                $value = (int) $value;
            break;
        
            case "array":
                $value = "(" . implode(",", $value) . ")";
                $operator = "IN";
            break;
        
            case "NULL":
                $value = "IS NULL";
                $operator = "";
            break;
        
            default:
                throw new Exception("Un-Supported value type");
        }
                
        $this->fields[] = array(
            "field" => trim($field),
            "value" => $value,
            "op" => trim($operator)
        );
        
        return $this;
    }
    
    public function addList($arr, $op)
    {
        foreach($arr as $field => $value)
        {
            $this->add($field, $value, $op);
        }
        
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
            $key = $field["field"];
            
            if ($table)
            {
                $key = $table . "." . $key;
            }
            
            if ($field["op"])
            {
                $key = $key . " " . $field["op"];
            }
            
            $list[$key] = $field["value"];
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
            $list[] = $k . " " . $v ;
        }
        
        return $list;
    }
}

