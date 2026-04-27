<?php

//CONFIRMACIÓN PARA LOS USUARIOS REGISTRADOS.

require(__DIR__ . '/../../config.php');
require(__DIR__ . '/../../login/lib.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/group/lib.php');

require_once('lib.php');

/**
 * Valida si el grupo todavía tiene cupo disponible para confirmaciones.
 *
 * @param int $groupid
 * @param int $userid
 * @return bool
 */
function local_qrcurp_group_has_space_for_confirmation(int $groupid, int $userid): bool {
    global $DB;

    if ($groupid <= 0) {
        return true;
    }

    // Si el usuario ya pertenece al grupo, no bloqueamos la confirmación.
    if ($DB->record_exists('groups_members', ['groupid' => $groupid, 'userid' => $userid])) {
        return true;
    }

    $group = $DB->get_record('groups', ['id' => $groupid], 'id,name', IGNORE_MISSING);
    if (!$group) {
        return true;
    }

    $limitedegrupo = (int) get_config('local_qrcurp', 'limitegroup');
    $nohaylimite = false;

    if ((int) get_config('local_qrcurp', 'haygroupespera') === 1) {
        $nameListaEspera = (string) get_config('local_qrcurp', 'namegroupespera');
        if ($nameListaEspera !== '' && stripos($group->name, $nameListaEspera) !== false) {
            $nohaylimite = true;
        }
        if (stripos($group->name, 'cultura') !== false) {
            $limitedegrupo = 40;
        }
    }

    if ($nohaylimite || $limitedegrupo <= 0) {
        return true;
    }

    $totalusersingroup = $DB->count_records('groups_members', ['groupid' => $groupid]);
    return $totalusersingroup < ($limitedegrupo + 1);
}

/**
 * Confirma usuario de manera local cuando no hay auth plugin de auto-registro activo.
 *
 * @param stdClass $user
 * @param string $usersecret
 * @return int
 */
function local_qrcurp_confirm_user_locally(stdClass $user, string $usersecret): int {
    global $DB;

    if ((int) $user->confirmed === 1) {
        return AUTH_CONFIRM_ALREADY;
    }

    if ($usersecret === '' || !hash_equals((string) $user->secret, $usersecret)) {
        return AUTH_CONFIRM_FAIL;
    }

    $updated = $DB->update_record('user', (object) [
        'id' => $user->id,
        'confirmed' => 1,
        'secret' => '',
        'timemodified' => time(),
    ]);

    return $updated ? AUTH_CONFIRM_OK : AUTH_CONFIRM_ERROR;
}

$data = optional_param('data', '', PARAM_RAW);  // Formatted as: secret/username.
$p = optional_param('p', '', PARAM_ALPHANUM);   // Old parameter: secret.
$s = optional_param('s', '', PARAM_RAW);        // Old parameter: username.
$redirectto = optional_param('redirect', '', PARAM_LOCALURL);

$PAGE->set_url('/local/qrcurp/confirm.php');
$PAGE->set_context(context_system::instance());

$authplugin = signup_get_user_confirmation_authplugin();

if (empty($data) && (empty($p) || empty($s))) {
    throw new moodle_exception('errorwhenconfirming');
}

if (!empty($data)) {
    $dataelements = explode('/', $data, 2);
    $usersecret = $dataelements[0] ?? '';
    $username = $dataelements[1] ?? '';
} else {
    $usersecret = $p;
    $username = $s;
}

if ($usersecret === '' || $username === '') {
    throw new moodle_exception('invalidconfirmdata');
}

$username = core_text::strtolower(trim($username));
$user = $DB->get_record('user', ['username' => $username], '*', IGNORE_MISSING);
if (!$user) {
    throw new moodle_exception('errorwhenconfirming');
}

if (!local_qrcurp_group_has_space_for_confirmation((int) $user->department, (int) $user->id)) {
    $url = $CFG->wwwroot . '/login/index.php';
    redirect($url,
        'Lo sentimos, tu confirmación tardó demasiado y el grupo al que intentas registrarte ha superado el límite permitido.',
        null,
        \core\output\notification::NOTIFY_INFO
    );
}

$confirmed = AUTH_CONFIRM_ERROR;
if ($authplugin) {
    $confirmed = $authplugin->user_confirm($username, $usersecret);
}
// Fallback: permitir confirmar desde este plugin incluso si el auto-registro global está deshabilitado.
if (!$authplugin || $confirmed === AUTH_CONFIRM_ERROR) {
    $confirmed = local_qrcurp_confirm_user_locally($user, $usersecret);
}
if ($confirmed !== AUTH_CONFIRM_OK && $confirmed !== AUTH_CONFIRM_ALREADY) {
    throw new moodle_exception('invalidconfirmdata');
}

// Recargar usuario después de confirmar.
$user = get_complete_user_data('id', $user->id);
if (!$user) {
    throw new moodle_exception('cannotfinduser', '', '', s($username));
}

if ($confirmed === AUTH_CONFIRM_OK && !$user->suspended) {
    complete_user_login($user);
    \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

    if (!empty($redirectto)) {
        if (!empty($SESSION->wantsurl)) {
            unset($SESSION->wantsurl);
        }
        redirect($redirectto);
    }
}

if (!empty($user->institution)) {
    $courseurl = new moodle_url('/course/view.php', ['id' => (int) $user->institution]);
    if ($confirmed === AUTH_CONFIRM_OK) {
        // Mantener flujo original del plugin para matrícula/asignación/grupo/correo.
        SetCourseGroupMoodle((int) $user->id, (int) $user->institution, (int) $user->department);
    }
    if ($confirmed === AUTH_CONFIRM_ALREADY) {
        redirect($courseurl, get_string('alreadyconfirmed'), null, \core\output\notification::NOTIFY_INFO);
    }
    $message = 'Usuario verificado con éxito. Revisa tu correo electrónico: ' . $user->email . ' para consultar tus datos de acceso.';
    redirect($courseurl, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

if ($confirmed === AUTH_CONFIRM_ALREADY) {
    redirect(new moodle_url('/login/index.php'), get_string('alreadyconfirmed'), null, \core\output\notification::NOTIFY_INFO);
}

redirect(core_login_get_return_url(), get_string('confirmed'), null, \core\output\notification::NOTIFY_SUCCESS);
