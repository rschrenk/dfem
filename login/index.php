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

if (!empty($_SESSION['authid'])) {
    redirect('/login/logout.php');
}

$PAGE->heading(get_string('dfem_long'));
$PAGE->title(get_string('dfem_long'));

$email = retrieve('email');
$onetimepassword = retrieve('onetimepassword');

if (!empty($email)) {
    for ($attempt = 0; $attempt < count($CFG->salts) && $attempt < 10; $attempt++) {
        $auth = $DB->get_record('authentications', array('email_hashed' => $DB->salt($email, $attempt)));
        if (!empty($auth) && !empty($auth->id)) {
            break;
        }
    }
    if (empty($auth) || empty($auth->id)) {
        $auth = (object) [
            'email_hashed' => $DB->salt($email),
            'onetimepassword' => substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 7)), 0, 7),
            'passwordcreated' => time(),
        ];
        $auth->id = $DB->insert_record('authentications', $auth);
    } else {
        $auth->onetimepassword = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 7)), 0, 7);
        $auth->passwordcreated = time();
        $DB->update_record('authentications', $auth);
    }

    if (!empty($auth) && !empty($auth->id)) {
        $_SESSION['expected_authid'] = $auth->id;

        // Now send the e-Mail containing the One Time Password.
        $params = [
            'onetimepassword' => $auth->onetimepassword,
            'proceed_url' => "$CFG->wwwroot/login/index.php?onetimepassword=$auth->onetimepassword",
            'str_onetimepassword' => get_string('onetimepassword', 'login'),
            'str_onetimepassword_enter' => get_string('onetimepassword_enter', 'login'),
            'str_onetimepassword_text' => get_string('onetimepassword_text', 'login'),
            'str_proceed' => get_string('proceed', 'login'),
            'str_welcome' => get_string('welcome', 'login'),
        ];
        $mailbody = $OUTPUT->render_from_template('login/email', $params);
        $mailsubject = get_string('onetimepassword_subject', 'login');
        $header = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=iso-8859-1',
            'To: ' . $email,
            'From: ' . get_string('email_noreply', 'login'),
        ];

        mail($email, $mailsubject, $mailbody, implode("\r\n", $header));
    }
}
if (!empty($_SESSION['expected_authid'])) {
    $auth = $DB->get_record('authentications', array('id' => $_SESSION['expected_authid']));
    if ($auth->passwordcreated < (time() - 60*5)) {
        $auth->onetimepassword = '';
        $auth->passwordcreated = 0;
        $DB->update_record('authentications', $auth);
        unset($_SESSION['expected_authid']);
    }
}

echo $OUTPUT->header();

if (empty($_SESSION['expected_authid'])) {
    $params = [
        'str_enter_email' => get_string('enter_email', 'login'),
        'str_email_dummy' => get_string('email_dummy', 'login'),
        'str_proceed' => get_string('proceed', 'login'),
        'str_welcome' => get_string('welcome', 'login'),
        'str_welcome_text' => get_string('welcome_text', 'login'),
    ];
    echo $OUTPUT->render_from_template('login/login', $params);
} elseif (!empty($onetimepassword) && $onetimepassword == $auth->onetimepassword) {
    $_SESSION['authid'] = $auth->id;
    unset($_SESSION['expected_authid']);
    redirect('/index.php');
} else {
    $params = [
        'passwordcreated' => $auth->passwordcreated,
        'str_onetimepassword' => get_string('onetimepassword', 'login'),
        'str_onetimepassword_enter' => get_string('onetimepassword_enter', 'login'),
        'str_onetimepassword_expired' => get_string('onetimepassword_expired', 'login'),
        'str_onetimepassword_text' => get_string('onetimepassword_text', 'login'),
        'str_proceed' => get_string('proceed', 'login'),
        'url_login' => $CFG->wwwroot . '/login/index.php',
    ];
    echo $OUTPUT->render_from_template('login/onetimepassword', $params);
}

echo $OUTPUT->footer();
