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

$PAGE->heading(get_string('personal_profile', 'results'));
$PAGE->title(get_string('personal_profile', 'results'));

require_login();
$persona = require_persona();

$PAGE->require_js("/node_modules/chart.js/dist/chart.min.js");

//$labels = \dfem_helper::get_resultdata('labels', null);
$estimations = $DB->get_records('estimations', [ 'authid' => $_SESSION['authid']], 'result DESC');
foreach ($estimations as $estimation) {
    $estimation->result = $DB->get_record('results', [ 'estimationid' => $estimation->id ]);
    $estimation->tool = $DB->get_record('tools', [ 'id' => $estimation->toolid ]);
    //$estimation->resultdata_labels = $labels;
    $estimation->resultdata_mine = \dfem_helper::get_resultdata('mine', $estimation->result);
    if (empty(retrieve('singlechart'))) {
        $estimation->resultdata_mean = \dfem_helper::get_resultdata('mean', $estimation->result);
    }
}



echo $OUTPUT->header();
echo $OUTPUT->navigation();

$params = (object) [
    'estimations' => $estimations,
    'resultdata_labels' => \dfem_helper::get_resultdata('labels', null),
    'singlechart' => retrieve('singlechart'),
];

echo $OUTPUT->render_from_template('tools/profile', $params);
echo $OUTPUT->footer();
