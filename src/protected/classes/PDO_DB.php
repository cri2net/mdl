<?php

class PDO_DB
{
    use Singleton;

    private static $pdo = null;

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        if (self::$pdo == null) {
            self::$pdo = new PDO(
                "mysql:host=". DB_HOST .";dbname=". DB_NAME .";charset=utf8",
                DB_USER,
                DB_PASSWORD,
                array(
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                )
            );
        }
    }

    public static function getPDO()
    {
        $instance = self::getInstance();
        return $instance::$pdo;
    }

    /**
     * Preform SQL insert operation. 
     * @param array $data - associated array of data, key name should be as field name in the DB table.
     * @param string $table - name of the table, where data should be inserted.
     * @param boolean $ignore - use INSERT or INSERT IGNORE statement. OPTIONAL
     */
    public static function insert(array $data, $table, $ignore = false)
    {
        if (!empty($data)) {
            $pdo = self::getPDO();
            $str = self::arrayToString($data);
            if ($ignore) {
                $pdo->query("INSERT IGNORE INTO `$table` SET $str");
            } else {
                $pdo->query("INSERT INTO `$table` SET $str");
            }
            return $pdo->lastInsertId();
        }
    }
    
    /**
     * Preform SQL update operation. 
     * @param array $data - associated array of data, key name should be as field name in the DB table.
     * @param string $table - name of the table, where data should be inserted.
     * @param string $idColumnName - name of the column for where clause.
     * @param string $id - value of the idColumnName for where clause.
     */
    public static function update(array $data, $table, $id, $idColumnName = 'id')
    {
        if (!empty($data)) {
            $pdo = self::getPDO();
            $str = self::arrayToString($data);
            $stm = $pdo->prepare("UPDATE `$table` SET $str WHERE `$idColumnName`=? LIMIT 1");
            $stm->execute(array($id));
        }
    }
    
    /**
     * Preform SQL update operation. 
     * @param array $data - associated array of data, key name should be as field name in the DB table.
     * @param string $table - name of the table, where data should be inserted.
     * @param string $where - SQL where clause.
     */
    public static function updateWithWhere(array $data, $table, $where)
    {
        if (!empty($data)) {
            $pdo = self::getPDO();
            $str = self::arrayToString($data);
            $pdo->query("UPDATE `$table` SET $str WHERE $where");
        }
    }
    
    /**
     *  Return last inserted id.
     */
    public static function lastInsertID()
    {
        $pdo = self::getPDO();
        return $pdo->lastInsertId();
    }
    
    /**
     *
     * Return concatenateed string from given array. Method are using in the insert and updated methods.
     * @param array $data - associated array of data.
     * @return string
     */
    private static function arrayToString(array $data)
    {
        $pdo = self::getPDO();
        $str = '';
        foreach ($data as $key=>$value) {
            $value = $pdo->quote($value);
            $str .= "`$key` = $value, ";
        }
        return trim($str, ', ');
    }

    /**
     *  Почти что псевдоним table_list
     */
    public static function first()
    {
        $instance = self::getInstance();
        $args = func_get_args();

        // LIMIT стоит 4-м аргументов, перед ним два необязательных. Если он не указан, ставим ему '1'
        if (!isset($args[1])) {
            $args[1] = null;
        }
        if (!isset($args[2])) {
            $args[2] = null;
        }
        if (!isset($args[3])) {
            $args[3] = '1';
        }

        $result = call_user_func_array([$instance, 'table_list'], $args);
        if (count($result) == 0) {
            return null;
        }
        return $result[0];
    }

    public static function table_list($table, $where = null, $order = null, $limit = null)
    {
        $pdo = self::getPDO();

        $query = "SELECT * FROM `$table`";
        if ($where != null) {
            $query .= " WHERE $where";
        }
        if ($order != null) {
            $query .= " ORDER BY $order";
        }
        if ($limit != null) {
            $query .= " LIMIT $limit";
        }
        
        $stm = $pdo->query($query);
        return $stm->fetchAll();
    }
    
    public static function row_by_id($table, $id, $idColumnName = 'id')
    {
        $pdo = self::getPDO();

        $stm = $pdo->prepare("SELECT * FROM `$table` WHERE `$idColumnName`=? LIMIT 1");
        $stm->execute(array($id));
        $record = $stm->fetch();
        
        if ($record === false) {
            return null;
        }

        return $record;
    }
    
    public static function query($query)
    {
        $pdo = self::getPDO();
        return $pdo->query($query);
    }

    public static function del_id($table, $id, $is_virtual = false, $del_column = 'is_del', $idColumnName = 'id')
    {
        $pdo = self::getPDO();
        $query = $is_virtual
            ? "UPDATE $table SET `$del_column`=1 WHERE `$idColumnName`=? LIMIT 1"
            : "DELETE FROM $table WHERE `$idColumnName`=? LIMIT 1";
        
        $stm = $pdo->prepare($query);
        $stm->execute(array($id));
    }

    public static function rebuild_pos($table, $where = null, $order = null)
    {
        $pdo = self::getPDO();
        $qOrder = ($order == null) ? "pos ASC, id ASC":$order;
        $qWhere = ($where == null) ? "":"WHERE $where";

        $stm = $pdo->query("SELECT * FROM $table $qWhere ORDER BY $qOrder");
        $arr = $stm->fetchAll();

        $stm = $pdo->prepare("UPDATE $table SET `pos`=? WHERE `id`=? LIMIT 1");
        for ($i=0; $i < count($arr); $i++) {
            $stm->execute(array($i+1, $arr[$i]['id']));
        }
    }
    
    public static function change_pos_from_to($table, $where, $posFrom, $posTo, $order = null, $column = 'pos')
    {
        $pdo = self::getPDO();
        $posFrom = (int)$posFrom;
        $posTo = (int)$posTo;
        if ($posFrom == $posTo || $posFrom == 0 || $posTo == 0) {
            return false;
        }

        $qW = ($where == null)? "" : "$where AND";
        
        $stm = $pdo->query("SELECT id FROM $table WHERE $qW `$column`=$posFrom LIMIT 1");
        $row = $stm->fetch();
        
        if ($row === false) {
            return false;
        }
        
        $id = $row['id'];
        
        if ($posFrom > $posTo) {
            $pdo->query("UPDATE $table SET `$column` = `$column` + 1 WHERE $qW `$column` >= $posTo AND `$column` < $posFrom");
        } else {
            $pdo->query("UPDATE $table SET `$column` = `$column` - 1 WHERE $qW `$column` > $posFrom AND `$column` <= $posTo");
        }
            
        $pdo->query("UPDATE $table SET `$column`=$posTo WHERE id=$id LIMIT 1");
        return true;
    }

    public static function change_pos($table, $where, $id, $dir, $order = null)
    {
        $pdo = self::getPDO();

        $id = (int)$id;
        self::rebuild_pos($table, $where, $order);
        $qWhere = ($where == null) ? '' : "AND $where";
        
        switch ($dir) {
            case 'dup':
                $pdo->query("UPDATE `$table` SET `pos`=0 WHERE `id`='$id'");
                break;

            case 'ddown':
                $pos = self::max_pos("$table", $where) + 1;
                $pdo->query("UPDATE `$table` SET `pos`='$pos' WHERE `id`='$id'");
                break;

            case 'up':
                $item1 = self::row_by_id("$table", $id);
                $pos1 = $item1['pos'];
                $pos2 = $pos1 - 1;
                $stm = $pdo->query("SELECT * FROM `$table` WHERE `pos`='$pos2' $qWhere LIMIT 1");

                $item2 = $stm->fetch();
                
                if ($item2 !== false) {
                    $pdo->query("UPDATE $table SET `pos`='$pos2' WHERE `id`='{$item1['id']}' LIMIT 1");
                    $pdo->query("UPDATE $table SET `pos`='$pos1' WHERE `id`='{$item2['id']}' LIMIT 1");
                }
                break;
            
            case 'down':
                $item1 = self::row_by_id("$table", $id);
                
                $pos1 = $item1['pos'];
                $pos2 = $pos1 + 1;

                $stm = $pdo->query("SELECT * FROM `$table` WHERE `pos`='$pos2' $qWhere LIMIT 1");
                $item2 = $stm->fetch();
                
                if ($item2 !== false) {
                    $pdo->query("UPDATE $table SET `pos`='$pos2' WHERE `id`='{$item1['id']}' LIMIT 1");
                    $pdo->query("UPDATE $table SET `pos`='$pos1' WHERE `id`='{$item2['id']}' LIMIT 1");
                }
                break;
        }
    }
    
    public static function max_pos($table, $where = null, $column = 'pos')
    {
        $pdo = self::getPDO();
        $qWhere = ($where == null) ? '' : "WHERE $where";
        $result = $pdo->query("SELECT MAX($column) FROM $table $qWhere");
        return (int)$result->fetchColumn();
    }
    
    public static function reset_pos($table, $order = 'id ASC', $column = 'pos')
    {
        $pdo = self::getPDO();

        $result = $pdo->query("SELECT * FROM `$table` ORDER BY $order");
        $arr = $result->fetchAll();

        for ($i=0; $i < count($arr); $i++) {
            $pdo->query("UPDATE `$table` SET `$column`='".(++$c)."' WHERE id='{$arr[$i]['id']}' LIMIT 1");
        }
    }
}
