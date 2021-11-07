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

class dfem_helper {

    public static function calc_result($tool) {
        $cols = [ 'control', 'intention', 'protection', 'objective', 'fortune'];
        foreach ($cols as $col) {
            $tool->result->{$col} = null;
        }
        $model = [
            'origin1'       => [ -4, -5, -5],
            'origin2'       => [ -2, -1 ],
            'origin3'       => [  3,  2,  2,  1],
            'origin4'       => [  3,  2,  2,  1],
            'granularity1'  => [  0, -1,  1],
            'granularity2'  => [  0,  2,  1],
            'granularity3'  => [  0,  2,  1],
            'transparency1' => [ -1, -1, -2],
            'transparency2' => [],
            'transparency3' => [  2,  0,  2,  0,  1],
            'transparency4' => [ -1, -1],
            'transparency5' => [],
            'transparency6' => [  2,  0,  2,  0,  1],
            'goals1'        => [  0, -1,  0, -3],
            'goals2'        => [  0,  0,  0, -2],
            'goals3'        => [  0,  0,  0,  1],
            'goals4'        => [  1,  1,  2,  1,  2],
            'goals5'        => [  2,  1,  2,  1,  4],
            'location1'     => [ -4,   0, -4],
            'location2'     => [ -2],
            'location3'     => [ -2,  0,  0,  0,  2],
            'location4'     => [  5,  0,  4,  0,  4],
            'location5'     => [  0,  0,  4],
            'means1'        => [ -1,  0, -1],
            'means2'        => [ -1,  0, -1],
            'means3'        => [ -1,  0, -1],
            'means4'        => [ -1,  0, -1],
            'means5'        => [  2,  0,  2,  0,  1],
            'benefits1'     => [  0,  0,  0,  0, -1],
            'benefits2'     => [  0,  0,  0,  0, -1],
            'benefits3'     => [  0,  0,  0,  0, -3],
            'benefits4'     => [  0,  0,  0,  0,  5],
        ];
        foreach ($model as $field => $calcs) {
            for ($a = 0; $a < count($calcs); $a++) {
                $col = $cols[$a];
                if (!empty($tool->rating->{$field}) && !empty($calcs[$a])) {
                    $tool->result->{$col} += $calcs[$a];
                }
            }
        }
        return $tool;
    }

    public static function calc_result_classes($result) {
        global $CFG;
        $cols = [ 'control', 'intention', 'protection', 'objective', 'fortune'];
        $resultclasses = [];
        foreach ($cols as $col) {
            $class = '';
            switch ($col) {
                case 'control':
                    if ($result->$col <= -7) { $class = 'A'; }
                    if ($result->$col >= -6 && $result->$col <= 6) { $class = 'B'; }
                    if ($result->$col >=  7) { $class = 'C'; }
                break;
                case 'intention':
                    if ($result->$col <= -4) { $class = 'A'; }
                    if ($result->$col >= -3 && $result->$col <= 3) { $class = 'B'; }
                    if ($result->$col >=  4) { $class = 'C'; }
                break;
                case 'protection':
                    if ($result->$col <= -2) { $class = 'A'; }
                    if ($result->$col >= -1 && $result->$col <= 12) { $class = 'B'; }
                    if ($result->$col >=  13) { $class = 'C'; }
                break;
                case 'objective':
                    if ($result->$col <= -2) { $class = 'A'; }
                    if ($result->$col >= -1 && $result->$col <= 2) { $class = 'B'; }
                    if ($result->$col >=  3) { $class = 'C'; }
                break;
                case 'fortune':
                    if ($result->$col <=  3) { $class = 'A'; }
                    if ($result->$col >=  4 && $result->$col <= 12) { $class = 'B'; }
                    if ($result->$col >=  13) { $class = 'C'; }
                break;
            }

            $resultclasses[] = (object) [
                'class' => $class,
                'icon' => $CFG->wwwroot . "/pix/traffic-light_$class.png",
                'label' => get_string($col, "results"),
                'text' => get_string("{$col}_{$class}", "results"),
            ];
        }

        return $resultclasses;
    }

    public static function calc_result_mean_classes($estimation) {
        $result = self::get_mean_data($estimation->toolid, $estimation->id);
        if (!empty($result->id)) {
            return self::calc_result_classes($result);
        }
    }

