<?php

/*
 * @package         HyperSite CMS
 * @file            class.dbal.php
 * @file_desc       class for dealing with MySQL database connections.
 * @author          Ryan Morrison
 * @website         -
 * @copyright       (c) 2019 HyperSite CMS
 * @license         http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/* Check if we are in CMS otherwise exit code. */
if (!defined('IN_HSCMS')) {
    exit;
}

/* Main Code here */

class dbal {

    public $connection, $query_result, $error_msg;
    public $persistency, $error = false;
    public $user, $server, $dbname = '';

    function __construct($host = 'localhost', $user = '', $pass = '', $dbname = null, $port = 3306, $persistency = false) {
        $this->server = $host;
        $this->user = $user;
        $this->dbname = $dbname;
        $this->persistency = (version_compare(PHP_VERSION, '5.3.0', '>=')) ? $persistency : false;
        $this->server = ($this->persistency) ? 'p:' . $this->server : $this->server;

        $this->connection = new mysqli($this->server, $this->user, $pass, $this->dbname, $port);
        if ($this->connection->connect_error) {
            $this->error = true;
            $this->error_msg = $this->connection->connect_error;
            return false;
        }
        return $this->connection;
    }

    function query($sql = '') {
        if ($sql != '') {
            $this->query_result = $this->connection->query($sql);
        } else {
            $this->query_result = false;
        }
        return $this->query_result;
    }

    function fetchrow($query_id = false) {
        if ($query_id == false) {
            $query_id = $this->query_result;
        }
        if ($query_id !== false) {
            $result = $query_id->fetch_assoc();
            return ($result !== null) ? $result : false;
        }
        return false;
    }

    function fetchall($query_id = false) {
        if ($query_id == false) {
            $query_id = $this->query_result;
        }
        if ($query_id !== false) {
            $result = array();
            while ($row = $this->fetchrow($query_id)) {
                $result[] = $row;
            }
            return $result;
        }
        return false;
    }

    function freeresult($query_id = false) {
        if ($query_id == false) {
            $query_id = $this->query_result;
        }
        if ($query_id !== false) {
            $query_id->free_result();
            return true;
        }
        return false;
    }

    function clean($string = '') {
        if ($string != '') {
            return $this->connection->real_escape_string($string);
        }
        return false;
    }

    function close() {
        return $this->connection->close();
    }

    function build_query($type = '', $table = '', $assoc_array = false, $where_array = false) {
        if ($type !== '' && $table !== '') {
            if ($type == 'create') {
                if (!is_array($assoc_array)) {
                    return false;
                }
                $query = "CREATE TABLE " . $table . " (";
                $P = 0;
                foreach ($assoc_array as $column => $fields) {
                    $query .= $column . " " . $fields['type'];
                    if ($fields['type'] == 'INT' || $fields['type'] == 'VARCHAR') {
                        $query .= "({$fields['length']})";
                    }
                    if ($fields['type'] == 'INT') {
                        $query .= " UNSIGNED";
                    }
                    if ($fields['auto_increment'] == true) {
                        $query .= " AUTO_INCREMENT";
                    }
                    if ($fields['primary'] == true) {
                        $query .= " PRIMARY KEY";
                    }
                    if ($fields['allow_null'] == false) {
                        $query .= " NOT NULL";
                    }
                    if (isset($fields['default'])) {
                        $query .= " DEFAULT '{$fields['default']}'";
                    }
                    if (++$P < count($assoc_array)) {
                        $query .= ",";
                    }
                }
                $query .= ");";
                return $query;
            }
            if ($type == 'insert') {
                if (!is_array($assoc_array)) {
                    return false;
                }
                $query = "INSERT INTO " . $table . " (";
                $fields = array_keys($assoc_array);
                $values = array_values($assoc_array);
                for ($x = 0; isset($fields[$x]); $x++) {
                    if ($x > 0) {
                        $query .= ", ";
                    }
                    $query .= '`' . $fields[$x] . '`';
                }
                $query .= ") VALUES (";
                for ($x = 0; isset($values[$x]); $x++) {
                    if ($x > 0) {
                        $query .= ",";
                    }
                    if (is_int($values[$x])) {
                        $query .= $values[$x];
                    } else {
                        $query .= "'{$values[$x]}'";
                    }
                }
                $query .= ");";
                return $query;
            } elseif ($type == 'delete') {
                if (is_array($where_array) && !empty($where_array)) {
                    $query = "DELETE FROM " . $table . " WHERE ";
                    $keys = array_keys($where_array);
                    $vals = array_values($where_array);
                    for ($x = 0; isset($keys[$x]); $x++) {
                        if ($x > 0)
                            $query .= "AND ";
                        $query .= $keys[$x] . "=" . (is_string($vals[$x]) ? "'{$vals[$x]}'" : $vals[$x]);
                    }
                    $query .= ";";
                    return $query;
                }
                return false;
            }
            elseif ($type == 'update') {
                $query = "UPDATE " . $table . " SET ";
                $fields = array_keys($assoc_array);
                $values = array_values($assoc_array);
                for ($x = 0; isset($fields[$x]); $x++) {
                    if ($x > 0)
                        $query .= ", ";
                    $query .= $fields[$x] . "=" . (is_string($values[$x]) ? "'{$values[$x]}'" : $values[$x]);
                }
                if (is_array($where_array) && !empty($where_array)) {
                    $query .= " WHERE ";
                    $keys = array_keys($where_array);
                    $vals = array_values($where_array);
                    for ($x = 0; isset($keys[$x]); $x++) {
                        if ($x > 0)
                            $query .= "AND ";
                        $query .= $keys[$x] . "=" . (is_string($vals[$x]) ? "'{$vals[$x]}'" : $vals[$x]);
                    }
                }
                $query .= ";";
                return $query;
            } else
                return false;
        }
        return false;
    }

}
