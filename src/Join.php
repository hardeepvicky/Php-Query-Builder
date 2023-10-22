<?php
namespace HardeepVicky\QueryBuilder;

class Join
{
    const INNER = 'INNER JOIN';
    const LEFT = 'LEFT JOIN';
    const OUTER = 'OUTER JOIN';

    protected Table $table;

    protected Condition | NULL $where = NULL;

    protected $join_type, $foreign_field, $field_list = [], $join_list = [], $raw_where_list = [];
    
    /**
     * @param String $join_type
     * @param Table $table
     * @param String $foreign_field
     */
    public function __construct(String $join_type, Table $table, String $foreign_field)
    {
        $this->join_type = trim($join_type);
        $this->table = $table;
        $this->foreign_field = trim($foreign_field);
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
     * @param Join $join
     * 
     * @return Join
     */
    public function join(Join $join)
    {
        $this->join_list[] = $join;

        return $this;
    }

    public function getJoinList()
    {
        return $this->join_list;
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
     * @param String $field
     * @param String $alias
     * 
     * @return Join
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
                $alias = $this->table->getRefName() . QuerySelect::TABLE_FIELD_SEP . $alias;
            }
        }
        else
        {
            if ($prepend_table_alias)
            {
                $alias = $this->table->getRefName() . QuerySelect::TABLE_FIELD_SEP . $field;
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
    
    /**
     * @return Array $field_list
     */
    public function getFields()
    {
        $fields = array();

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
                    $fields[] = $table_alias . "." . $field;
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
        
        return $fields;
    }
   
    /**
     * @param Table $calling_table
     * 
     * @return String
     */
    public function get(Table $calling_table)
    {   
        $q = $this->join_type;
        
        $join_table_alias = "";

        if ($this->table->alias)
        {
            $join_table_alias = $this->table->alias;

            $q .= " " . "`" . $this->table->name . "`" . " AS " . $join_table_alias;
        }
        else
        {
            $join_table_alias = "`" . $this->table->name . "`";

            $q .= " " . $join_table_alias;
        }
        
        $q .= " ON " . $join_table_alias . "." . $this->foreign_field;

        if ($calling_table->alias)
        {
            $q .= " = " . $calling_table->alias . "." . $calling_table->primary_field; 
        }
        else
        {
            $q .= " = " . "`" . $calling_table->name . "`" . "." . $calling_table->primary_field; 
        }
        
        $where_str = "";
        if ($this->where)
        {
            $wh_str = trim($this->where->get($join_table_alias));

            if ($wh_str)
            {
                $where_str = " AND " . $wh_str;
            }
        }

        if (is_array($this->raw_where_list) && !empty($this->raw_where_list))
        {
            $where_str .= " " . implode(" ", $this->raw_where_list);
        }

        $where_str = trim($where_str);

        if ($where_str)
        {
            $q .= " " . $where_str;
        }

        foreach($this->join_list as $join)
        {
            $q .= " " . $join->get($this->table);
        }
        
        return $q;
    }
}
