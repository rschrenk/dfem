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

class dfem_output {
    private static $loaded = false;

    public function header() {
        global $PAGE;
        $params = [
            'heading' => $PAGE->heading(),
            'title' => $PAGE->title(),
            'stylesheets' => $PAGE->get_stylesheets(),
        ];
        return $this->render_from_template('core/header', $params);
    }

    public function footer() {
        return $this->render_from_template('core/footer');
    }

    public function render_from_template($template, $params = []) {
        global $CFG;
        $m = self::get_mustache_engine();
        return $m->render($template, $params);
    }

    private static function get_mustache_engine() {
        global $CFG;
        if (!self::$loaded) {
            require_once("{$CFG->dirroot}/vendor/autoload.php");
            self::$loaded = true;
        }
        return new \Mustache_Engine(array(
            'loader' => new Mustache_Loader_FilesystemLoader("{$CFG->dirroot}/templates"),
        ));
    }

}
