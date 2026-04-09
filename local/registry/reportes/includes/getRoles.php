<?php

require_once(__DIR__.'/../../../../config.php');
global $DB;

$consultamoodle = $DB->get_records('role',array(),'shortname'); //CONSULTA DE LA CURP EN LA BD DE MOODLE

$html= "<option value=''>Seleccionar un rol</option>";
//Textos en Español
$ArrayES = array(
'coursecreator' => "Creador del curso",
'editingteacher' => "Profesor",
'frontpage' => "Usuario autenticado en la página inicial del sitio",
'guest' => "Invitado",
'manager' => "Manager",
'student' => "Estudiante",
'teacher' => "Profesor sin permiso de edición",
'user' => "Usuario autenticado",
);
$keys = array_keys($ArrayES);
foreach ($consultamoodle as $data)
{
    for ($i=0; $i<= sizeof($keys)-1; $i++){
        $key =$keys[$i];
        $shortname =$data->{"shortname"};
        if($key == $shortname){
            $extraname = $ArrayES[$keys[$i]];
            break;
        }else{
            $extraname = $data->{"name"};
        }
    }
    $html .= "<option value='" . $data->{"id"}."|".$extraname ."'>" . $extraname . "</option>";
}
echo $html;



