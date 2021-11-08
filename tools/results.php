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

require_once("../config.php");

$PAGE->heading(get_string('results', 'results'));
$PAGE->title(get_string('results', 'results'));

$id = retrieve('id');
$s  = retrieve('s');

if (!empty($s)) {
    // Load id of shared estimation.
    $shared = $DB->get_record('sharings', [ 's' => $s, 'timeexpired' => 0 ]);
    if (empty($shared->id)) {
        throw new \dfem_exception(get_string('secret_not_found', 'results'));
    }
    $id = $shared->estimationid;
} else {
    require_login();
    $persona = require_persona();
}

if (!empty($id)) {
    $estimation = $DB->get_record('estimations', [ 'id' => $id ]);
    if (empty($estimation->id)) {
        throw new \dfem_exception(get_string('missing_estimation', 'results'), 0, "/tools/index.php");
    }
}

if (empty($s) && $_SESSION['authid'] != $estimation->authid) {
    throw new \dfem_exception(get_string('permission_denied', 'core'), 0, "/tools/index.php");
}

$tool = $DB->get_record('tools', [ 'id' => $estimation->toolid ]);

$PAGE->heading($tool->name);
$PAGE->title(get_string($tool->name));
$PAGE->require_js("/node_modules/chart.js/dist/chart.min.js");

$rating = $DB->get_record('ratings', [ 'estimationid' => $estimation->id ]);
$result = $DB->get_record('results', [ 'estimationid' => $estimation->id ]);

echo $OUTPUT->header();

if (empty(retrieve("s"))) {
    echo $OUTPUT->navigation();
}

$params = (object) [
    'canshare' => empty(retrieve("s")) && is_loggedin() && $_SESSION['authid'] == $estimation->authid,
    'estimation' => $estimation,
    'isshared' => !empty(retrieve("s")),
    'rating' => $rating,
    'result' => $result,
    'resultclasses' => \dfem_helper::calc_result_classes($result),
    'resultmeanclasses' => \dfem_helper::calc_result_mean_classes($estimation),
    'resultdata_maximum' => \dfem_helper::get_resultdata('maximum', $result),
    'resultdata_minimum' => \dfem_helper::get_resultdata('minimum', $result),
    'resultdata_mine' => \dfem_helper::get_resultdata('mine', $result),
    'resultdata_mean' => \dfem_helper::get_resultdata('mean', $result, $estimation),
    'tool' => $tool,
];

echo $OUTPUT->render_from_template('tools/results', $params);
echo $OUTPUT->footer();
