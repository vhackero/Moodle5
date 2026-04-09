<?php
//echo $OUTPUT->header();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Registro - retos ciudadanos</title>
    <link rel="icon" type="image/x-icon" href="/registro/images/favicon.ico">
</head>
<body>

<style>
    .red-text{
        color: red;
    }
</style>
<br>
<div class="container">
    <div  id="dos_form"  class="row">
        <div class="col-md-6 offset-md-3 card" id="medio" >

            <form id="controler-curp" action="upload.php" method="post" enctype="multipart/form-data">
                <div class="panel-heading">
                    <h3 id="texto-a-mostrar" >Para continuar por favor, carga la imagen  aquí: <a target="_blank" href="https://www.gob.mx/curp/">Consultar CURP.</a></h3>
                </div>
                <hr>
                <div id="muestra-curp" style=" padding: 0px 5% 10px;">
                    <label style="font-size: x-large; ">Para realizarlo sigue los siguientes pasos:</label>
                    <ol style="padding: 0px 15% 10px;">
                        <li>Verifica que archivo a cargar tenga el nombre de la categoría que no muestra la imagen.</li>
                        <li>Ejemplo:<br>
                            <span>nombre de categoría : Ciencias sociales</span><br>
                            <span>nombre de la imagen a cargar : Ciencias sociales.jpg</span><br>
                        </li>
                        <li>NOTA:<br><b>LA IMAGEN DEBE ESTAR EN FORMATO .jpg</b>
                        </li>
                    </ol>
                </div>
                <label for="formFileMultiple" class="form-label">Multiple files input example</label>
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input name="submit" type="submit" value="Enviar">




            </form>


        </div>

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
