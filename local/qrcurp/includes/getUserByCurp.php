<?php
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("location: index.php");
    die();
}
//require ('../externals/conexion.php');
require_once(__DIR__.'/../../../config.php');
global $DB;
$curp = $_POST['curp'];

$consultamoodleuser = $DB->get_record('user_info_data',array('data'=>$curp)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$iduser = $consultamoodleuser->userid;
$consultamoodle = $DB->get_record('user',array('id'=>$iduser)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$email = $consultamoodle->email;
$data = enrol_get_all_users_courses($iduser);

//para obtener el id rol del usuario
$idrol = 0;
if($iduser != '') {
    require_once($CFG->dirroot . '/user/profile/lib.php');
    $userinfo = profile_user_record($iduser);
    $idrol = $userinfo->rol;

}
foreach ($data as $data)
{
    $html.= "<option value='".$data->{"id"}."'>".$data->{"fullname"}."</option>";
}
echo $html;
echo "|".$iduser."|".$email."|".$idrol;



