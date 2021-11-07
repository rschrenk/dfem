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

class dfem_exception extends Exception {
    public function __construct($message = "", $code = 0, $urltogo = "") {
        global $OUTPUT;
        echo $OUTPUT->header();
        $p = (object) [
            'msg' => $message,
            'type' => 'danger',
        ];
        if (!empty($urltogo)) {
            $p->url = "$CFG->wwwroot$urltogo";
        }
        echo $OUTPUT->render_from_template("core/alert", $p);
        echo $OUTPUT->footer();
        die();
    }
}
