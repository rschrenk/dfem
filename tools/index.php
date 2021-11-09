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

$PAGE->heading(get_string('dfem'));
$PAGE->title(get_string('dfem'));

echo $OUTPUT->header();
$params = (object) [
    'toolcategories' => [],
    'tool' => '',
];


$toolid = retrieve('id');
if (!empty($toolid)) {
    $tool = $DB->get_record('tools', [ 'id' => $toolid ]);

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
    $params->tool = $tool;
}


$sql = "SELECT id,toolid
            FROM {prefix}estimations
            WHERE authid = ?";
$recs = $DB->get_records_sql($sql, [ $_SESSION['authid']]);
$hasestimated = [];
foreach ($recs as $rec) {
    $hasestimated[] = $rec->toolid;
}

$sql = "SELECT t.*, tc.category
            FROM {prefix}tools t, {prefix}toolcategories tc
            WHERE t.toolcategory = tc.id
            ORDER BY tc.category ASC, t.archetype ASC, t.name ASC";
$tools = $DB->get_records_sql($sql);
$toolcategory = ''; $archetype = ''; $catindex = -1; $archeindex = -1;
foreach ($tools as $tool) {
    if ($tool->category != $toolcategory) {
        $catindex++;
        $toolcategory = $tool->category;
        $params->toolcategories[$catindex] = (object) [
            'category' => $toolcategory,
            'countcategory' => 0,
            'archetypes' => [],
        ];
        $archetype = '';
        $archeindex = -1;
    }
    $params->toolcategories[$catindex]->countcategory++;
    if ($archetype != $tool->archetype) {
        $archeindex++;
        $archetype = $tool->archetype;
        $params->toolcategories[$catindex]->archetypes[$archeindex] = (object) [
            'archetype' => $archetype,
            'countarchetype' => 0,
            'tools' => [],
        ];
    }
    $tool->toolurl = "$CFG->wwwroot/tools/index.php?id=$tool->id";
    $tool->hasestimated = (in_array($tool->id, $hasestimated));
    $params->toolcategories[$catindex]->archetypes[$archeindex]->tools[] = $tool;
    $params->toolcategories[$catindex]->archetypes[$archeindex]->countarchetype++;
    if ($archetype == $tool->name) {
        $params->toolcategories[$catindex]->archetypes[$archeindex]->toolisarchetype = 1;
    }
}

$params->isadmin = is_admin();

echo $OUTPUT->navigation();
echo $OUTPUT->render_from_template('tools/tools', $params);
echo $OUTPUT->footer();
