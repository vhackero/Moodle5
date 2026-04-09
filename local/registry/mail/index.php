<?php
/**
 * @package local_registry
 * @author  Luis_Felipe
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Función de envío de correo única para el plugin Registry.
 * Se cambió el nombre para evitar colisión con el plugin qrcurp.
 */
function local_registry_envia_correo($iduser, $typemail = '', $alias = '', $idcourse = '', $idgroup = '', $nameCategory = '') {
    global $CFG, $DB;

    $NAMEPLATAFORMREGISTRY = get_config('local_registry', 'nameplataform');

    if ($nameCategory == '') {
        $nameCursos = get_config('local_registry', 'defaultnamecategory');
    } else {
        $nameCursos = $nameCategory;
    }

    $urlprincipal = $CFG->wwwroot . '/login/index.php';
    $urlcourses = $CFG->wwwroot . '/course/view.php?id=';

    $from = $DB->get_record('user', array('id' => $iduser));
    if (!$from) {
        return false;
    }

    // --- BLOQUE PARA OBTENER LA CURP ---
    $curp_display = "";
    $field = $DB->get_record('user_info_field', array('shortname' => 'curp'));
    if ($field) {
        $data = $DB->get_record('user_info_data', array('userid' => $iduser, 'fieldid' => $field->id));
        if ($data) {
            // Se convierte a minúsculas para el usuario de prueba
            $curp_display = strtolower($data->data);
        }
    }
    // ----------------------------------

    $para = $from->email;
    $supportuser = \core_user::get_support_user();
    $user = get_complete_user_data('id', $from->id);
    $correoexterno = get_config('local_registry', 'emailexterno');
    $message = "Bienvenido.";
    $send = false;

    if ($typemail == 0) {
        $subject = "Bienvenida(o) a $NAMEPLATAFORMREGISTRY";
        $mensajehtml = "Estimada/o <strong> $from->firstname $from->lastname </strong>.<br><br>Te damos la más cordial bienvenida al $nameCursos.<br><br>Tus datos de acceso son los siguientes: <br><br> <strong>URL: </strong> <a href='$urlprincipal' target='_blank'>$urlprincipal</a> <br> <strong>Nombre de usuario: </strong> $from->username <br> <strong>Contraseña: </strong> $alias <br><br>Que esta oportunidad de aprendizaje se refleje positivamente en tu desempeño docente.<br><br>Atentamente,<br>Universidad Abierta y a Distancia de México<br>#OrgulloyCorazónUnADM";
        $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
    }

    if ($typemail == 2) {
        $cursoinscrito = $DB->get_record('course', array('id' => $idcourse), 'fullname');
        $nombrecurso = $cursoinscrito->fullname;
        $urlcourses = $urlcourses . $idcourse;
        $subject = "Bienvenida(o) a $nombrecurso";
        $mensajehtml = "Estimada/o <strong> $from->firstname $from->lastname </strong>.<br><br>Te damos la más cordial bienvenida al $nombrecurso.<br><br>Tus datos de acceso son los siguientes: <br><br> <strong>URL: </strong> <a href='$urlcourses' target='_blank'>$urlcourses</a> <br> <strong>Nombre de usuario: </strong> $from->username <br> <strong>Contraseña: </strong> $alias<br><br>Que esta oportunidad de aprendizaje se refleje positivamente en tu desempeño docente.<br><br>Atentamente,<br>Universidad Abierta y a Distancia de México<br>#OrgulloyCorazónUnADM";

        if ($correoexterno == 1) {
            $urlcorreo = '/local/registry/mail/sendmail/index.php/Mail/enviarCorreo/' . $para . '/' . $typemail . '/' . $subject . '/' . $from->firstname . $from->lastname . '/' . $nombrecurso . '/' . $idcourse . '/' . $from->username . '/' . $alias;
            redirect($urlcorreo);
        } else {
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
        }
    }

    if ($typemail == 3) {
        $cursoinscrito = $DB->get_record('course', array('id' => $idcourse), 'fullname');
        $gruposinscrito = $DB->get_record('groups', array('id' => $idgroup), 'name');
        $nombregrupo = $gruposinscrito->name;
        $nombrecurso = $cursoinscrito->fullname;
        $urlcourses = $urlcourses . $idcourse;
        $subject = "Bienvenida a $nombrecurso";
        $mensajehtml = "Estimada/o <strong> $from->firstname $from->lastname </strong>.<br><br>Te damos la más cordial bienvenida al $nombrecurso, en el grupo de $nombregrupo. <br><br>Tus datos de acceso son los siguientes : <br><br> <strong>URL: </strong> <a href='$urlcourses' target='_blank'>$urlcourses</a> <br> <strong>Nombre de usuario: </strong> $from->username <br> <strong>Contraseña: </strong> $alias<br><br>Agradecemos tu interés y participación.";

        if ($correoexterno == 1) {
            $urlcorreo = '/local/registry/mail/sendmail/index.php/Mail/enviarCorreo/' . $para . '/' . $typemail . '/' . $subject . '/' . $from->firstname . $from->lastname . '/' . $nombrecurso . '/' . $nombregrupo . '/' . $idcourse . '/' . $from->username . '/' . $alias;
            redirect($urlcorreo);
        } else {
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
        }
    }

    if ($typemail == 4) {
        $username = $from->username;
        $secretConfirm = $from->secret;
        $urlconfirmusers = $CFG->wwwroot . '/local/registry/confirm.php?data=' . $secretConfirm . '/' . $username;
        $subject = $nameCursos . " :: Confirmación de registro";
        $mensajehtml = "Hola, $from->firstname $from->lastname.<br><br> Se ha solicitado una nueva cuenta en '" . $nameCursos . "' utilizando tu dirección de correo electrónico. <br><br> Para confirmar tu nueva cuenta, copia y pega la siguiente URL en tu navegador: <br><br> <strong>$urlconfirmusers</strong><br><br>Agradecemos tu interés y participación.<br><br>Que esta experiencia de aprendizaje contribuya al fortalecimiento de tu labor docente.<br><br>Atentamente,<br>Universidad Abierta y a Distancia de México<br>#OrgulloyCorazónUnADM";

        if ($correoexterno == 1) {
            $urlcorreo = '/local/registry/mail/sendmail/index.php/Mail/enviarCorreo/' . $para . '/' . $typemail . '/' . $subject . '/' . $nameCursos . '/' . $secretConfirm . '/' . $username;
            redirect($urlcorreo);
        } else {
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
        }
    }

    if ($typemail == 5) {
        $nombre = $idgroup; // Se asume que en este tipo el nombre viene en el parámetro idgroup
        $username_rec = $idcourse;
        $subject = "Recuperación de credenciales de acceso - " . $nameCursos;
        $mensajehtml = "Estimado $nombre : <br><br>Hemos recibido una petición de recuperación de acceso en $NAMEPLATAFORMREGISTRY <br><br> <strong>Nombre de usuario: </strong> $username_rec <br> <strong>Contraseña: </strong> $alias <br><br> <hr>";

        if ($correoexterno == 1) {
            $urlcorreo = '/local/registry/mail/sendmail/index.php/Mail/enviarCorreo/' . $para . '/' . $typemail . '/' . $subject . '/' . $nombre . '/' . $username_rec . '/' . $alias;
            redirect($urlcorreo);
        } else {
            $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
        }
    }

    if ($typemail == 6) {
        $cursoinscrito = $DB->get_record('course', array('id' => $idcourse), 'fullname');
        $gruposinscrito = $DB->get_record('groups', array('id' => $idgroup), 'name');
        $nombregrupo = $gruposinscrito->name;
        $nombrecurso = $cursoinscrito->fullname;
        $urlcourses = $urlcourses . $idcourse;
        $subject = "Bienvenida a el curso de $nombrecurso, $NAMEPLATAFORMREGISTRY";
        $mensajehtml = "Estimada/o <strong> $from->firstname $from->lastname </strong>.<br><br>Te damos la más cordial bienvenida al $nombrecurso, en el grupo de $nombregrupo. <br><br>Tus datos de acceso son los siguientes : <br><br> <strong>URL: </strong> <a href='$urlcourses' target='_blank'>$urlcourses</a> <br> <strong>Nombre de usuario: </strong> $from->username <br> <strong>Contraseña: </strong> $alias<br><br>Que esta experiencia de aprendizaje contribuya al fortalecimiento de tu labor docente.<br><br>Atentamente,<br>Universidad Abierta y a Distancia de México<br>#OrgulloyCorazónUnADM";
        $send = email_to_user($user, $supportuser, $subject, $message, $mensajehtml);
    }

    return $send;
}