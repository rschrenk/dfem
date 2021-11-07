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

$PAGE->heading(get_string('share', 'results'));
$PAGE->title(get_string('share', 'results'));

require_login();
require_persona();

$id = retrieve('id');
$estimation = $DB->get_record('estimations', [ 'id' => $id ]);
if (empty($estimation->id)) {
    throw new \dfem_exception(get_string('missing_estimation', 'results'), 0, "/tools/index.php");
}
if ($_SESSION['authid'] != $estimation->authid) {
    throw new \dfem_exception(get_string('permission_denied', 'core'), 0, "/tools/index.php");
}
$tool = $DB->get_record('tools', [ 'id' => $estimation->toolid ]);

if (!empty(retrieve('revoke'))) {
    $sharing = $DB->get_record('sharings', [ 'estimationid' => $estimation->id, 's' => retrieve('revoke')]);
    if (!empty($sharing->id) && empty($sharing->timeexpired)) {
        $sharing->timeexpired = time();
        $DB->update_record('sharings', $sharing);
    }
}

$sql = "SELECT *
            FROM {prefix}sharings
            WHERE estimationid = ?
            LIMIT 0,1";
$sharing = $DB->get_record_sql($sql, [ $estimation->id ]);

if (empty($sharing->id) || !empty(retrieve('createshare'))) {
    $sharing = (object)[
        'estimationid' => $estimation->id,
        's' => substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 10)), 0, 10),
        'timecreated' => time(),
        'timeexpired' => 0,
    ];
    $sharing->id = $DB->insert_record('sharings', $sharing);
}

echo $OUTPUT->header();

echo $OUTPUT->navigation();

$params = (object) [
    'estimation' => $estimation,
    'sharings' => array_values(
        $DB->get_records_sql(
            "SELECT s.*
                FROM {prefix}sharings s, {prefix}estimations e
                WHERE s.estimationid=e.id
                    AND s.estimationid = ?
                    AND e.authid = ?
                ORDER BY timeexpired ASC, timecreated ASC",
            [ $estimation->id, $_SESSION['authid'] ]
        )
    ),
    'tool' => $tool,
];

echo $OUTPUT->render_from_template('tools/sharings', $params);
echo $OUTPUT->footer();
