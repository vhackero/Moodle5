<?php

require_once(__DIR__.'/../../../../config.php');
global $DB;
$idconsultar = $_GET['idcourse'];
$idconsultar  =20;
$consultamoodle = $DB->get_records('course',array('id'=>$idconsultar),'fullname'); //CONSULTA DE LA CURP EN LA BD DE MOODLE
if($consultamoodle){
    echo "course-validate";
}else{
    echo "not-exist-course";
}
die();
echo $html;



