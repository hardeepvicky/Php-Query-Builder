<?php
namespace HardeepVicky\QueryBuilder;

class Condition
{
    private $op = null, $fields = array(), $Condition_list = array();

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
     * @return Condition
     */
    public static function init(String $op)
    {
        return new Condition($op);
    }
    
    /**
     * @param String $field
     * @param $value
     * @param String $operator
     * @param String $value_type
     * 
     * @return Condition
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
     * @return Condition
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
     * @param Condition $wh
     * 
     * @return Condition
     */
    public function addCondition(Condition $wh)
    {
        $this->Condition_list[] = $wh;
        return $this;
    }
    
    /**
     * @param String $table
     * 
     * @return String
     */
    public function get(String $table_name = "")
    {
        $list = [];
        
        foreach($this->fields as $field)
        {
            if (strlen($field["value"]) > 0)
            {
                $key = $field["field"];

                if ($table_name)
                {
                    $key = $table_name . "." . $key;
                }

                if ($field["op"])
                {
                    $key = $key . " " . $field["op"];
                }

                $list[] = $key . " " . $field["value"];
            }
        }
        
        foreach($this->Condition_list as $wh)
        {
            $list[] = $wh->get($table_name);
        }

        if (empty($list))
        {
            return "";
        }
        
        return "(" . implode($this->op, $list) . ")";
    }
}

