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
    private static $cols = [
                        'uncontrollability', 'involuntariness', 'vulnerability',
                        'disparity', 'endangerment'
                    ];
    private static $model = [
                        'origin1'       => [ -4, -6, -4],
                        'origin2'       => [  0, -5 ],
                        'origin3'       => [  0,  5,  2,  1],
                        'origin4'       => [  0,  5,  2,  1],

                        'granularity1'  => [  0,  2,  1],
                        'granularity2'  => [  0,  2,  1],
                        'granularity3'  => [  0,  3,  5],

                        'transparency1' => [ -1, -1, -1],
                        'transparency2' => [  2,  0,  2,  0,  2],
                        'transparency3' => [ -1, -1],
                        'transparency4' => [  2,  0,  2,  0,  2],

                        'goals1'        => [  0, -1,  0, -2],
                        'goals2'        => [  0, -1,  0, -2],
                        'goals3'        => [  0,  0,  2,  6,  2],
                        'goals4'        => [  2,  0,  2,  3,  2],
                        'goals5'        => [  4,  4,  3,  3,  4],

                        'location1'     => [ -4,  0, -4,  0, -4],
                        'location2'     => [ -2,  0, -2,  0, -2],
                        'location3'     => [ -2,  0,  0,  0, -1],
                        'location4'     => [  6,  0,  6,  0,  6],

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

    public static function calc_result($tool) {
        foreach (self::$cols as $col) {
            $tool->result->{$col} = null;
        }
        $mins = [];
        $maxs = [];
        foreach (self::$model as $field => $calcs) {
            for ($a = 0; $a < count($calcs); $a++) {
                if ($calcs[$a] < 0) $mins[$a] += $calcs[$a];
                if ($calcs[$a] > 0) $maxs[$a] += $calcs[$a];
            }
        }

        foreach (self::$model as $field => $calcs) {
            for ($a = 0; $a < count($calcs); $a++) {
                $col = self::$cols[$a];
                if (!empty($tool->rating->{$field}) && !empty($calcs[$a])) {
                    $tool->result->{$col} += $calcs[$a];
                }
            }
        }
        for ($a = 0; $a < count(self::$cols); $a++) {
            $col = self::$cols[$a];
            $range = $maxs[$a] - $mins[$a];
            $v = $tool->result->{$col} - $mins[$a];
            $tool->result->{$col} = round($v / $range * 100);
        }
        return $tool;
    }

    public static function calc_result_classes($result) {
        global $CFG;
        $resultclasses = [];
        foreach (self::$cols as $col) {
            $class = ($result->$col < 34) ? 'A' : (($result->$col < 67) ? 'B' : 'C');

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
                'transparency4', /* 'transparency5', 'transparency6' */
            ],
            'goals' => [
                'goals1', 'goals2', 'goals3', 'goals4', 'goals5'
            ],
            'location' => [
                'location1', 'location2', 'location3', 'location4', /* 'location5' */
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
        $avgcols = [];
        foreach (self::$cols as $col) {
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
     * @param type either for my own result ('mine'), mean results ('mean'), 'labels'
     * @param result the result object to gather this data from.
     * @param estimation the estimation this is for (for type 'mean').
     */
    public static function get_resultdata($type, $result = null, $estimation = null) {
        global $DB;
        if ($type == 'labels') {
            $labels = [];
            foreach (self::$cols as $col) {
                $labels[] = "'" . get_string($col, 'results') . "'";
            }
            return implode(",",$labels);
        }
        $resultdata = [];
        switch ($type) {
            case 'mine':
                foreach (self::$cols as $col) {
                    $resultdata[] = $result->$col;
                }
            break;
            case 'mean':
                $avg = self::get_mean_data($estimation->toolid, $estimation->id);
                if (!empty($avg->id)) {
                    foreach (self::$cols as $col) {
                        $resultdata[] = round($avg->{$col});
                    }
                }
            break;
        }
        return implode(",", $resultdata);
    }

    /**
     * Recalculate all estimations.
     * Used after the values of the model have been changed.
     * Only admin users can call this.
     */
    public static function recalculate() {
        global $DB;
        require_admin();
        $estimations = $DB->get_records('estimations', [], 'toolid ASC');
        $toolid = 0;
        foreach ($estimations as $estimation) {
            if ($toolid != $estimation->toolid) {
                $tool = $DB->get_record('tools', [ 'id' => $estimation->toolid ]);
                echo "Loading new tool #$tool->id / $tool->name<br />";
            }
            unset($tool->rating);
            unset($tool->result);
            echo "=> Recalculate #$estimation->id<br />";
            $tool->rating = $DB->get_record('ratings', [ 'estimationid' => $estimation->id ]);
            $tool->result = $DB->get_record('results', [ 'estimationid' => $estimation->id ]);
            $tool->estimation = $estimation;

            self::store_result($tool);
        }

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
        $tool->estimation->result = 0;
        foreach (self::$cols as $col) {
            $tool->estimation->result += $tool->result->$col;
        }
        $DB->update_record('estimations', $tool->estimation);
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
            ];
            foreach (self::$cols as $col) {
                $tool->result->{$col} = null;
            }
            $tool->result->id = $DB->insert_record('results', $tool->result);
        }

        $tool = self::calc_result($tool);
        $DB->update_record('results', $tool->result);
        return $tool;
    }
}
