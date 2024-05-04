<?php
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;
use Twig\TwigFilter;

class dbSelectModel extends Model implements ExtensionInterface
{
    public function getFunctions() {
        return [
            new TwigFunction('dbSelect_RealEscape', [$this, 'RealEscape']),
            new TwigFunction('dbSelect_FetchAll', [$this, 'FetchAll']),
            new TwigFunction('dbSelect_RowCount', [$this, 'RowCount']),
            new TwigFunction('dbSelect_TableCount', [$this, 'TableCount']),
            new TwigFunction('dbSelect_TableData', [$this, 'TableData']),
            new TwigFunction('dbSelect_RowData', [$this, 'RowData']),
            new TwigFunction('dbSelect_RowDataLast', [$this, 'RowDataLast']),
            new TwigFunction('dbSelect_RowDataFirst', [$this, 'RowDataFirst']),
        ];
    }
    public function getFilters()
    {
        return [
            new TwigFilter('RealEscape', [$this, 'RealEscape']),
            new TwigFilter('TableCount', [$this, 'TableCount']),
        ];
    }
    public function getTokenParsers()
    {
        return [];
    }
    public function getNodeVisitors()
    {
        return [];
    }
    public function getTests()
    {
        return [];
    }
    public function getOperators()
    {
        return [];
    }

    public function RealEscape($string) {
        $string = str_replace('ㅤ', ' ', $string);
        $string = trim($string);
        $string = htmlspecialchars($string);
        $string = mysqli_real_escape_string($this->db, $string);
        return (strlen($string) > 1) ? $string : null;
    }

    public function GetResult($sql, $params = [])
    {
        $stmt = mysqli_prepare($this->db, $sql);
        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_double($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    public function FetchAll($sql, $params = [])
    {
        $query_sql = $this->GetResult($sql, $params);
        if (!$query_sql) {
            return false;
        }
        $result = mysqli_fetch_all($query_sql, MYSQLI_ASSOC);
        return (!$result || $result === false || (count($result)) <= 0) ? false : $result;
    }

    public function Count($sql)
    {
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_row($result);
        return isset($row[0]) ? $row[0] : 0;
    }

    public function RowCount($table_name = null, $where = null)
    {
        $sql = "SELECT COUNT(*) FROM `$table_name`";
        $sql_operator = ['>=', '<=', '>', '<', '=', '!='];
        if ($where) {
            $operator = $where['operator'] ? $where['operator'] : '=';
            if (!in_array($operator, $sql_operator)) {
                $operator = '=';
            }
            $sql .= " WHERE ";
            $where_new = [];
            foreach ($where as $key => $value) {
                if ($key !== 'operator') {
                    $where_new[$key] = $value;
                }
            }
            foreach ($where_new as $key => $value) {
                $sql .= "`$key` " . $operator . " '$value'";
                if (next($where_new)) {
                    $sql .= " AND ";
                }
            }
        }
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_row($result);
        return isset($row[0]) ? $row[0] : 0;
    }

    public function TableCount($table_name = null)
    {
        return $this->RowCount($table_name);
    }

    public function TableData($table_name = null, $order = 'id', $sort = 'ASC')
    {

        $sql = "SELECT * FROM $table_name ORDER BY $order $sort";
        $query = $this->FetchAll($sql);
        if (isset($query) && count($query) >= 1) {
            return $query;
        } else {
            return false;
        }
    }

    public function RowData($table_name = null, $column_name = null, $column_value = null)
    {
        $sql = "SELECT * FROM $table_name WHERE $column_name = '$column_value' ORDER BY id DESC LIMIT 1";
        $query = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($query);
        return isset($row) ? $row : false;
    }

    public function RowDataLast($table_name = null, $where = null)
    {
        $sql = "SELECT * FROM $table_name $where ORDER BY id DESC LIMIT 1";
        $query = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($query);
        return isset($row) ? $row : false;
    }

    public function RowDataFirst($table_name = null, $where = null)
    {
        $sql = "SELECT * FROM $table_name $where ORDER BY id ASC LIMIT 1";
        $query = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($query);
        return isset($row) ? $row : false;
    }
}