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

define('LOGIN_PAGE', 1);

require_once("../config.php");

$confirmed = retrieve('confirmed');
if (!empty($confirmed)) {
    unset($_SESSION['authid']);
}

if (empty($_SESSION['authid'])) {
    redirect('/login/index.php');
}

$PAGE->heading(get_string('logout', 'login'));
$PAGE->title(get_string('logout', 'login'));

echo $OUTPUT->header();
echo $OUTPUT->navigation();
$params = [
    'str_proceed' => get_string('proceed', 'login'),
    'str_logout' => get_string('logout', 'login'),
    'str_logout_text' => get_string('logout_text', 'login'),
    'url_logout' => $CFG->wwwroot . '/login/logout.php?confirmed=1',
];
echo $OUTPUT->render_from_template('login/logout', $params);

echo $OUTPUT->footer();
