<?php
require('../../config.php');
require_once('classes/event/consult_normateca_unadm.php');

$serverData = "";

//Conexión
$host = get_config('block_normateca_unadm','hostdb');
$userdb = get_config('block_normateca_unadm','userdb');
$passdb = get_config('block_normateca_unadm','passdb');
$db = get_config('block_normateca_unadm','dbname');
$mysqli = new mysqli($host, $userdb, $passdb, $db);
/* Comprobando si hay un error de conexión. */
if ($mysqli->connect_error) {
    echo "Falló la conexión: error_connect_normatecaunadm" . $mysqli->connect_error;
    exit();
}

$mysqli->set_charset('utf8');

//Divisiones
if(isset( $_POST['funtion']) && $_POST['funtion'] == 'getDivisiones' ){
    $filtro='activo=1';
    $ordenamiento='nombre_largo';
    $consulta = "SELECT * FROM cat_divisiones WHERE $filtro ORDER BY $ordenamiento ASC ";
    $result = $mysqli->query($consulta);
    $dataSend = '<option value="0">Seleccionar</option>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dataSend.='<option value="'.$row['id_division'].'">'.$row['nombre_largo'].'</option>';
        }
    }else{
        $dataSend='error_not_data';
    }
    echo $dataSend;
    die();
}

//Carreras
if(isset( $_POST['funtion']) && $_POST['funtion'] == 'getCarreras' ){
if(isset( $_POST['id_division']) && $_POST['id_division'] > 0 ){
    $filtro='estatus=1 and id_division='.$_POST['id_division'];
    $ordenamiento='nombre_largo';
    $consulta = "SELECT * FROM cat_carreras WHERE $filtro ORDER BY $ordenamiento ASC ";
    $result = $mysqli->query($consulta);
    $dataSend = '<option value="0">Seleccionar carrera</option>';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dataSend.='<option value="'.$row['id_carrera'].'">'.$row['nombre_largo'].'</option>';
        }
    }else{
        $dataSend='error_not_data';
    }
    echo $dataSend;
}else{
    $dataSend='error_not_division';
}
    die();
}

//Dependendias
if(isset( $_POST['funtion']) && $_POST['funtion'] == 'getDependencias' ){
    $filtro='estatus=1';
    $ordenamiento='nombre_largo';
    $consulta = "SELECT * FROM cat_dependencias WHERE $filtro ORDER BY $ordenamiento ASC ";
    $result = $mysqli->query($consulta);
    $dataSend = '<option value="0">Seleccionar dependencia</option>';
    $dataSend .= '<option selected value="all">Todas</option>';


    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dataSend.='<option value="'.$row['id_dependencia'].'">'.$row['nombre_largo'].'</option>';
        }
    }else{
        $dataSend='error_not_data_dependencia';
    }
    echo $dataSend;
    die();
}

//Recursos
if(isset( $_POST['funtion']) && $_POST['funtion'] == 'getRecurso' ){
    $filtro='estatus=1';
    $ordenamiento='nombre';
    $consulta = "SELECT * FROM cat_recurso WHERE $filtro ORDER BY $ordenamiento ASC ";
    $result = $mysqli->query($consulta);
    $dataSend = '<option value="0">Seleccionar recurso</option>';
    $dataSend .= '<option selected value="all">Todos</option>';

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dataSend.='<option value="'.$row['id_recurso'].'">'.$row['nombre'].'</option>';
        }
    }else{
        $dataSend='error_not_data_recurso';
    }
    echo $dataSend;
    die();
}

//Variables

$urlImage = get_config('block_normateca_unadm','siteexternal');
$columallelement= "all";

