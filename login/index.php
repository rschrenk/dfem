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
            'timecreated' => time(),
            'timelastlogin= '> time(),
        ];
        $auth->id = $DB->insert_record('authentications', $auth);
    } else {
        $auth->onetimepassword = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 7)), 0, 7);
        $auth->passwordcreated = time();
        $DB->update_record('authentications', $auth);
    }

    if (!empty($auth) && !empty($auth->id)) {
        $_SESSION['expected_authid'] = $auth->id;

        $mailer = new \dfem_mailer();

        // Now send the e-Mail containing the One Time Password.
        $params = [
            'onetimepassword' => $auth->onetimepassword,
            'proceed_url' => "$CFG->wwwroot/login/index.php?onetimepassword=$auth->onetimepassword",
        ];
        $mailer->msgHTML($OUTPUT->render_from_template('login/email', $params));
        $mailer->set('Subject', get_string('onetimepassword_subject', 'login'));
        $mailer->addAddress($email);
        $mailer->send();
    }
}
if (!empty($_SESSION['expected_authid'])) {
    $auth = $DB->get_record('authentications', array('id' => $_SESSION['expected_authid']));
    if ($auth->passwordcreated < (time() - 60*5)) {
        $auth->onetimepassword = '';
        $auth->passwordcreated = 0;
        $auth->timelastlogin = time();
        $DB->update_record('authentications', $auth);
        unset($_SESSION['expected_authid']);
    }
}
$langselector = \dfem_lang::lang_selector();

echo $OUTPUT->header();

if (empty($_SESSION['expected_authid'])) {
    $params = [
        'langselector' => $langselector,
        'str_proceed' => get_string('proceed', 'login'),
    ];
    echo $OUTPUT->render_from_template('login/login', $params);
} elseif (!empty($onetimepassword) && $onetimepassword == $auth->onetimepassword) {
    if (!empty($auth->language)) {
        \dfem_lang::set_default($auth->language);
    }
    $_SESSION['authid'] = $auth->id;
    unset($_SESSION['expected_authid']);
    redirect('/index.php');
} else {
    $params = [
        'langselector' => $langselector,
        'passwordcreated' => $auth->passwordcreated,
        'str_proceed' => get_string('proceed', 'login'),
        'url_login' => $CFG->wwwroot . '/login/index.php',
    ];
    echo $OUTPUT->render_from_template('login/onetimepassword', $params);
}

echo $OUTPUT->footer();
