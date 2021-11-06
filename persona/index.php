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
//phpinfo();

require_once("../config.php");

$PAGE->heading(get_string('dfem'));
$PAGE->title(get_string('dfem'));

$persona = $DB->get_record('personas', [ 'authid' => $_SESSION['authid' ]]);
if (empty($persona) || empty($persona->id)) {
    $persona = (object) [];
}
$valid_data = [];
$fields = [
        'age' => [],
        'academiclevel' => [ 'hsd', 'alevel', 'b', 'm', 'p' ],
        'gender' => [ 'd', 'f', 'm' ],
        // Based on the list of the Austrian AMS (https://www.berufslexikon.at/bereiche-branchen/)
        'industry' => [
            'agriculture', 'buildingtechnology', 'chemistry', 'commerce',
            'engineering', 'environment', 'fashion', 'homecare',
            'media', 'mining', 'office',
            'social', 'tourism', 'science'
        ],
        'residual' => [],
        'nationality' => [],
];

if (!empty(retrieve('age'))) {
    foreach ($fields as $field => $subfields) {
        $persona->{$field} = retrieve($field);
    }
    if (empty($persona->id)) {
        $persona->authid = $_SESSION['authid'];
        $persona->id = $DB->insert_record('personas', $persona);
    } else {
        $DB->update_record('personas', $persona);
    }
}

foreach ($fields as $field => $subfields) {
    $valid_data[$field] = false;
    $persona->{"s_$field"} = [];
    foreach ($subfields as $subfield) {
        $persona->{"s_$field"}[] = (object)[
            "str_$field" => get_string("{$field}_{$subfield}", "persona"),
            $field => $subfield,
            "selected" => ($persona->{$field} == $subfield) ? 1 : 0,
        ];
        if ($persona->{$field} == $subfield) {
            $valid_data[$field] = true;
        }
    }
    if (count($subfields) == 0 && !empty($persona->{$field})) {
        $valid_data[$field] = true;
    }
}

$countries = \dfem_lang::get_countries();
$countryfields = [ 'residual', 'nationality' ];
foreach ($countryfields as $countryfield) {
    $persona->{"s_$countryfield"} = [];
    foreach ($countries as $countrycode => $country) {
        $persona->{"s_$countryfield"}[] = (object) [
            "str_$countryfield" => $country,
            $countryfield => $countrycode,
            "selected" => ($persona->{$countryfield} == $countrycode) ? 1 : 0,
        ];
        if ($persona->{$countryfield} == $countrycode) {
            $valid_data[$countryfield] = true;
        }
    }
}

$allvalid = true;
foreach($valid_data as $vdata) {
    if (!$vdata) {
        $allvalid = false;
    }
}

$persona->str_persona_store = get_string('persona_store');

echo $OUTPUT->header();

if ($allvalid) {
    echo $OUTPUT->navigation();
    $o = (object) [
        'msg' => get_string('data_complete', 'persona'),
        'type' => 'success',
        'url' => "$CFG->wwwroot/index.php",
    ];
    echo $OUTPUT->render_from_template('core/alert', $o);
}

echo $OUTPUT->render_from_template('core/persona', $persona);
echo $OUTPUT->footer();
