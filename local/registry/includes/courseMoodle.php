<?php

//require ('../externals/conexion.php');
require_once(__DIR__.'/../../../config.php');
global $DB;
$courseid = $_POST['course'];
//$categoryid = 3;
//$correo = "felipealcocersosa@gmail.com";

$consultamoodle = $DB->get_records('course',array('id'=>$courseid,'visible'=>1),'','id,fullname'); //CONSULTA DE LA CURP EN LA BD DE MOODLE

//$html= "<option value=''>Seleccionar</option>";

foreach ($consultamoodle as $data)
{
        $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
}
echo $html;



