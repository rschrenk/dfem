<?php
// This file is part of the DFEM tool
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This tool is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this tool. If not, see <http://www.gnu.org/licenses/>.

/**
 * @copyright  2021 Robert Schrenk (www.schrenk.cc)
 * @author     Robert Schrenk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('DFEM_INTERNAL')) die();

class dfem_db {
    private $pdo;
    private $prefix;

    public function __construct() {
        global $CFG;

        $this->prefix = $CFG->db['prefix'];
        $dsn = "mysql:host={$CFG->db['host']};dbname={$CFG->db['db']};charset=utf8mb4";
        $options = [
            PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        ];
        try {
            $this->pdo = new PDO($dsn, $CFG->db['user'], $CFG->db['pass'], $options);
        } catch (Exception $e) {
            error_log($e->getMessage());
            exit('Database not available!');
            // @todo redirect to error page.
        }
    }

    public function get_record($table, $conditions = [], $fields = '*') {
        $results = array_values($this->get_records($table, $conditions, $fields, 0, 2));
        if (count($results) > 1) {
            // throw error?
        }
        if (count($results) == 1) {
            // return first occurrence.
            return $results[0];
        }
    }
    public function get_records($table, $conditions, $fields, $limitfrom = 0, $limitnum = 0) {
        $cfields = []; $values = [];
        foreach ($conditions as $cfield => $val) {
            $cfields[] = $cfield . " = ?";
            $values[] = $val;
        }
        if (count($cfields) > 0) {
            $WHERE = " WHERE " . implode(" AND ", $cfields);
        }

        $LIMIT = "";
        if ($limitfrom > 0 || $limitnum > 0) {
            $LIMIT = " LIMIT $limitfrom, $limitnum";
        }
        $sql = "SELECT $fields FROM {$this->prefix}$table $WHERE $LIMIT";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);
            $arr = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $arr;
        } catch(PDOException $e) {
            echo "DB-Error: " . $e->getMessage();
        }

        return $results;
    }
    public function get_records_sql($sql, $params = []) {
        $sql = str_replace('{prefix}', $this->prefix, $sql);
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $arr = $stmt->fetchAll(PDO::FETCH_OBJ);
            return $arr;
        } catch(PDOException $e) {
            echo "DB-Error: " . $e->getMessage();
        }
        return $results;
    }
    public function update_record($table, $object) {
        $sql = "UPDATE {$this->prefix}$table SET ";
        $cfields = []; $values = [];
        foreach ($object as $cfield => $val) {
            $cfields[] = "$cfield = ?";
            $values[] = $val;
        }
        $sql .= implode(", ", $cfields);
        $sql .= " WHERE id = ?";
        $values[] = $object->id;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);
        } catch(PDOException $e) {
            echo "DB-Error: " . $e->getMessage();
        }

    }
    public function insert_record($table, $object) {
        $sql = "INSERT INTO {$this->prefix}$table ";
        $fields = []; $qms = []; $values = [];
        foreach ($object as $field => $val) {
            $fields[] = $field;
            $values[] = $val;
            $qms[] = '?';
        }
        $sql .= "(" . implode(", ", $fields) . ") VALUES (" . implode(", ", $qms) . ")";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    public function salt($param, $history = 0) {
        global $CFG;
        if ($history > count($CFG->salts)) {
            $history = 0;
        }
        $salted = password_hash($param, PASSWORD_DEFAULT, array('salt' => $CFG->salts[$history]));
        return $salted;
    }

    private function condition_sql($conditions) {
        $arconditions = [];
        foreach ($conditions as $field => $condition) {
            $arconditions[] = $field . "=\"" . $this->con->real_escape_string($condition) . "\"";
        }
        if (count($arconditions) > 0) {
            return " WHERE " . implode(" AND ", $arconditions);
        } else {
            return "";
        }
    }
}
