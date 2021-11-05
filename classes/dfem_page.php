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

class dfem_page {
    private $vars = [];
    private $stylesheets = [];

    public function __construct() {
        $this->add_stylesheet('/style/main.css');
    }

    public function add_stylesheet($relativepath) {
        $this->stylesheets[] = $relativepath;
    }
    public function get_stylesheets() {
        global $CFG;
        $stylesheets = [];
        foreach ($this->stylesheets as $stylesheet) {
            $stylesheets[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$CFG->wwwroot$stylesheet\" />";
        }
        return implode("\n", $stylesheets);
    }

    public function heading($v = '') {
        return $this->get_set('heading', $v);
    }
    public function title($v = '') {
        return $this->get_set('title', $v);
    }

    private function get_set($t, $v) {
        if (empty($v)) {
            if (!empty($this->vars[$t])) {
                return $this->vars[$t];
            } else {
                return;
            }
        } else {
            $this->vars[$t] = $v;
            return $v;
        }
    }


}
