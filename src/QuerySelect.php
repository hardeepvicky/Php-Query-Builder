<?php
namespace HardeepVicky\QueryBuilder;

use Exception;

class QuerySelect
{
    const TABLE_FIELD_SEP = "__";

    protected Table $table;

    protected Condition | NULL $where = NULL;

    protected Condition | NULL $having = NULL;

    protected $field_list = [], $custom_field_list = [], $order_list = [], $group_by_list = [], $raw_where_list = [], $join_list = [];

    protected String $offset = "";

    protected String $limit = "";
    
    /**
     * @param Table $table
     */
    public function __construct(Table $table)
    {
        $this->table = $table;
    }
    
    /**
     * @param Condition $wh
     * 
     * @return QuerySelect
     */
    public function setWhere(Condition $wh)
    {
        $this->where = $wh;

        return $this;
    }
    
    /**     
     * @return Condition
     */
    public function getWhere()
    {
        if (!$this->where)
        {
            $this->where = Condition::init("AND");
        }
        
        return $this->where;
    }

    /**
     * @param String $where_str
     * 
     * @return QuerySelect
     */
    public function addRawWhere(String $where_str)
    {
        $this->raw_where_list[] = $where_str;

        return $this;
    }
    
    /**
     * @param String $field
     * 
     * @param String | NULL $alias
     * 
     * @param bool $prepend_table_alias
     * 
     * @return QuerySelect
     */
    public function field(String $field, String | Null $alias = NULL, bool $prepend_table_alias = false)
    {
        if ($field == "*")
        {
            return $this;
        }

        if ($alias)
        {
            if ($prepend_table_alias)
            {
                $alias = $this->table->getRefName() . static::TABLE_FIELD_SEP . $alias;
            }
        }
        else
        {
            if ($prepend_table_alias)
            {
                $alias = $this->table->getRefName() . static::TABLE_FIELD_SEP . $field;
            }
        }

        if ($alias)
        {
            $this->field_list[] = [$field, $alias];
        }
        else
        {
            $this->field_list[] = $field;
        }

        return $this;
    }

    public function addCustomField(String $field)
    {
        $this->custom_field_list[] = $field;

        return $this;
    }

    public function getCustomFieldList()
    {
        return $this->custom_field_list;
    }

    public function setCustomFieldList(Array $list)
    {
        $this->custom_field_list = $list;

        return $this;
    }

    /**     
     * @return QuerySelect
     */
    public function noField()
    {
        $this->field_list = null;

        return $this;
    }

    /**          
     * @param Array $field_list
     * 
     * @return QuerySelect
     */
    public function setFieldList(Array $field_list)
    {
        $this->field_list = $field_list;

        return $this;
    }
    
    /**               
     * @return Array
     */
    public function getFieldList()
    {
        return $this->field_list;
    }

    

    public function groupBy(String $field)
    {
        $this->group_by_list[] = trim($field);

        return $this;
    }

    public function getGroupByList()
    {
        return $this->group_by_list;
    }

    public function setHaving(Condition $condition)
    {
        $this->having = $condition;

        return $this;
    }

    public function getHaving()
    {
        return $this->having;
    }
    
    /**
     * @param String $direction
     * 
     * @return String $direction
     */
    protected function getOrderDirection(String $direction)
    {
        $direction = strtoupper(trim($direction));

        if ( !in_array($direction, ["ASC", "DESC"]) )
        {
            throw new \Exception("Direction Parameter should be ASC OR DESC");
        }

        return $direction;
    }

    /**
     * @param String $field
     * 
     * @param String $direction
     */
    public function order(String $field, String $direction = "ASC")
    {
        $field = trim($field);

        $this->order_list[$field] = $this->getOrderDirection($direction);

        return $this;
    }

    /**
     * @return Array $order_list
     */
    public function getOrderList()
    {
        return $this->order_list;
    }

    /**
     * @param Array $order_list
     */
    public function setOrderList(Array $order_list)
    {
        $this->order_list = $order_list;

        return $this;
    }

    /**
     * @param Int $limit
     * 
     * @return QuerySelect
     */
    public function setLimit(Int $limit)
    {
        $this->limit = (String) $limit;

        return $this;
    }

    /**
     * @param Int $offset
     * 
     * @return QuerySelect
     */
    public function setOffset(Int $offset)
    {
        $this->offset = (String) $offset;

        return $this;
    }
    
    /**
     * @param Join $join
     * 
     * @return QuerySelect
     */
    public function join(Join $join)
    {
        $this->join_list[] = $join;
        
        return $this;
    }

    /**
     * Main Function return Query
     * 
     * @return String;
     */
    public function get()
    {   
        if ($this->table->alias)
        {
            $table_alias = $this->table->alias;            
        }
        else
        {
            $table_alias = "`" . $this->table->name . "`";
        }
   
        $fields = [];
        
        if (is_array($this->field_list))
        {
            foreach($this->field_list as $field)
            {
                if (is_array($field))
                {
                    $fields[] = $table_alias . "."  . $field[0] . " AS " . $field[1];
                }
                else
                {
                    $fields[] = $table_alias . "."  . $field;
                }
            }

            if (empty($fields))
            {
                $fields[] = "$table_alias.*";
            }
        }

        if (is_array($this->custom_field_list))
        {
            foreach($this->custom_field_list as $field)
            {
                $fields[] = $field;
            }
        }
        
        foreach($this->join_list as $join)
        {
            $fields = array_merge($fields, $join->getFields());
        }
        
        $field_str = "*"; 
        
        if (!empty($fields))
        {
            $field_str = implode(", ", $fields);
        }
        
        $q = "SELECT $field_str";

        if ($this->table->alias)
        {
            $q .= " FROM " . "`" . $this->table->name . "`" . " AS " . $table_alias;
        }
        else
        {
            $q .= " FROM " . "`" . $this->table->name . "`";
        }
        
        foreach($this->join_list as $join)
        {
            $str = $join->get($this->table);
            
            if ($str)
            {
                $q .= " " . $str;
            }
        }
        
        $where_str = "";
        if ($this->where)
        {
            $where_str = $this->where->get();
        }

        if (is_array($this->raw_where_list) && !empty($this->raw_where_list))
        {
            $where_str .= " " . implode(" ", $this->raw_where_list);
        }

        $where_str = trim($where_str);

        if ($where_str)
        {
            $q .= " WHERE " . $where_str;
        }

        $group_str = "";
        
        if (is_array($this->group_by_list) && !empty($this->group_by_list))
        {
            $group_str .= " " . implode(" ", $this->group_by_list);
        }

        $group_str = trim($group_str);

        if ($group_str)
        {
            $q .= " GROUP BY " . $group_str;
        }

        $having_str = "";
        if ($this->having)
        {
            $having_str = $this->having->get();
        }

        $having_str = trim($having_str);

        if ($having_str)
        {
            $q .= " HAVING " . $having_str;
        }
        
        $order_list = [];        
        foreach($this->order_list as $field => $dir)
        {
            $order_list[] = $field . " " . $dir;
        }
        
        if ($order_list)
        {
            $q .= " ORDER BY " . implode(", ", $order_list);
        }

        if ($this->limit)
        {
            $q .= " LIMIT " . $this->limit;
        }

        if ($this->offset)
        {
            $q .= " OFFSET " . $this->offset;
        }
        
        return $q;
    }
    
}