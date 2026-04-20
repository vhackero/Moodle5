<?php
function createGraph($datasql)
{
// Incluye la librería JpGraph
    require_once('../jpgraph/src/jpgraph.php');
    require_once('../jpgraph/src/jpgraph_bar.php');
    require_once(__DIR__.'/../../../../config.php');
    global $CFG;
////PRUEBAS
//$consulta = 'SELECT  groups.name as "Nombre del grupo",course.fullname as "Nombre del curso",COUNT(u.id) as "No. de usuarios"
//        FROM mdl_role_assignments AS asg
//        JOIN mdl_context AS context ON asg.contextid = context.id AND context.contextlevel = 50
//        JOIN mdl_user AS u ON u.id = asg.userid
//        JOIN mdl_course AS course ON context.instanceid = course.id
//        JOIN mdl_course_categories as category ON category.id = course.category
//        JOIN mdl_groups as groups ON groups.courseid = course.id
//        JOIN mdl_groups_members as groups_m On groups_m.groupid = groups.id AND groups_m.userid=u.id
//        WHERE asg.roleid = 5 AND u.deleted = 0 AND category.id = 1
//        group by groups.id';
//$datasql = $DB->get_records_sql($consulta);
//$nombreCompleto = $USER->firstname . " " . $USER->lastname;
$items = [];
//$fechaExtraccion = date('d-m-Y H:m');
//$filename = 'Reporte_' . $nameReport . '_' . date('d_m_Y');
//Obtenemos el nombre de los header
foreach ($datasql as $lista){
    $items = $lista;
    break;
}
$data = get_object_vars($items);
$ArrayHeaders = (array_keys($data));
//Obtenemos la data a graficar
$i=0;
$datos = array();
$headers = array();
foreach ($datasql as $item) {
    //acedemos a el primero dato encontrado
    foreach ($item as $dataitem){
        $i++;
        if(sizeof($ArrayHeaders) == $i){
        // Define los datos del gráfico
            array_push($datos,$dataitem);
            $i=0;
        }
        if($i==1){
            //Headers
            array_push($headers,$dataitem);

        }
    }
}
// Crea una instancia del objeto Graph
    $graph = new Graph(500, 300);
//Titulo de la gráfica
    $graph->title->Set("Resumen");
// Configura algunos aspectos del gráfico
    $graph->SetScale("textlin");
    $graph->SetShadow();


// Crea una instancia del objeto BarPlot
    $barplot = new BarPlot($datos);

// Agrega el BarPlot al gráfico
    $graph->Add($barplot);

// Configura algunos aspectos del BarPlot
    $barplot->SetColor("white");
    $barplot->SetFillColor("#47bbdd");

// Configura algunos aspectos del eje X
    $graph->xaxis->SetTickLabels($headers); //labels
    $graph->xaxis->SetFont(FF_DV_SANSSERIF, FS_BOLD);

// Configura algunos aspectos del eje Y
    $graph->yaxis->SetFont(FF_DV_SANSSERIF, FS_BOLD);

// Dibuja el gráfico
    $graph->Stroke();

//Guarda la gráfica
    $graph->img->Stream("$CFG->dirroot/local/qrcurp/reportes/img/grafico.png");
}
?>
