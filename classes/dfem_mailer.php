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

if (!defined('DFEM_INTERNAL')) die();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require("$CFG->dirroot/vendor/autoload.php");
require("$CFG->dirroot/vendor/phpmailer/phpmailer/src/PHPMailer.php");
require("$CFG->dirroot/vendor/phpmailer/phpmailer/src/SMTP.php");
require("$CFG->dirroot/vendor/phpmailer/phpmailer/src/Exception.php");

class dfem_mailer {
    private $phpmailer;

    public function __construct() {
        global $CFG;
        try {
            $this->phpmailer = new PHPMailer(true);
            //$this->phpmailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->phpmailer->isSMTP();

            $this->phpmailer->Host = $CFG->phpmailer['host'];
            $this->phpmailer->Username = $CFG->phpmailer['user'];
            $this->phpmailer->Password = $CFG->phpmailer['pass'];
            $this->phpmailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->phpmailer->SMTPAuth = true;
            $this->phpmailer->Port = $CFG->phpmailer['port'];
            $this->phpmailer->setFrom($CFG->phpmailer['from'], $CFG->phpmailer['fromn']);
        } catch(Exception $e) {
            echo "PHPMailer error: " . $this->phpmailer->ErrorInfo;
        }

    }
    public function addAddress($address, $name = null) {
        $this->phpmailer->addAddress($address, $name);
    }
    public function addCC($address, $name = null) {
        $this->phpmailer->addCC($address, $name);
    }
    public function addBCC($address, $name = null) {
        $this->phpmailer->addBCC($address, $name);
    }
    public function addAttachment($path, $filename = null) {
        $this->phpmailer->addAttachment($path, $filename);
    }
    public function isHTML($to) {
        $this->phpmailer->isHTML($to);
    }
    public function msgHTML($html, $path = null) {
        $this->phpmailer->msgHTML($html, $path);
    }
    /**
     * Set Subject, Body or AltBody, but you could also overwrite all other parameters.
     */
    public function set($what, $to) {
        $this->phpmailer->{$what} = $to;
    }
    public function send() {
        try {
            $this->phpmailer->send();
        } catch(Exception $e) {
            echo "E-Mail could not be sent: " . $this->phpmailer->ErrorInfo;
        }

    }

}
