<?php

//require ('../externals/conexion.php');
require_once(__DIR__.'/../../../config.php');
require_once('../notificaciones.php');

global $DB;
$categoryid = $_POST['idcategoria'];
$idrol = $_POST['idrol'];
//$categoryid = 1;
//$correo = "felipealcocersosa@gmail.com";
$ordeAlfabetico = get_config("local_qrcurp","coursealphaorder"); //Ordenara los cursos alfabéticamente
$ordeby = '';
if($ordeAlfabetico){
    $ordeby = "fullname"; //columno de la tabla course
}
$courseid = '';
$roldata = '';
$oncedata  ='';
$dataValidate = [];

$consultamoodle = $DB->get_records('course',array('category'=>$categoryid,'visible'=>1),$ordeby,'id,fullname'); //CONSULTA DE LA categoria EN LA BD DE MOODLE
$acceptcoursesdbexternal = get_config("local_qrcurp","acceptcoursesdbexternal");
$muestraCursosEnEspacifico =  get_config('local_qrcurp','showuniquecourses');    //aceptará registro en cursos en epecífico

if($acceptcoursesdbexternal == 1){
    if($muestraCursosEnEspacifico == 0 ){
        echo 'error_not_active_config';
        die();
    }
    $accessbyroltocourse = get_config("local_qrcurp","accessbyroltocourse");
    if(strstr($accessbyroltocourse,'|')){
        $oncedata = 0;
        //Existe mas de una regla
        $datacourses = explode('|',$accessbyroltocourse);
            //trabaja con cada regla
            foreach ($datacourses as $datacourse) {

                if(strstr($datacourse,'{') AND strstr($datacourse,'}') AND strstr($datacourse,',')){
                    //intenta crear el arreglo a partir de los datos
                    $lipiezadata = str_replace('{','',$datacourse);
                    $lipiezadata = str_replace('}','',$lipiezadata);
                }
                $dato = explode(':', $datacourse);

                if(isset($lipiezadata)) {
                    $dato =[];
                    $datamodified = explode(':', $lipiezadata);
                    if (isset($datamodified[1]) AND strstr($datamodified[1], ',')) {
                        $datos = explode(',', $datamodified[1]);
                        $count = 1;
                        foreach ($datos as $item){
                            if($count == 1){
                                array_push($dato,$datamodified[0]);
                                array_push($dato,$item);
                                $count++;
                                array_push($dataValidate, $dato);
                                $dato = [];
                                $count = 1;
                            }
                        }
                    }
                }else {
                    if(strstr($datacourse,'{') AND strstr($datacourse,'}')){
                        $datacourse = str_replace('{','',$datacourse);
                        $datacourse = str_replace('}','',$datacourse);
                    }
                    $dato = explode(':', $datacourse);
                    array_push($dataValidate, $dato);
                }
            }
    }else{

        if(strstr($accessbyroltocourse,'{') AND strstr($accessbyroltocourse,'}') and strstr($accessbyroltocourse,',')){
            //intenta crear el arreglo a partir de los datos
            $lipiezadata = str_replace('{','',$accessbyroltocourse);
            $lipiezadata = str_replace('}','',$lipiezadata);
            if(isset($lipiezadata)) {
                $dato =[];
                $datamodified = explode(':', $lipiezadata);
                if (isset($datamodified[1]) AND strstr($datamodified[1], ',')) {
                    $datos = explode(',', $datamodified[1]);
                    $count = 1;
                    foreach ($datos as $item){
                        if($count == 1){
                            array_push($dato,$datamodified[0]);
                            array_push($dato,$item);
                            $count++;
                            array_push($dataValidate, $dato);
                            $dato = [];
                            $count = 1;
                        }
                    }
                }
                $oncedata =0;
            }
        }else {
            //trabaja con la única regla
            $data = explode(':', $accessbyroltocourse);
            $roldata = $data[0];
            $courseid = $data[1];
            $oncedata = 1;
        }
    }
    /*echo 'Rol:';
    print_object($roldata);
    echo '<br>';
    echo 'Courseid:';
    print_object($courseid);*/
}

$idscourses =  get_config('local_qrcurp','showuniquecourseslist');    //id de los cursos
$idsArray = [];
$html = "<option value=''>Seleccionar</option>";

if($idscourses != '') {
    $idsArray = explode(",", $idscourses);
    $existecurso = 0;
}

    foreach ($consultamoodle as $data) {

        if ($muestraCursosEnEspacifico == 1) {
            if ($idscourses == '' AND $acceptcoursesdbexternal == 0 ) {
                echo 'not_config_courses';
                die();
            }
            else if($acceptcoursesdbexternal == 1 AND $accessbyroltocourse == ''){
                $html = "<option value=''>Actualmente no existen cursos disponibles para inscribirse.</option>";
            }
            else {
                foreach ($idsArray as $item) {
                    if ($data->{"id"} == $item) {
                        $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                        //Valida el curso solo para roles en espacífico y no mostrarlo dos veces en caso de que exista en los dos paramretros
                        if ($oncedata == 1 and $item == $courseid) {
                            $existecurso = 1;
                        }
                    }
                }

                if ($oncedata == 0) {
                    //Valida los cursos para roles en especifico
                    foreach ($dataValidate as $dat) {
                        //Valida el rol
                        if ($dat[0] == $idrol and $dat[1] == $data->{"id"}) {
                            //Valida el curso por si se capturar en la lista de cursos a mostrar
                            if (!strstr($html, "<option value='" . $data->{"id"})) {
                                $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                                break;
                            }

                        }

                    }
                } else {
                    //Valida los cursos para roles en especifico
                    if ($oncedata == 1 and $data->{"id"} == $courseid and $existecurso == 0 and $roldata == $idrol) {
                        $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                    }
                }
            }
        } else {
            $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
        }


    }
    if($html == "<option value=''>Seleccionar</option>"){
        $html = "<option value=''>Actualmente no existen cursos disponibles para inscribirse.</option>";
    }
echo $html;



