<?php
require_once ("consults_reports.php");
require_once ("export.php");
require_once ("graph.php");
require_once(__DIR__.'/../../../../config.php');
global $DB,$CFG;
//print_object($_POST);
//die();
//consulta y extracción de datos;
if(isset($_GET['funcion']) && !empty($_GET['funcion'])) {
    $funcion = $_GET['funcion'];
    $parametros = $_GET['parametros'];
    $submitType = $_GET['submitType'];
    $rol = $parametros[0]['value'];
    $rol = explode("|",$rol);
    $rolnumber = $rol[0];
    $rolname = $rol[1];
    $hayGrafica = 0;
    $typequery = $parametros[1]['value'];
    $dataExtra = $parametros[2]['value'];
    //En función del parámetro que nos llegue ejecutamos una función u otra
    switch($funcion) {
        case 'generateReport':
            $data = generateReports($typequery,$dataExtra,$rolnumber);
            if(gettype($data)!= "string"){
                createGraph($data);
            }else{
                echo $data;
                die();
            }
            break;
//        case 'selectCourses':
//            selectCourses($idcurso);
//            break;
    }
}
if(isset($_POST['menuReport']) && !empty($_POST['menuReport'])) {
    $dataExtra = $_POST['data'];
    $rol = $_POST['rolReportSelect'];
    $rol = explode("|",$rol);
    $rolnumber = $rol[0];
    $rolname = $rol[1];
    $typequery =  $_POST['menuReport'];
    $hayGrafica = $_POST['hay_grafica'];
    $nameReport = $typequery."_".$rolname;
    $data = generateReports($typequery,$dataExtra,$rolnumber);
    if(gettype($data)!= "string"){
        $reporte = createExport($data,$nameReport,$hayGrafica);
        if($reporte == "image-not-exist"){
            $destination = "../index.php";
            $message = "La imagen del gráfico aun no se termina de subir al servidor o no tiene permisos de escritura, Vuelve a intentar Descargar, si el problema continua contacta al administrador.";
            redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
        }
    }else{
        if($data == "not_data_show" ){
            $destination = "../index.php";
            $message = "El reporte que intentas realizar no tiene datos que mostrar.";
            redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
        }
        if($data == 'not_query_associated'){
            $destination = "../index.php";
            $message = "No existe una consulta asociada a el item seleccionado, revistar el archivo consults_reports.php";
            redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
        }
        die();
    }
}else{
    $destination = "../index.php";
    $message = "Los datos no son validos.";
    redirect($destination, $message, 15, \core\output\notification::NOTIFY_WARNING);
}



