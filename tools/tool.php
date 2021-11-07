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

require_login();
$persona = require_persona();

$toolid = retrieve('id');
if (!empty($toolid)) {
    $tool = $DB->get_record('tools', [ 'id' => $toolid ]);
    if (empty($tool->id)) {
        redirect("/tools/index.php");
    }
}

$PAGE->heading($tool->name);
$PAGE->title(get_string($tool->name));

$tool->estimation = $DB->get_record('estimations', [ 'authid' => $_SESSION['authid'], 'toolid' => $tool->id]);
if (!empty($tool->estimation->id)) {
    $tool->rating = $DB->get_record('ratings', [ 'estimationid' => $tool->estimation->id ]);
    $tool->result = $DB->get_record('results', [ 'estimationid' => $tool->estimation->id ]);
}
$fieldtypes = \dfem_helper::get_fieldtypes();

if (!empty(retrieve('formsent'))) {
    $tool = \dfem_helper::store_estimation($tool);
    $tool->stored = 1;
}

$tool->fieldtypes = [];
foreach ($fieldtypes as $fieldtype => $fields) {
    $index = count($tool->fieldtypes);
    $tool->fieldtypes[$index] = (object)[
        'fieldtype' => $fieldtype,
        'label' => get_string($fieldtype, "tools"),
        'fields' => [],
    ];
    foreach ($fields as $field) {
        $tool->fieldtypes[$index]->fields[] = (object) [
            'field' => $field,
            'label' => get_string($field, "tools"),
            'enabled' => (!empty($tool->rating->{$field})) ? 1 : 0,
        ];
    }
}

echo $OUTPUT->header();
echo $OUTPUT->navigation();
echo $OUTPUT->render_from_template('tools/tool', $tool);
echo $OUTPUT->footer();
