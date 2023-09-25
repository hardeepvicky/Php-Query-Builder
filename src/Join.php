<?php
namespace HardeepVicky\QueryBuilder;

class Join
{
    const INNER = 'INNER JOIN';
    const LEFT = 'LEFT JOIN';
    const OUTER = 'OUTER JOIN';

    protected Table $table;

    protected $join_type, $foreign_field, $fields = array(), $joins = array();
    
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
     * @param Join $join
     * 
     * @return Join
     */
    public function join(Join $join)
    {
        $this->joins[] = $join;
        return $this;
    }
    
     /**     
     * @return QuerySelect
     */
    public function noField()
    {
        $this->fields = null;
        return $this;
    }
    
    /**
     * @param String $field
     * @param String $alias
     * 
     * @return Join
     */
    public function field(String $field, String $alias = "")
    {
        if (!$alias)
        {
            $alias = $field;
        }
        
        $this->fields[$alias] = $field;

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
                    $fields[] = $table_alias . "." . $field;
                }
            }
        }
        
        foreach($this->joins as $join)
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
        
        foreach($this->joins as $join)
        {
            $q .= " " . $join->get($this->table);
        }
        
        return $q;
    }
}
