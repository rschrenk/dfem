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
    private $con;
    private $prefix;

    public function __construct() {
        global $CFG;
        $con = new mysqli($CFG->db['host'],  $CFG->db['user'], $CFG->db['pass']);
        if ($con->connect_error) {
            // redirect to error page.
        }
        $this->prefix = $CFG->db['prefix'];
        $this->con = $con;
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
        $WHERE = $this->condition_sql($conditions);
        $LIMIT = "";
        if ($limitfrom > 0 || $limitnum > 0) {
            $LIMIT = " LIMIT $limitfrom, $limitnum";
        }
        $sql = "SELECT $fields FROM {$this->prefix}_$table $WHERE $LIMIT";
        $rows = $con->query($sql);
        $results = [];
        while ($row = $rows->fetch_object) {
            $results[$row->id] = $row;
        }
        return $results;
    }
    public function update_record($table, $object, $conditions) {

    }
    public function insert_record($table, $object) {

    }
    public function salt($param, $history = 0) {
        global $CFG;
        if ($history > count($CFG->salts)) $history = 0;
        return password_hash($param . $CFG->salts[$history]);
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
