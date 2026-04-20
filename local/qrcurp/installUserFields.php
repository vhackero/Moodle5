<?php

require_once(__DIR__.'/../../config.php');
require_once('./notificaciones.php');
require_once('./lib.php');

echo $OUTPUT->header();

$consultaDataExistente =  $DB->get_records('user_info_field');
$destination = "$CFG->wwwroot/local/qrcurp/notificaciones.php";
$nameUnique = ["cp","estado_residencia","estado_nacimiento","fecha_nacimiento","ocupacion","curp","genero","edad","matricula","rol","rol_name","courseid","grouping"];
$nameShow = ["Codígo Postal", "Estado de residencia","Estado de nacimiento","Fecha de nacimiento","Ocupación","Curp","Género","Edad","Matrícula","Id rol","Rol","Id Curso","Id Grupo"];
if(sizeof($consultaDataExistente)>0){
    foreach ($consultaDataExistente as $item) {
        foreach ($nameUnique as $itemUnique) {
            if($item->shortname == $itemUnique){
                notifyQrCurp(1,"index.php",$item->shortname);
                die();
            }
        }
    }
    addDataUserInfoFieldQrCurp($nameUnique,$nameShow);
}else{
    addDataUserInfoFieldQrCurp($nameUnique,$nameShow);
}


