<?php
require_once(__DIR__.'/../../../config.php');
require_once('../mail/index.php');//Se incluye el archivo que enviará el correo
global $DB;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
require_sesskey();

$curp = optional_param('email', '', PARAM_ALPHANUMEXT);
$curp = core_text::strtoupper(trim($curp));
$urlsesion = "index.php";
if ($curp === '' || strlen($curp) !== 18) {
    $message = "¡No existe un usuario registrado con la CURP proporcionada!";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}
//$email = 'piipeliin@gmail.com';

$field = $DB->get_record('user_info_field', array('shortname' => 'curp'), 'id');
if (!$field) {
    $message = "¡No existe un usuario registrado con la CURP proporcionada!";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}
$sqlcurp = "SELECT uid.userid
              FROM {user_info_data} uid
             WHERE uid.fieldid = :fieldid
               AND " . $DB->sql_compare_text('uid.data') . " = " . $DB->sql_compare_text(':curp');
$consultaiduser = $DB->get_record_sql($sqlcurp, ['fieldid' => (int)$field->id, 'curp' => $curp]);
$emailSuport = get_config("local_qrcurp","mailsupport");

$idUser = $consultaiduser->userid ?? 0;
if (!$idUser) {
    $message = "¡No existe un usuario registrado con la CURP proporcionada!";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}

$consulta = $DB->get_record('user',array('id'=>$idUser),'id,username,idnumber,email,firstname');
$consulta = $consulta ?: new stdClass();
$idusuario = $consulta->id;
 $usuario = $consulta->username;
$alias = $consulta->idnumber;
 $email = $consulta->email;
 $nombre = $consulta->firstname;
if($alias == ''){
    $message = "¡No se encontró la contraseña para el usuario! Contactar al administrador para mayor información: $emailSuport ";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}
if($usuario != '' && $alias != ''){
    $correoSend = enviaCorreo($idusuario,5,$alias,$usuario,$nombre);
    if(!$correoSend){
        $message = "El servidor de correo no esta disponible por el momento, intentalo más tarde.";
    }
    $message = "Tus credenciales de acceso se han enviado con éxito a $email , revisa tu bandeja de correo electrónico.";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}else{
    $message = "¡No existe un usuario registrado con la CURP proporcionada!";
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_INFO);
}
