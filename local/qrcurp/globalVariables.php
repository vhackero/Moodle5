<?php
require_once(__DIR__.'/../../config.php');

use local_qrcurp\local\config;

GLOBAL $NAMEPLATAFORMQRCURP, $NAMEEXTERNALDBQRCURP;

$NAMEPLATAFORMQRCURP = config::get_string('nameplataform');
$NAMEEXTERNALDBQRCURP = config::get_string('nameexternal');

/**
 * Verify and create profile field used for registration origin tracking.
 *
 * @return bool|int
 */
function verify_registration_profile_field() {
    global $DB;

    $field = $DB->get_record('user_info_field', ['shortname' => 'registro']);

    if (!$field) {
        $newfield = (object) [
            'shortname' => 'registro',
            'name' => 'Origen de Registro',
            'datatype' => 'text',
            'description' => 'Origen del registro del usuario (default, saberes_mx)',
            'descriptionformat' => 1,
            'categoryid' => 1,
            'sortorder' => 1,
            'required' => 0,
            'locked' => 1,
            'visible' => 2,
            'forceunique' => 0,
            'signup' => 0,
            'defaultdata' => 'default',
            'defaultdataformat' => 0,
            'param1' => 30,
            'param2' => 2048,
        ];

        return $DB->insert_record('user_info_field', $newfield);
    }

    return true;
}

/**
 * Gets user registration origin from profile fields.
 */
function get_registration_origin_from_profile($userid) {
    global $DB;

    $sql = "SELECT uid.data
              FROM {user_info_data} uid
              JOIN {user_info_field} uif ON uif.id = uid.fieldid
             WHERE uif.shortname = 'registro' AND uid.userid = ?";

    $record = $DB->get_record_sql($sql, [$userid]);

    return $record ? $record->data : null;
}
