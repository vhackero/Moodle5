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
$idgroup = 10001;
//Desmatricula a el usuario del curso de espera
$instance = $DB->get_record('enrol',array("courseid"=>$idcourse, "enrol"=>"manual"));
$plugin = enrol_get_plugin($instance->enrol);
$plugin->unenrol_user($instance, $idUser);
//Enviado data para agregar a los usuarios al curso
local_registry_set_course_group($idUser,$idcourse,$idgroup);

$destination = "index.php?idcurso=".$idcourse;
$message = "Incripción realizada con éxito.";
redirect($destination, $message, 15, \core\output\notification::NOTIFY_SUCCESS);

