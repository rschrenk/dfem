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

$PAGE->heading(get_string('dfem_long'));
$PAGE->title(get_string('dfem_long'));

$email = retrieve('email');

if (!empty($email)) {
    for ($attempt = 0; $attempt < count($CFG->salts) && $attempt < 10; $attempt++) {
        $auth = $DB->get_record('authentications', array('email' => $DB->salt($email, $attempt)));
        if (!empty($auth->id)) {
            break;
        }
    }

}

$params = [
    'str_enter_email' => get_string('enter_email', 'login'),
    'str_email_dummy' => get_string('email_dummy', 'login'),
    'str_proceed' => get_string('proceed', 'login'),
    'str_welcome' => get_string('welcome', 'login'),
    'str_welcome_text' => get_string('welcome_text', 'login'),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('login/login', $params);
echo $OUTPUT->footer();
