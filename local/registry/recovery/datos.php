<?php
require_once(__DIR__.'/../../../config.php');
require_once('../mail/index.php');//Se incluye el archivo que enviará el correo
global $DB;

$curp = $_POST['email'];
$emailSuport = get_config("local_registry","mailsupport");

// SOLUCIÓN: Usar sql_compare_text para la comparación del campo de texto
$sql = "SELECT userid 
        FROM {user_info_data} 
        WHERE " . $DB->sql_compare_text('data') . " = " . $DB->sql_compare_text(':curp');

$params = array('curp' => $curp);
$consultaiduser = $DB->get_record_sql($sql, $params);

// Verificar si se encontró el registro
if (!$consultaiduser) {
    $message = "¡No existe un usuario registrado con la CURP proporcionada!";
    redirect("index.php", $message, null, \core\output\notification::NOTIFY_INFO);
}

$idUser = $consultaiduser->userid;

$consulta = $DB->get_record('user', array('id' => $idUser), 'id,username,idnumber,email,firstname');
$urlsesion = "index.php";

// Verificar si se encontró el usuario
if (!$consulta) {
    $message = "¡No se encontró el usuario asociado! Contactar al administrador para mayor información: $emailSuport";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}

$idusuario = $consulta->id;
$usuario = $consulta->username;
$alias = $consulta->idnumber;
$email = $consulta->email;
$nombre = $consulta->firstname;

if ($alias == '') {
    $message = "¡No se encontró la contraseña para el usuario! Contactar al administrador para mayor información: $emailSuport";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}

if ($usuario != '' && $alias != '') {
    $correoSend = local_registry_envia_correo($idusuario, 5, $alias, $usuario, $nombre);
    if (!$correoSend) {
        $message = "El servidor de correo no esta disponible por el momento, intentalo más tarde.";
        redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_ERROR);
    }
    $message = "Tus credenciales de acceso se han enviado con éxito a $email, revisa tu bandeja de correo electrónico.";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    $message = "¡No existe un usuario registrado con la CURP proporcionada!";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}