<?php
namespace HardeepVicky\QueryBuilder;

class Where
{
    private $op = null, $fields = array(), $where_list = array();

    /**
     * @param String $op
     */
    public function __construct(String $op)
    {
        $this->op = " " . strtoupper(trim($op)) . " ";
    }
    
    /**
     * @param String $op
     * 
     * @return Where
     */
    public static function init(String $op)
    {
        return new Where($op);
    }
    
    /**
     * @param String $field
     * @param $value
     * @param String $operator
     * @param String $value_type
     * 
     * @return Where
     */
    public function add(String $field, $value, String $operator = "=", String $value_type = "string")
    {
        if (
                strpos($field, "=") !== FALSE 
                || strpos($field, ">") !== FALSE 
                || strpos($field, "<") !== FALSE 
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
                        $value = date("Y-m-d", strtotime($value));
                        $value = "'" . $value . "'";
                        break;
                    
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
            case "integer":
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
                throw new \Exception("Query Builder : Un-Supported value type :" . gettype($value) );
        }
                
        $this->fields[] = array(
            "field" => trim($field),
            "value" => $value,
            "op" => trim($operator)
        );
        
        return $this;
    }
    
    /**
     * @param Array $arr 
     * @param String $operator
     * 
     * @return Where
     */
    public function addList(Array $arr, String $operator = "=")
    {
        foreach($arr as $field => $value)
        {
            $this->add($field, $value, $operator);
        }
        
        return $this;
    }
    
    /**
     * @param Where $wh
     * 
     * @return Where
     */
    public function addWhere(Where $wh)
    {
        $this->where_list[] = $wh;
        return $this;
    }
    
    /**
     * @param String $table
     * 
     * @return String
     */
    public function get(String $table = "")
    {
        $list = array();
        
        foreach($this->fields as $field)
        {
            if (strlen($field["value"]) > 0)
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

