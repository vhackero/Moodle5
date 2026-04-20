<?php
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("location: index.php");
    die();
}
//require ('../externals/conexion.php');
require_once(__DIR__.'/../../../config.php');
global $DB;



$iduser = $_POST['iduser'];
$courseid = $_POST['courseid'];

$validateuser = 1;


if($courseid != ''){
   $enrolinstances =  enrol_get_instances($courseid,true);
   foreach ($enrolinstances as $enrolinstance){
       if($enrolinstance->enrol == 'self'){
           $validateuser = 0;
           break;
       }
   }
}else{
    $courseid = 0;
    $validateuser = 0;
}
if($validateuser) {
    $data = enrol_get_all_users_courses($iduser);
//$iduser = 40000;
    foreach ($data as $datum) {
        $customcerts = $DB->get_records('customcert', array('course' => $datum->id));
        foreach ($customcerts as $customcert) {
            $consultamoodleuser = $DB->get_record('customcert_issues', array('userid' => $iduser, 'customcertid' => $customcert->id));
            if ($consultamoodleuser and $consultamoodleuser->code != '') {
                echo $consultamoodleuser->code;
                die();
            }
        }
    }
}else{
    echo $courseid;
    die();

}



