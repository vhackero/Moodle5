<?php
require_once(__DIR__.'/../../config.php');

function notifyQrCurp($id,$destination = "",$descripcioItem = ""){
    global $CFG;
    if($destination == ''){
        $destination = "$CFG->wwwroot/local/qrcurp/index.php";
    }
    if($id == 1){
        //Notificación para cuando Existe un campo de perfil de usuario repetido
        $message = "El campo <b>".$descripcioItem."</b> ya se encuentra registrado en los campos de perfil de usuario.";
        redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
    }
    if($id == 2){
        //Notificación para cuando no se llenaron los cursos en específico que aparecen en el registro
        $message = "Los ids de cursos en específico se encuentra vacío, configurar correctamente el pluggin QRCURP";
        redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
    }
    if($id == 3){
        //Notificación para cuando esta activo el registro por patron pero no se selecciono un id
        $message = "Debes agregar un id en el patron a seguir para los grupos, configurar correctamente el pluggin QRCURP";
        redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
    }

}

