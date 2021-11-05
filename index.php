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
//phpinfo();

require_once("config.php");

$PAGE->heading(get_string('dfem'));
$PAGE->title(get_string('dfem'));

echo $OUTPUT->header();
$params = (object) [
    'archetypes' => [],
];
$sql = "SELECT * FROM {prefix}tools ORDER BY archetype ASC, name ASC";
$tools = $DB->get_records_sql($sql);
$archetype = ''; $index = 0;
foreach ($tools as $tool) {
    if ($archetype != $tool->archetype) {
        $archetype = $tool->archetype;
        $params['archetypes'][$index++] = (object)[
            'archetype' => $archetype,
            'tools' => [],
        ];
    }
    $archetypes[$index]->tools[] = $tool;
}
print_r($params);
echo $OUTPUT->render_from_template('core/dashboard', $params);
echo $OUTPUT->footer();
