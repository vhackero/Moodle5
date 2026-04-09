<?php
/**
 * @package local_registry
 * Librería de funciones para matriculación y grupos
 */
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/mail/index.php');

define('REGISTRY_GROUPENTIDAD', 10001);

/**
 * Función para matricular a un usuario en un curso y asignarlo a un grupo de forma segura.
 */
function local_registry_set_course_group($iduserinsert, $idcourse = '', $idcreategroup = '', $alias = '') {
    global $DB;

    if (empty($idcourse)) return;

    if (empty($alias)) {
        $u = $DB->get_record('user', array('id' => $iduserinsert), 'idnumber');
        $alias = $u ? $u->idnumber : '';
    }

    $context = context_course::instance($idcourse);
    $roleid = get_config('local_registry', 'rolstudent');
    $enrol = $DB->get_record('enrol', array('enrol' => "manual", "courseid" => $idcourse));

    if ($enrol) {
        // VALIDACIÓN PARA EVITAR DOBLE MATRICULACIÓN Y DOBLE CORREO
        $ya_matriculado = $DB->record_exists('user_enrolments', array('enrolid' => $enrol->id, 'userid' => $iduserinsert));

        if (!$ya_matriculado) {
            // Proceso de matriculación
            $ra = (object)['roleid'=>$roleid, 'contextid'=>$context->id, 'userid'=>$iduserinsert, 'timemodified'=>time(), 'modifierid'=>2];
            $DB->insert_record('role_assignments', $ra);

            $ue = (object)['status'=>0, 'enrolid'=>$enrol->id, 'userid'=>$iduserinsert, 'modifierid'=>2, 'timecreated'=>time(), 'timemodified'=>time()];
            $DB->insert_record('user_enrolments', $ue);

            // LLAMADA A AULA PRÁCTICA (Si la función existe)
            if (function_exists('registeruserinothersite')) {
                registeruserinothersite($iduserinsert, $alias);
            }

            // ENVIAR CORREO: Solo si es una matriculación nueva
            // Se usa el tipo 2 que es donde se agregó la lógica de la CURP en minúsculas
            local_registry_envia_correo($iduserinsert, 2, $alias, $idcourse, $idcreategroup);
        }
    }

    // Asignación a grupo
    if (!empty($idcreategroup) && $idcreategroup != REGISTRY_GROUPENTIDAD) {
        if ($DB->record_exists('groups', array('id' => $idcreategroup))) {
            if (!$DB->record_exists('groups_members', array('groupid' => $idcreategroup, 'userid' => $iduserinsert))) {
                groups_add_member($idcreategroup, $iduserinsert);
            }
        }
    }
}