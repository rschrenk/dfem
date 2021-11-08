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

class dfem_lang {
    private static $verbs;

    /**
     * Initialize the language interface.
     */
    public static function init() {
        global $CFG;
        self::load_language($CFG->lang_fallback);
        if ($CFG->lang_fallback != $CFG->lang_default) {
            self::load_language($CFG->lang_default);
        }
    }
    public static function get_countries($language = '') {
        global $CFG;
        if (empty($language)) {
            $language = $CFG->lang_default;
        }
        // First load list in fallback language, then overwrite existing pairs
        // from desired language.
        $countries = [];
        $files = [
            "$CFG->dirroot/lang/$CFG->lang_fallback/countries.csv",
        ];
        if ($CFG->lang_fallback != $language) {
            $files[] = "$CFG->dirroot/lang/$language/countries.csv";
        }
        foreach ($files as $file) {
            if (file_exists($file)) {
                $lines = explode("\n", file_get_contents($file));
                foreach ($lines as $line) {
                    $line = explode(",", $line);
                    if (count($line) == 2) {
                        $countries[strtolower(trim($line[0]))] = trim($line[1]);
                    }
                }
            }
        }
        return $countries;
    }
    public static function get_languages() {
        global $CFG;
        $langcandidates = scandir("$CFG->dirroot/lang");
        $languages = [];
        foreach ($langcandidates as $candidate) {
            if (str_replace('.', '', $candidate) == '') continue;
            if (!is_dir("$CFG->dirroot/lang/$candidate")) continue;
            $languages[] = $candidate;
        }
        return $languages;
    }
    /**
     * Get a particular localized string.
     * If string is not found in default language, fallback language is used.
     * @param verb the verb that should be returned in localized language.
     * @param component the component to load string of.
     * @param language valid language identifier (e.g. en, de, ...)
     * @param params Additional parameters to be replaced in string using {$a} or {$a->param}
     */
    public static function get_string($verb, $component = '', $language = '', $params = null) {
        global $CFG;
        if (empty($component)) {
            $component = 'core';
        }
        if (empty($language)) {
            $language = $CFG->lang_default;
        }
        if (is_array($params)) {
            $params = (object) $params;
        }

        $languages = [ $language, $CFG->lang_fallback];
        foreach ($languages as $language) {
            if (empty(self::$verbs[$language]) || empty(self::$verbs[$language][$component])) {
                self::load_language($language, $component);
            }
            if (!empty(self::$verbs[$language][$component][$verb])) {
                $verb = self::$verbs[$language][$component][$verb];
                if (!empty($params)) {
                    if (is_object($params)) {
                        foreach ($params as $param => $val) {
                            $verb = str_replace('{$a->' . $param . '}', $val, $verb);
                        }
                    } else {
                        $verb = str_replace('{$a}', $params, $verb);
                    }
                }
                return $verb;
            }
        }
    }
    public static function lang_selector() {
        global $CFG, $OUTPUT, $PAGE;

        $langcandidates = self::get_languages();
        $languages = [];
        foreach ($langcandidates as $candidate) {
            $languages[] = (object) [
                'lang' => $candidate,
                'caption' => get_string('lang', 'core', $candidate),
                'selected' => ($CFG->lang_default == $candidate),
            ];
        }

        $params = (object) [
            'languages' => $languages
        ];
        return $OUTPUT->render_from_template("core/langselector", $params);
    }
    /**
     * Load a specific language file into memory.
     * @param language valid language identifier (e.g. en, de, ...)
     * @param component the component to load strings of.
     */
    private static function load_language($language, $component = 'core') {
        global $CFG;
        if (!file_exists("$CFG->dirroot/lang/{$language}/{$component}.php")) {
            return;
        }
        require_once("{$CFG->dirroot}/lang/{$language}/{$component}.php");
        self::$verbs[$language][$component] = $lang;

    }
    /**
     * Sets the default language for a single request.
     * Used for user specific settings.
     * @param language valid language identifier (e.g. en, de, ...)
     */
    public static function set_default($language) {
        global $CFG, $DB;
        if (!in_array($language, self::get_languages())) {
            unset($_SESSION['lang_default']);
            throw new \dfem_exception(get_string('language_unkown', 'core', '', $language));
        }
        $CFG->lang_default = $language;
        $_SESSION['lang_default'] = $language;

        if (!empty($_SESSION['authid'])) {
            $auth = $DB->get_record('authentications', [ 'id' => $_SESSION['authid'] ]);
            if ($auth->language != $language) {
                $auth->language = $language;
                $DB->update_record('authentications', $auth);
            }
        }
    }
}
