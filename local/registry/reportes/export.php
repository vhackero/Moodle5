<?php
require_once(__DIR__ . '/../../../config.php');

function createExport($datasql,$nameReport,$download=0,$urlImagen=null,$hayGrafica=0){
    global $CFG,$OUTPUT;

//obtenemos el array de las headers para construir la tabla
    $items = [];
    $fechaExtracción = date('d-m-Y H:m');
    $filename= 'Reporte_'.$nameReport.date('d_m_Y');
?>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Reporte</title>
        <!-- Importar chart.js -->
        <script src="js/Chart.min.js"></script>
        <script src="../js/jquery.min.js"></script>
    </head>
    <?php
    function descargar($filename,$imagenTodistroy){
        global $CFG;
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-Type: text/html;charset=utf-8");
        header("Content-type: application/force-download");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $filename . "-Report.xls");
//        unlink("$CFG->dirroot/local/registry/reportes/img/$imagenTodistroy");
    }
    if($download == 1){
       descargar($filename,$urlImagen);
    }
    if($download == 1){

    foreach ($datasql as $lista){
        $items = $lista;
        break;
    }
$data = get_object_vars($items);
$ArrayHeaders = (array_keys($data));
$urlImagen = $CFG->wwwroot."/local/registry/reportes/img/".$urlImagen;
if($hayGrafica == 1)
{?>
    <div style="width:350px">Fecha de extracción:<?= $fechaExtracción ?> </div>

    <?php
        echo '<img id="grafica_img"  src="'.$urlImagen.'">';
?>
        <br><br><br><br><br><br><br>
        <br><br><br><br><br><br><br>
<?php
}
if($download == 1 && $hayGrafica ==0){
    ?>
    <h1><?=$nameReport?></h1>
    <div style="width:350px">Fecha de extracción:<?= $fechaExtracción ?> </div>

    <?php
}
    ?>
   <table border="1">
    <thead>
    <tr>
<?php
foreach ($ArrayHeaders as $i){
    array_push($etiquetaVertical,$i);
    ?>
    <th><?=$i?></th>
    <?php
}
?>
    </tr>
    </thead>
    <?php
    $conteo=0;
    foreach ($datasql as $item) {
            ?>
            <tr>
        <?php
            foreach ($item as $dataitem){
//                $conteo++;
            ?>
            <td><?php echo $dataitem;  ?></td>
            <?php
            }
    ?>
            </tr>
        <?php

    }
    ?>
    </table>
    <?php

}else{
    if($download == 0){
        $url = 'index.php';
        $menssage = "Reporte creado con éxito.";
        echo $OUTPUT->header();
        echo '
        
        <div class="progress">
            <div id="creando-reporte" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 5%" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">5%</div>
        </div>
        <br>
         <p id="texto-reporte" class="d-flex flex-row justify-content-center alig-items-center ">Creando reporte..</p>
         <div class="d-flex flex-row justify-content-center alig-items-center ">
                  <a  id="aceptar" style="display: none" href="index.php" class="btn btn-success" >Aceptar</a>
         </div>
        </div>
        <script>
            dataReporte = "'.$_POST["data"].'";
            idcursoReporte = "'.$_POST["idscursos"].'";
            function reporteCreado(){
                const actual = document.getElementById("creando-reporte").style.width;
                porcentaje = actual.split("%")
                numero = porcentaje[0];
//                console.log(numero);
                if(numero == 80){
                    document.getElementById("creando-reporte").style.width = "100%";
                    document.getElementById("creando-reporte").ariaValueNow = "100";
                    document.getElementById("creando-reporte").textContent = "100%";
                    document.getElementById("texto-reporte").textContent = "Reporte creado.";
                    document.getElementById("aceptar").style.display = "block"
                    localStorage.setItem("reporte-generado",dataReporte);
                    localStorage.setItem("idcursoReporte",idcursoReporte);
                }else{
                    numero = porcentaje[0]*2;
                   document.getElementById("creando-reporte").style.width = numero+"%";
                   document.getElementById("creando-reporte").ariaValueNow = numero;
                   document.getElementById("creando-reporte").textContent = numero+"%";
                }

            }  
            window.onload = () =>{
               setTimeout(reporteCreado,2000);
               setTimeout(reporteCreado,4000);
               setTimeout(reporteCreado,6000);
               setTimeout(reporteCreado,8000);
               setTimeout(reporteCreado,12000);
            }
        </script>
        ';
//        redirect($url, $menssage , 10, \core\output\notification::NOTIFY_SUCCESS);
    }
}


}
?>





