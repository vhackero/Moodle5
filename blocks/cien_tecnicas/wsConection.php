<?php
require('../../config.php');
require_once('classes/event/consult_cien_tecnicas.php');

$mysqli = new mysqli('172.18.30.49' , 'consulta_tecnicas' , '-G20kf5!_XwsTw!.U', '100tecnicasdidacticas');
$serverData = "";
if ($mysqli->connect_errno) {
    echo "Falló la conexión: error_connect_database100tecnicas" . $mysqli->connect_error;
    exit();
}

$mysqli->set_charset('utf8');

//Variables
$colorsNivel = [
    '1'=>'#7351a1',
    '2'=>'#ee7b45',
    '3'=>'#4ead49',
    '4'=>'#dbb11e',
    '5'=>'#4579ae',
    '6'=>'#e01f8f',
];
$secondaryColorsNivel = [
    '1'=>'#ac99c9',
    '2'=>'#ecba94',
    '3'=>'#aecf94',
    '4'=>'#ebd28d',
    '5'=>'#8bb4dc',
    '6'=>'#f7acba',
];
$urlPdfGenerator = "https://100tecnicasdidacticas.unadmexico.mx/generate_get_PDF.php?numeroDeTecnica=";
$urlImage = "https://100tecnicasdidacticas.unadmexico.mx/local/resource_repository/docs/";
$columtiperesource= "resource_type_resource";
$columnumtecnica= "resource_no_tecnica";

if(isset( $_POST['filtro']) && isset( $_POST['busqueda'])) {
    $filtro = $_POST['filtro'];
    $busqueda = $_POST['busqueda'];

    if ($filtro != '' && $busqueda != '') {
//    echo "Busqueda solo por busqueda avanzada";
        if($filtro == $columtiperesource ){
            //Para buscar tanto por nombre o número de nivel
            $consulta = "SELECT * FROM mdl_local_resource_repository WHERE $filtro LIKE '%$busqueda%' OR resource_nivel LIKE '%$busqueda%'   ORDER BY $filtro ASC ";
        }else if( $filtro == $columnumtecnica ){
            //Para buscar cuando es por número de técnica
            $consulta = "SELECT * FROM mdl_local_resource_repository WHERE $filtro = $busqueda ORDER BY $filtro ASC ";
        }
        else{
            $consulta = "SELECT * FROM mdl_local_resource_repository WHERE $filtro LIKE '%$busqueda%' ORDER BY $filtro ASC ";
        }
        $result = $mysqli->query($consulta);
        $dataSend = '';
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $imageTecnica = $row['resource_nombre_img'];
                $nameTecnica = $row['resource_tecnica'];
                $numTecnica = $row['resource_no_tecnica'];
                $nivelTecnica = $row['resource_nivel'];
                $colorTecnica = $colorsNivel[$nivelTecnica];
                $colorTecnicaSecondary = $secondaryColorsNivel[$nivelTecnica];
                $dataSend = $dataSend. "<div class='form-card row' style='background: ".$colorTecnicaSecondary."'>
                    <div class='col-md-4 img-container-form-card'>
                                            <p class='extra-card-info'>".$numTecnica."</p>
                                            <img src='".$urlImage.$imageTecnica."?v=1.0'>
                                            <p class='extra-card-info'>".$nivelTecnica."</p>
                    </div>
                    <div class='col-md-12 container-info'>
                    <p class='p-form-card'>".$nameTecnica."</p>
                    <input type='hidden' name='numeroDeTecnica' id='numeroDeTecnica' value='".$numTecnica."'>
                    <button class='button-form-card' style='background: ".$colorTecnica."' >
                        <a href='".$urlPdfGenerator.$numTecnica."' target='_blank' class ='btn color-white'>
                            <i class='fa fa-eye' aria-hidden='true'></i>
                            <span>Ver PDF</span>
                        </a>
                    </button>
                    </div>
                    
                    </div>";
            }
            //Creación de log de bitácoras
            if($filtro != '' && $busqueda != '' ){
                global $USER;
                $filtros = ['resource_tecnica'=>'Nombre de técnica','resource_type_resource'=>'Nivel taxonómico básico','resource_no_tecnica'=>'Número de técnica','resource_keywords'=>'Palabras clave'];
                $couserid = $_POST['courseid'];
                $event =  \block_cien_tecnicas\event\consult_cien_tecnicas::create(array(
                'context' => context_course::instance($couserid),
                'other' => array('filtro'=>$filtros[$filtro],'busqueda'=>$busqueda,
                ),
                'userid'  => $USER->id,
            ));
            $event->trigger();
            }
            echo $dataSend;
        } else {
            printNotData();
        }
    }
}else{
    printNotData();
}
function printNotData(){
    echo '<div class="wrapper centrar-contenido">';
    echo '<p class="searchNotValue">No se han encontrado resultados para tu búsqueda.</p>';
    echo '</div>';
}
