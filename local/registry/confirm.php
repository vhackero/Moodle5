<?php
/**
 * @package local_registry
 * Procesamiento de confirmación de cuenta
 */
require(__DIR__ . '/../../config.php');
require_once('lib.php');

$data = optional_param('data', '', PARAM_RAW);
$PAGE->set_url('/local/registry/confirm.php');
$PAGE->set_context(context_system::instance());

if (!empty($data)) {
    $parts = explode('/', $data, 2);
    $usersecret = isset($parts[0]) ? $parts[0] : '';
    $username = isset($parts[1]) ? $parts[1] : '';

    $consultauser = $DB->get_record('user', array('username' => $username, 'secret' => $usersecret));

    if ($consultauser) {
        if ($consultauser->confirmed == 0) {
            $update = (object)['id' => $consultauser->id, 'confirmed' => 1, 'secret' => '', 'timemodified' => time()];
            $DB->update_record('user', $update);

            $courseid = $consultauser->institution;
            $groupid = $consultauser->department;
            $alias = $consultauser->idnumber;

            if ($groupid != REGISTRY_GROUPENTIDAD && !$DB->record_exists('groups', array('id' => $groupid))) {
                $groupid = '';
            }

            // Esta función ahora controla el envío único del correo
            local_registry_set_course_group($consultauser->id, $courseid, $groupid, $alias);

            redirect($CFG->wwwroot . '/course/view.php?id=' . $courseid, "Cuenta confirmada exitosamente. Revisa tu correo electrónico para ver tus datos de acceso.", 10, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            redirect($CFG->wwwroot . '/login/index.php', "Esta cuenta ya fue confirmada.");
        }
    }
}