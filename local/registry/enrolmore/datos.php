<?php
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("location: index.php");
    die();
}
require_once(__DIR__.'/../../../config.php');
require_once('../mail/index.php');//Se incluye el archivo que enviará el correo
require_once('../lib.php');

global $DB;

$correo= $_POST['email'];
$idUser = $_POST['iduser'];
$idcourse = $_POST['curso'];
$idcourses = $_POST['oldcurso'];
$idgroup = $_POST['grupos'];
//Enviado data para agregar a los usuarios al curso
$typemeail = 6; //Para cuando se matricularan a usuarios de lista de espera
$matriculacion = local_registry_set_course_group($idUser,$idcourse,$idgroup,$typemeail);
$destination = "$CFG->wwwroot/index.php";
echo $OUTPUT->header();
if($matriculacion){
    $message = "Matriculación realizada con éxito! Se ha enviado un mensaje a tu dirección de correo electrónico ".$correo." con los detalles de tu matriculación.";
    redirect($destination, $message, 15, \core\output\notification::NOTIFY_SUCCESS);
}else{
    $message = "Matriculación realizada con éxito! El servidor de correo no esta disponible por el momento. Puedes revisar tus datos de acceso desde el apartado de Recuperar nombre de usuario y/o contraseña. ";
    redirect($destination, $message, 15, \core\output\notification::NOTIFY_INFO);
}

