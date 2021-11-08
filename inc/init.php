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

require_once("{$CFG->dirroot}/inc/functions.php");
require_once("{$CFG->dirroot}/classes/dfem_db.php");
require_once("{$CFG->dirroot}/classes/dfem_exception.php");
require_once("{$CFG->dirroot}/classes/dfem_helper.php");
require_once("{$CFG->dirroot}/classes/dfem_lang.php");
require_once("{$CFG->dirroot}/classes/dfem_mailer.php");
require_once("{$CFG->dirroot}/classes/dfem_output.php");
require_once("{$CFG->dirroot}/classes/dfem_page.php");

\dfem_lang::init();
$OUTPUT = new \dfem_output();
$PAGE = new \dfem_page();
$DB = new \dfem_db();

session_start();

if (!empty($_SESSION['lang_default'])) {
    \dfem_lang::set_default($_SESSION['lang_default']);
}

if (!empty(retrieve("setlanguage"))) {
    \dfem_lang::set_default(retrieve("setlanguage"));
}
