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
    /**
     * Get a particular localized string.
     * If string is not found in default language, fallback language is used.
     * @param verb the verb that should be returned in localized language.
     * @param component the component to load string of.
     * @param language valid language identifier (e.g. en, de, ...)
     */
    public static function get_string($verb, $component = 'core', $language = '') {
        global $CFG;

        if (empty($language)) {
            $language = $CFG->lang_default;
        }
        $languages = [ $language, $CFG->lang_fallback];
        foreach ($languages as $language) {
            if (empty(self::$verbs[$language]) || empty(self::$verbs[$language][$component])) {
                self::load_language($language, $component);
            }
            if (!empty(self::$verbs[$language][$component][$verb])) {
                return self::$verbs[$language][$component][$verb];
            }
        }
    }
    /**
     * Load a specific language file into memory.
     * @param language valid language identifier (e.g. en, de, ...)
     * @param component the component to load strings of.
     */
    private static function load_language($language, $component = 'core') {
        global $CFG;
        if (!file_exists("$CFG->dirroot/lang/{$language}_{$component}.php")) {
            return;
        }
        require_once("{$CFG->dirroot}/lang/{$language}_{$component}.php");
        self::$verbs[$language][$component] = $lang;

    }
    /**
     * Sets the default language for a single request.
     * Used for user specific settings.
     * @param language valid language identifier (e.g. en, de, ...)
     */
    public static function set_default($language) {
        global $CFG;
        if (file_exists("$CFG->dirroot/lang/$language.php")) {
            $CFG->lang_default = $language;
        }
    }
}