//if(isset( $_POST['filtro']) && isset( $_POST['busqueda'])) {
if(isset( $_POST['filtro'])) {
    $filtro = $_POST['filtro'];
    $filtro2 = $_POST['filtro2'];
    $filtro3 = $_POST['filtro3'];
    $filtro4 = $_POST['filtro4'];
    $consulta = '';
//    $busqueda = $_POST['busqueda'];
    if ($filtro != '' && $filtro2 != '') {
//    if ($filtro != '' ) {
//    echo "Busqueda solo por busqueda avanzada";
        if ($filtro == $columallelement and $filtro2 == $columallelement and (is_numeric($filtro3) and $filtro3 != 0) and (is_numeric($filtro4) and $filtro4 != 0)) {
            //Busqueda general básica
            $complementaria = " WHERE cd.id_division = " . $filtro3 . " AND cc.id_carrera = " . $filtro4;
            $consulta = "SELECT DISTINCT(dn.id_documento),cd.nombre_corto division,dn.division division_largo,dn.carrera,dn.indicaciones_carrera,dn.dependencia_sigla,dn.dependencia,dn.documento_normativo,dn.link_acceso,dn.recurso,dn.nombre_imagen,cd.nombre_corto
                FROM cat_carreras cc
                JOIN cat_divisiones cd ON cc.id_division = cd.id_division
                JOIN documentos_normativos dn ON dn.division = cd.nombre_largo AND dn.carrera = cc.nombre_largo
                JOIN cat_dependencias cds ON cds.nombre_largo = dn.dependencia
                JOIN cat_recurso cr ON cr.nombre = dn.recurso" . $complementaria;
        } else if ($filtro2 == $columallelement and is_numeric($filtro) and is_numeric($filtro3) and is_numeric($filtro4)) {
            //Busqueda general básica con recursos
            $complementaria = " WHERE cd.id_division = " . $filtro3 . " AND cc.id_carrera = " . $filtro4 . " AND cr.id_recurso = " . $filtro;
            $consulta = "SELECT DISTINCT(dn.id_documento),cd.nombre_corto division,dn.division division_largo,dn.carrera,dn.indicaciones_carrera,dn.dependencia_sigla,dn.dependencia,dn.documento_normativo,dn.link_acceso,dn.recurso,dn.nombre_imagen,cd.nombre_corto
                FROM cat_carreras cc
                JOIN cat_divisiones cd ON cc.id_division = cd.id_division
                JOIN documentos_normativos dn ON dn.division = cd.nombre_largo AND dn.carrera = cc.nombre_largo
                JOIN cat_dependencias cds ON cds.nombre_largo = dn.dependencia
                JOIN cat_recurso cr ON cr.nombre = dn.recurso" . $complementaria;
        }
        else if ($filtro == $columallelement and is_numeric($filtro2) and is_numeric($filtro3) and is_numeric($filtro4)) {
            //Busqueda general básica con recursos
            $complementaria = " WHERE cd.id_division = " . $filtro3 . " AND cc.id_carrera = " . $filtro4 . " AND cds.id_dependencia = " . $filtro2;
            $consulta = "SELECT DISTINCT(dn.id_documento),cd.nombre_corto division,dn.division division_largo,dn.carrera,dn.indicaciones_carrera,dn.dependencia_sigla,dn.dependencia,dn.documento_normativo,dn.link_acceso,dn.recurso,dn.nombre_imagen,cd.nombre_corto
                FROM cat_carreras cc
                JOIN cat_divisiones cd ON cc.id_division = cd.id_division
                JOIN documentos_normativos dn ON dn.division = cd.nombre_largo AND dn.carrera = cc.nombre_largo
                JOIN cat_dependencias cds ON cds.nombre_largo = dn.dependencia
                JOIN cat_recurso cr ON cr.nombre = dn.recurso" . $complementaria;
        }
        else if (is_numeric($filtro2) and is_numeric($filtro) and is_numeric($filtro3) and is_numeric($filtro4)) {
            //Busqueda general completa
            $complementaria = " WHERE cd.id_division = " . $filtro3 . " AND cc.id_carrera = " . $filtro4 . " AND cr.id_recurso = " . $filtro . " AND cds.id_dependencia = " . $filtro2;
            $consulta = "SELECT DISTINCT(dn.id_documento),cd.nombre_corto division,dn.division division_largo, dn.carrera,dn.indicaciones_carrera,dn.dependencia_sigla,dn.dependencia,dn.documento_normativo,dn.link_acceso,dn.recurso,dn.nombre_imagen,cd.nombre_corto
                FROM cat_carreras cc
                JOIN cat_divisiones cd ON cc.id_division = cd.id_division
                JOIN documentos_normativos dn ON dn.division = cd.nombre_largo AND dn.carrera = cc.nombre_largo
                JOIN cat_dependencias cds ON cds.nombre_largo = dn.dependencia
                JOIN cat_recurso cr ON cr.nombre = dn.recurso" . $complementaria;
        }
        $result = '';
        if ($consulta != '') {
            $result = $mysqli->query($consulta);
        }
        $dataSend = "<div class='row resultados-normateca active-item-normateca'>";
        $contador = 0;
        $contador2 = 0;
        $filter1 = '';
        $filter2 = '';
        $filter3 = '';
        $filter4 = '';
        $newbox = 0;
        if ($result){
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $contador++;
                    $contador2++;

                    if ($contador2 == 1) {
                        // datos a enviar para el registro de bitacoras
                        $filter1 = $row['division_largo'];
                        $filter2 = $row['carrera'];
                        if ($filtro == $columallelement and $filtro2 == $columallelement) {
                            $filter3 = 'Todas';
                            $filter4 = 'Todas';
                        } else if ($filtro == $columallelement and $filtro2 != $columallelement) {
                            $filter3 = 'Todas';
                            $filter4 = $row['recurso'];
                        } else if ($filtro != $columallelement and $filtro2 == $columallelement) {
                            $filter3 = $row['dependencia'];
                            $filter4 = 'Todas';
                        } else {
                            $filter3 = $row['dependencia'];
                            $filter4 = $row['recurso'];
                        }

                    }

                    $imageitem = $row['division'] . "/" . $row['nombre_imagen'];
                    $nameitem = $row['documento_normativo'];
                    $urlitem = $row['link_acceso'];
                    $indicaciones = $row['indicaciones_carrera'];

                    //Para corregir las comillas dobles
                    $indicaciones = str_replace('"','\"',$indicaciones);
                    $nameorig = $nameitem;
                    $nameitem = str_replace('"','\"',$nameitem);

                    if ($contador <= 4) {
                        if(strstr($urlitem,'.pdf')) {
                            $dataSend = $dataSend . "
                            <div class='col-md-3 container-info-normateca d-flex card'>
                             <img alt='$imageitem' title='$imageitem' src='" . $urlImage . $imageitem . "?v=1'>
                            <p class='p-form-card'>" . $nameorig . "</p>
                            <div class='mt-auto'>
                            <button data-item='$urlitem"."|"."$nameitem"."|"."$indicaciones' class='urlelements button-form-card' >
                                <a class ='btn color-white'>
                                    <span>Ver Recurso</span>
                                    <i class='fa fa-eye' aria-hidden='true'></i>
                                </a>
                            </button>
                            </div>
                            </div>
                            ";
                        }else{
                            $dataSend = $dataSend . "
                            <div class='col-md-3 container-info-normateca d-flex card'>
                             <img alt='$imageitem' title='$imageitem' src='" . $urlImage . $imageitem . "?v=1'>
                            <p class='p-form-card'>" . $nameorig . "</p>
                            <div class='mt-auto'>
                            <button class='button-form-card' >
                                <a target='_blank' href='$urlitem' class ='btn color-white'>
                                    <span>Ver Recurso</span>
                                    <i class='fa fa-eye' aria-hidden='true'></i>
                                </a>
                            </button>
                            </div>
                            </div>
                            ";
                        }
                    }
                    if ($contador == 4) {
                        if ($contador2 < $result->num_rows) {
                            $dataSend = $dataSend . "</div>";
                            $contador = 0;
                            $dataSend = $dataSend . "<div class='row resultados-normateca'>";
                            $newbox = 1;
                        }
                    }
                }
                //Creación de log de bitácoras
                if ($filtro != '' && $filtro2 != '') {
                    global $USER;
//                $filtros = ['resource_tecnica'=>'Nombre de técnica','resource_type_resource'=>'Nivel taxonómico básico','resource_no_tecnica'=>'Número de técnica','resource_keywords'=>'Palabras clave'];
                    $couserid = $_POST['courseid_normateca'];
                    $event = \block_normateca_unadm\event\consult_normateca_unadm::create(array(
                        'context' => context_course::instance($couserid),
                        'other' => array('filtro1' => $filter1, 'filtro2' => $filter2, 'filtro3' => $filter3, 'filtro4' => $filter4,
                        ),
                        'userid' => $USER->id,
                    ));
                    $event->trigger();
                }
                echo $dataSend;
            }else {
                printNotData();
            }
        }
        else {
            printNotData();
        }
    }
}else{
    printNotData();
}
function printNotData(){
    echo '<div class="wrapper centrar-contenido">';
    echo '<p class="searchNotValue-normateca">No se han encontrado resultados para tu búsqueda.</p>';
    echo '</div>';
}