    public static function get_fieldtypes() {
        return [
            'origin' => [
                'origin1', 'origin2', 'origin3', 'origin4'
            ],
            'granularity' => [
                'granularity1', 'granularity2', 'granularity3'
            ],
            'transparency' => [
                'transparency1', 'transparency2', 'transparency3',
                'transparency4', 'transparency5', 'transparency6'
            ],
            'goals' => [
                'goals1', 'goals2', 'goals3', 'goals4', 'goals5'
            ],
            'location' => [
                'location1', 'location2', 'location3', 'location4', 'location5'
            ],
            'means' => [
                'means1', 'means2', 'means3', 'means4', 'means5'
            ],
            'benefits' => [
                'benefits1', 'benefits2', 'benefits3', 'benefits4'
            ],
        ];
    }

    public static function get_formfields($tool) {
        foreach (self::get_fieldtypes() as $fieldtype => $fields) {
            foreach ($fields as $field) {
                $tool->rating->{$field} = !empty(retrieve($field)) ? 1 : null;
            }
        }
        return $tool;
    }

    public static function get_mean_data($toolid, $excludeestimationid = 0) {
        global $DB;
        $cols = [ 'control', 'intention', 'protection', 'objective', 'fortune'];
        $avgcols = [];
        foreach ($cols as $col) {
            $avgcols[] = "AVG($col) AS $col";
        }
        $avgcols = implode(",", $avgcols);
        $sql = "SELECT e.toolid as id, $avgcols
                    FROM {prefix}results r, {prefix}estimations e
                    WHERE r.estimationid = e.id
                        AND e.toolid = ?
                        AND r.estimationid <> ?
                    GROUP BY e.toolid";
        $params = [ $toolid, $excludeestimationid ];

        $avg = $DB->get_record_sql($sql, $params);
        return $avg;
    }

    /**
     * Create a data-array for the use of chart.js.
     * @param type either for my own result ('mine'), mean results ('mean'), 'minimum' or 'maximum'
     * @param result the result object to gather this data from.
     * @param estimation the estimation this is for (for type 'mean').
     */
    public static function get_resultdata($type, $result, $estimation = null) {
        global $DB;
        if ($type == 'maximum') {
            return implode(",", [  20,  10,  25,  5, 20]);
        }
        if ($type == 'minimum') {
            return implode(",", [ -20, -10, -15, -5, -5]);
        }
        $cols = [ 'control', 'intention', 'protection', 'objective', 'fortune'];
        $resultdata = [];
        switch ($type) {
            case 'mine':
                foreach ($cols as $col) {
                    $resultdata[] = $result->$col;
                }
            break;
            case 'mean':
                $avg = self::get_mean_data($estimation->toolid, $estimation->id);
                if (!empty($avg->id)) {
                    foreach ($cols as $col) {
                        $resultdata[] = round($avg->{$col});
                    }
                }
            break;
        }
        return implode(",", $resultdata);
    }

    public static function store_estimation($tool) {
        global $DB;
        if (empty($tool->estimation) || empty($tool->estimation->id)) {
            $tool->estimation = (object) [
                'authid' => $_SESSION['authid'],
                'toolid' => $tool->id,
                'timecreated' => time(),
                'timemodified' => time(),
            ];
            $tool->estimation->id = $DB->insert_record('estimations', $tool->estimation);
        } else {
            $tool->estimation->timemodified = time();
            $DB->update_record('estimations', $tool->estimation);
        }
        $tool = self::store_rating($tool);
        return $tool;
    }

    private static function store_rating($tool) {
        global $DB;
        if (empty($tool->rating) || empty($tool->rating->id)) {
            $tool->rating = (object) [
                'estimationid' => $tool->estimation->id,
            ];
            $tool->rating->id = $DB->insert_record('ratings', $tool->rating);
        }
        $tool = self::get_formfields($tool);
        $DB->update_record('ratings', $tool->rating);
        $tool = self::store_result($tool);
        return $tool;
    }

    private static function store_result($tool) {
        global $DB;
        if (empty($tool->result) || empty($tool->result->id)) {
            $tool->result = (object) [
                'estimationid' => $tool->estimation->id,
                'control' => NULL,
                'intention' => NULL,
                'protection' => NULL,
                'objective' => NULL,
                'fortune' => NULL,
            ];
            $DB->insert_record('results', $tool->result);
        }

        $tool = self::calc_result($tool);
        $DB->update_record('results', $tool->result);
        return $tool;
    }
}
