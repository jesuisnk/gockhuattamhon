<?php

class dbUpdateModel extends Model
{
    private dbSelectModel $dbSelect;

    function __construct()
    {
        parent::__construct();
        $this->dbSelect = $this->load->model('dbSelect');
    }

    public function RowData($table_name = null, $row = array(), $where = array())
    {
        $sql = "UPDATE $table_name SET ";
        foreach ($row as $key => $value) {
            $sql .= "`$key` = '$value', ";
        }
        $sql = substr($sql, 0, -2);
        
        $where_column_name = array_keys($where)[0];
        $where_column_value = array_values($where)[0];
        $sql .= " WHERE `$where_column_name` = '$where_column_value'";

        $query_sql = mysqli_query($this->db, $sql);
        if (!isset($query_sql)) {
            return false;
        }
    }
}