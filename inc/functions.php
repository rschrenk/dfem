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

function get_config($identifier) {
    global $CFG;
    if (!empty($CFG->$identifier)) {
        return $CFG->$identifier;
    }
}

function get_string($identifier, $component = '', $language = '', $params = null) {
    return \dfem_lang::get_string($identifier, $component, $language, $params);
}

function has_persona() {
    if (!is_loggedin()) return;
    global $DB;
    $persona = $DB->get_record('personas', [ 'authid' => $_SESSION['authid' ]]);
    return (!empty($persona->id));
}

function is_loggedin() {
    return !empty($_SESSION['authid']);
}

function redirect($url, $params = []) {
    global $CFG;
    $strparams = '';
    if (!empty($params) && !is_array($params) && !is_object($params)) {
        $strparams = $params;
    }
    if (is_array($params) && count($params) > 0) {
        $aparams = [];
        foreach ($params as $n => $v) {
            $aparams[] = $n . '=' . rawurlencode($v);
        }
        $strparams = implode('&', $aparams);
    }
    if (!empty($strparams)) {
        $strparams = "?$strparams";
    }
    header("Location: {$CFG->wwwroot}$url$strparams");
    exit;
}

function require_login() {
    if (!is_loggedin()) {
        redirect("/login/index.php");
    }
}

function require_persona() {
    global $DB;
    $persona = $DB->get_record('personas', [ 'authid' => $_SESSION['authid' ]]);
    if (empty($persona->id)) {
        redirect("/persona/index.php");
    }
    return $persona;
}

function retrieve($parameter) {
    if (!empty($_POST[$parameter])) {
        return $_POST[$parameter];
    }
    if (!empty($_GET[$parameter])) {
        return $_GET[$parameter];
    }
    if (!empty($_COOKIE[$parameter])) {
        return $_COOKIE[$parameter];
    }
}
