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
        $op = strtoupper(trim($op));
        if ($op)
        {
            $this->op = " " . strtoupper(trim($op)) . " ";
        }
        else
        {
            $this->op = "";
        }
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

        $will_condition_apply = true;

        switch(gettype($value))
        {
            case "integer":
            case "float":                
                break;

            case "boolean":
                $value = (int) $value;
            break;

            case "string":
                $value = trim($value);

                $null_present = strpos(strtoupper($value), "NULL") >= 0;

                if (!$null_present)
                {
                    $value = $this->_parseStringValue($value);
                    if ($value === false)
                    {
                        $will_condition_apply = false;
                    }
                }
            break;
        
            
        
            case "array":
                if (empty($value))
                {
                    $will_condition_apply = false;
                    break;
                }

                $arr = $value;

                foreach($arr as $k => $v)
                {
                    switch(gettype($v))
                    {
                        case "integer":
                        case "float":                
                            break;
                
                        case "string":
                            $arr[$k] = $v = $this->_parseStringValue($v);
                            if ($v === false)
                            {
                                unset($arr[$k]);
                            }
                        break;
                    }
                }

                $value = "(" . implode(",", $arr) . ")";
            break;
        
            case "NULL":
                $value = "IS NULL";
                $operator = "";
            break;
        
            default:
                throw new \Exception("Query Builder : Un-Supported value type :" . gettype($value) );
        }
                
        if ($will_condition_apply)
        {
            $this->fields[] = array(
                "field" => trim($field),
                "value" => $value,
                "op" => trim($operator)
            );
        }
        
        return $this;
    }

    private function _parseStringValue($value, $value_type = "string")
    {
        if (is_string($value) && strlen($value) > 0)
        {
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

            return $value;
        }

        return false;
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

