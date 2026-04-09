<?php
require_once(__DIR__.'/../../config.php');
GLOBAL $NAMEPLATAFORMREGISTRY,$NAMEEXTERNALDBREGISTRY;

$NAMEPLATAFORMREGISTRY = get_config('local_registry','nameplataform');
$NAMEEXTERNALDBREGISTRY = get_config('local_registry','nameexternal');


// ✅ NUEVO: FUNCIÓN PARA VERIFICAR Y CREAR EL CAMPO DE PERFIL
function verify_registration_profile_field()
{
    global $DB;

    $field = $DB->get_record('user_info_field', array('shortname' => 'registro'));

    if (!$field) {
        // Crear el campo automáticamente si no existe
        $new_field = new stdClass();
        $new_field->shortname = 'registro';
        $new_field->name = 'Origen de Registro';
        $new_field->datatype = 'text';
        $new_field->description = 'Origen del registro del usuario (default, saberes_mx)';
        $new_field->descriptionformat = 1;
        $new_field->categoryid = 1;
        $new_field->sortorder = 1;
        $new_field->required = 0;
        $new_field->locked = 1;
        $new_field->visible = 2; // Solo para administradores
        $new_field->forceunique = 0;
        $new_field->signup = 0;
        $new_field->defaultdata = 'default';
        $new_field->defaultdataformat = 0;
        $new_field->param1 = 30;
        $new_field->param2 = 2048;

        return $DB->insert_record('user_info_field', $new_field);
    }

    return true;
}

// ✅ NUEVO: FUNCIÓN PARA OBTENER EL ORIGEN DEL REGISTRO
function get_registration_origin_from_profile($userid)
{
    global $DB;

    $sql = "SELECT uid.data 
            FROM {user_info_data} uid 
            JOIN {user_info_field} uif ON uif.id = uid.fieldid 
            WHERE uif.shortname = 'registro' AND uid.userid = ?";

    $record = $DB->get_record_sql($sql, array($userid));

    return $record ? $record->data : null;
}
