<?php
function createGraph($datasql,$nameReport,$download){
    $items = '';
    $datos = [];
    $ArrayHeadersNew = [];
    foreach ($datasql as $lista){
        $items = $lista;
        break;
    }
    $data = get_object_vars($items);
    $ArrayHeaders = (array_keys($data));
    $label = $ArrayHeaders[0];
//    print_object($ArrayHeaders);
//    print_object($datasql);
//    $ArrayHeadersNew = (array_keys($datasql));
//    print_object($ArrayHeadersNew);

    foreach ($datasql as $c){
//        echo $c->{$label};
        array_push($ArrayHeadersNew,$c->{$label});
        foreach ($c as $i) {

            if(is_numeric($i)){
                if($i > 0 ){
                    array_push($datos,$i);
                }else{
                    array_push($datos,0);
                }
            }

        }
    }
//    print_object($datac);

//    print_object($datos);
    $etiquetaVertical = $ArrayHeadersNew; //para loa labels verticales
    $datosVentas = $datos;

    if($download == 0){
        '<script>
        document.getElementById("grafica").style.display = "block";
        </script>';
    }
    ?>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reporte</title>
        <!-- Importar chart.js -->
        <script src="js/Chart.min.js"></script>
        <script src="../js/jquery.min.js"></script>
    </head>
    <input type='hidden' name='imagen' id='imagen' />
    <div style="width: 500px !important;">
        <canvas style="display: none"  id="grafica"></canvas>
    </div>
    <script type="text/javascript">
        // Obtener una referencia al elemento canvas del DOM
        const $grafica = document.querySelector("#grafica");
        // Pasaamos las etiquetas desde PHP
        const etiquetas = <?php echo json_encode($etiquetaVertical) ?>;

        function getRandomInt(max) {
            return Math.floor(Math.random() * max);
        }
        $colorRandom = getRandomInt(150);
        $colorRando2 = getRandomInt(220);
        $colorRando3 = getRandomInt(90);
        // Podemos tener varios conjuntos de datos. Comencemos con uno
        const datosVentas2020 = {
            label: "<?=$nameReport ?>",
            // Pasar los datos igualmente desde PHP
            data: <?php echo json_encode($datosVentas) ?>,
            backgroundColor: 'rgba('+$colorRando3+','+$colorRandom+', 235, 0.2)', // Color de fondo
            borderColor: 'rgba('+$colorRando2+', 162,'+$colorRando3+', 1)', // Color del borde
            // backgroundColor: 'rgba(54, 162, 235, 0.2)', // Color de fondo
            // borderColor: 'rgba(54, 162, 235, 1)', // Color del borde
            borderWidth: 1, // Ancho del borde
        };
        new Chart($grafica, {
            type: 'bar', // Tipo de gráfica
            data: {
                labels: etiquetas,
                datasets: [
                    datosVentas2020,
                    // Aquí más datos...
                ]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                },
            }

        });
        function SaveImg(){
            var imagen=document.getElementById('imagen');
            imagen.value=document.getElementById("grafica").toDataURL('image/png');
            imagen2 = $('#imagen').val();
            $.post("saveGraph.php", {imagen: imagen2}, function (data) {
                localStorage.setItem('urlgrafica',data);
            });
        }
        setTimeout(SaveImg,500); //todo falta modificar para que se ejecute hasta que cargue la data del canva.

    </script>
<?php
    return true;
}
?>

