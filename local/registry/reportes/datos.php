<?php
require_once(__DIR__ . '/../../../config.php');
include_once('consults_reports.php');

$data = '';
$data2 = [];
$hayGrafica =0;
//print_object($_POST);
//die();
if(sizeof($_POST)>0){
    if($_POST['rolReport'] != 0){
        $rolReport = $_POST['rolReport'];
        $ArrRolReport = explode("|",$rolReport);
        $rolReport =  $ArrRolReport[0];
        $nameUserReport = $ArrRolReport[1];
    }
    foreach ($_POST as $item) {
        if($item == "all_user_plataform"){
            $data = $item;
            $nameReport = "Reporte de todos los usuario registrados en la plataforma";
            $hayGrafica =0;
        }
        if($item == "all_user_plataform_rol"){
            $data = $item;
            $nameReport = "Reporte de todos los usuarios(".$nameUserReport.") registrados en la plataforma";
            $hayGrafica =0;
        }
        if($item == "all_users_whithout_access"){
            $data = $item;
            $nameReport = "Reporte de todos los usuarios en la plataforma sin ningun acceso";
            $hayGrafica =0;
        }
        if($item == "all_partipants_whithout_access"){
            $data = $item;
            $nameReport = "Reporte de todos los usuarios(".$nameUserReport.") en la plataforma sin ningun acceso";
            $hayGrafica =0;
        }
        if($item == "all_users_whith_one_access"){
            $data = $item;
            $nameReport = "Reporte de todos los usuarios en la plataforma con al menos un acceso";
            $hayGrafica =0;
        }
        if($item == "all_partipants_whith_one_access"){
            $data = $item;
            $nameReport = "Reporte de todos los usuarios(".$nameUserReport.") en la plataforma con al menos un acceso";
            $hayGrafica =0;
        }
        if($item == "all_partipants_in_n_courses_whithout_access"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Reporte de todos los usuarios(".$nameUserReport.") en n cursos sin ningun acceso y ultimo acceso";
            $hayGrafica =0;
        }
        if($item == "all_partipants_active_one_course_n_days"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "(".$nameUserReport.") activos en un curso en específico en n días hasta la fecha";
            $hayGrafica =0;
        }
        if($item == "all_users_active_n_days"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $numday = explode(",",$data2);
            $dias = $numday[0];
            $nameReport = "(".$nameUserReport.") activos en ".$dias." días hasta la fecha";
            $hayGrafica =0;
        }
        if($item == "all_participants_in_actual_category_and_old"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "(".$nameUserReport.") que estuvieron en el trimestre(categoría) anterior y el actual(categoría)";
            $hayGrafica =0;
        }
        if($item == "count_participants_per_groups"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Conteo de todos los usuarios(".$nameUserReport.") separados por grupo en una categoría ";
            $hayGrafica =1;
        }
        if($item == "count_participants_per_groups_types"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Conteo de todos los usuarios(".$nameUserReport.") por grupo y curso en una categoría ";
            $hayGrafica =1;
        }
        if($item == "report_all_data_with_course_and_group"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Conteo de todos los usuarios(".$nameUserReport.") en una categoría con datos generales ";
            $hayGrafica =0;
        }
        if($item == "report_week_access"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Conteo por grupo y curso con activos- inactivos - inactivos por 10 dias(".$nameUserReport.")";
            $hayGrafica =0;
        }
        if($item == "report_status_acreditado"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Acreditación de participantes en un curso (".$nameUserReport.")";
            $hayGrafica =0;
        }
        if($item == "report_access_n_course_with_rol"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Reporte de usuarios con rol que ingresaron una al menos una vez en n cursos (".$nameUserReport.")";
            $hayGrafica =0;
        }
       /* if($item == "custom_sql"){
            $data = $item;
            $data2 = $_POST['idscursos'];
            $nameReport = "Conteo de todos los usuarios(".$nameUserReport.") por grupo en una categoría ";
            $hayGrafica =1;
        }*/
        if($item == "Descargar"){
            $download =1;
        }


    }
}
generateReports($data,$data2,$nameReport,$download,$hayGrafica,$rolReport);
