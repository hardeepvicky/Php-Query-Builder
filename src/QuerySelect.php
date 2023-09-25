<?php
namespace HardeepVicky\QueryBuilder;

use Exception;

class QuerySelect
{
    protected Table $table;

    protected $field_list = [], $order_list = [], $where = null, $raw_where_list = [], $join_list = [];

    /**
     * Sepretor for table alias and field alias
     */
    protected String $sep = "__";

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
     * @param Where $wh
     * 
     * @return QuerySelect
     */
    public function setWhere(Where $wh)
    {
        $this->where = $wh;

        return $this;
    }
    
    /**     
     * @return Where
     */
    public function getWhere()
    {
        if (!$this->where)
        {
            $this->where = Where::init("AND");
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
        if ($alias)
        {
            if ($prepend_table_alias)
            {
                $alias = $this->table->getRefName() . $this->sep . $alias;
            }

            $this->field_list[] = [$field, $alias];
        }
        else
        {
            $this->field_list[] = $field;
        }

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
    public function orderField(String $field, String $direction = "ASC")
    {
        $this->order_list[$this->table->getRefName() . "." . $field] = $this->getOrderDirection($direction);

        return $this;
    }

    /**
     * @param String $alias
     * 
     * @param String $direction
     */
    public function orderAlias(String $alias, String $direction = "ASC")
    {
        $this->order_list[$this->table->getRefName() . "__" . $alias] = $this->getOrderDirection($direction);

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
                    $fields[] = $table_alias . "." . $field[0] . " AS " . $field[1];
                }
                else
                {
                    $fields[] = $table_alias . "." . $field[0];
                }
            }

            if (empty($fields))
            {
                $fields[] = "$table_alias.*";
            }
        }
        
        foreach($this->join_list as $join)
        {
            $fields = array_merge($fields, $join->getFields());
        }
        
        $fields = implode(", ", $fields);
        
        $q = "SELECT $fields";

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

        if ($this->raw_where_list)
        {
            $where_str .= " " . implode(" ", $this->raw_where_list);
        }

        if ($where_str)
        {
            $q .= " WHERE " . trim($where_str);
        }
        
        $order_list = [];        
        foreach($this->order_list as $field_or_alias => $dir)
        {
            $order_list[] = $field_or_alias . " " . $dir;
        }
        
        if ($order_list)
        {
            $q .= " ORDER BY " . implode(", ", $order_list);
        }

        if ($this->limit)
        {
            $q .= " LIMIT " . $this->limit;
        }

        if ($this->limit)
        {
            $q .= " OFFSET " . $this->limit;
        }
        
        return $q;
    }
    
}