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

define('DFEM_INTERNAL', 1);

$CFG = (object) [];
$CFG->dirroot = '';
$CFG->wwwroot = '';
$CFG->db = [
    'host'   => 'localhost',
    'db'     => 'dfem',
    'user'   => 'root',
    'pass'   => '',
    'prefix' => 'dfem_',
];
$CFG->phpmailer = [
    'host'   => '',
    'user'   => '',
    'pass'   => '',
    'port'   => 465,
    'from'   => '',
    'fromn'  => 'Digital Footprint Estimation Model',
];
// YOU SHOULD ADD A SALT HERE TO PROTECT YOUR USERS PRIVACY.
// ATTENTION: IF YOU CHANGE THE SALT, CONNECTIONS BETWEEN personas AND
// authentications WILL NOT BE POSSIBLE ANYMORE.
// ADD NEW SALTS AT THE TOP OF THE LIST, UP TO 10 SALTS WILL BE USED.
// HINT: use a salt generator like https://www.symbionts.de/tools/random-password-salt-generator.html
$CFG->salts = [
    '@XZMptHokoNdLFyFwp:7Oo=cOlPYPQcVodp.H-J)@bpxh'
];
$CFG->lang_default  = 'en';
$CFG->lang_fallback = 'en';

require_once("{$CFG->dirroot}/inc/init.php");
