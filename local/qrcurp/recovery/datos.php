<?php
require_once(__DIR__.'/../../../config.php');
require_once('../mail/index.php');//Se incluye el archivo que enviará el correo
global $DB;

$curp= $_POST['email'];
//$email = 'piipeliin@gmail.com';

$consultaiduser = $DB->get_record('user_info_data',array('data'=>$curp),'userid');
$emailSuport = get_config("local_qrcurp","mailsupport");

$idUser = $consultaiduser->userid;

$consulta = $DB->get_record('user',array('id'=>$idUser),'id,username,idnumber,email,firstname');
$urlsesion = "index.php";
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