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

require_once("config.php");

$PAGE->heading(get_string('dfem'));
$PAGE->title(get_string('dfem'));

$persona = require_persona();

echo $OUTPUT->header();
$params = (object) [
    'toolcategories' => [],
    'tool' => '',
];

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
            'archetypes' => [],
        ];
        $archetype = '';
        $archeindex = -1;
    }
    if ($archetype != $tool->archetype) {
        $archeindex++;
        $archetype = $tool->archetype;
        $params->toolcategories[$catindex]->archetypes[$archeindex] = (object) [
            'archetype' => $archetype,
            'tools' => [],
        ];
    }
    $tool->toolurl = "$CFG->wwwroot/tool.php?id=$tool->id";
    $params->toolcategories[$catindex]->archetypes[$archeindex]->tools[] = $tool;
}

$toolid = retrieve('id');
if (!empty($toolid)) {
    $params->tool = $DB->get_record('tools', [ 'id' => $toolid ]);
}

echo $OUTPUT->navigation();
echo $OUTPUT->render_from_template('core/dashboard', $params);
echo $OUTPUT->footer();
