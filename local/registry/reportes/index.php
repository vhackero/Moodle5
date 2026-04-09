<?php
require_once(__DIR__ . '/../../../config.php');
global $USER,$CFG,$OUTPUT,$PAGE;


if (!is_siteadmin()) {
    // Si no es administrador, redirige a la página de inicio.
    redirect($CFG->wwwroot . '/');
}


echo $OUTPUT->header();

?>
<header>
    <script src="../js/jquery/jquery.min.js"></script>
</header>
<style>
    li{
        font-size: 1rem;
    }
</style>
<div id="genera-report" style="display: none" >
    <div  class="progress">
        <div id="creando-reporte" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 5%" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">5%</div>
    </div>
    <br>
    <p id="texto-reporte" class="d-flex flex-row justify-content-center alig-items-center ">Generando gráfico..</p>
    <div class="d-flex flex-row justify-content-center alig-items-center ">
        <a  id="aceptar" style="display: none" href="index.php" class="btn btn-success" >Aceptar</a>
    </div>
</div>

<section">
    <h1>Reporteador de estadísticas:</h1>
    <hr>
    <div class="form-group col-md-12 ">
        <form  method="post" action="includes/dispatch.php">
            <label for="">Rol a evaluar:</label>
            <select required  class="form-control" name="rolReportSelect" id="rolReportSelect">
            </select><br>
            <label for="">Reporte a generar:</label>
            <select required  class="form-control" name="menuReport" id="menuReport">
            </select><br>
            <input type="hidden" style="margin-bottom: 1.5rem;" class="form-control" name="data" id="data"  value="" >
            <input type="hidden"name="hay_grafica" id="hay_grafica" value="0">
            <input style="display: none" onclick="validaDAta(this.value)" id="graph_generate" class="btn btn-info" type="button" value="Generar Gráfico">
            <input  class="btn btn-success" type="submit" value="Descargar">
        </form>
    </div><br>

</section>
<script>
    function reporteCreado(){
        const actual = document.getElementById("creando-reporte").style.width;
        porcentaje = actual.split("%")
        numero = porcentaje[0];
        if(numero >= 80){
            document.getElementById("creando-reporte").style.width = "100%";
            document.getElementById("creando-reporte").ariaValueNow = "100";
            document.getElementById("creando-reporte").textContent = "100%";
            document.getElementById("texto-reporte").textContent = "Gráfico creado.";
            setTimeout(()=>{
                document.getElementById("genera-report").style.display = "none";
                document.getElementById("hay_grafica").value=1;
                localStorage.setItem("hay_grafica",1);
                localStorage.setItem("tipo_reporte",document.getElementById("menuReport").value);
                localStorage.setItem("dataExtra",document.getElementById("data").value);
                document.getElementById("creando-reporte").ariaValueNow = "5";
                document.getElementById("creando-reporte").textContent = "5%";
                document.getElementById("creando-reporte").style.width = "5%";

            },2500);

        }else{
            numero = porcentaje[0]*2;
            document.getElementById("creando-reporte").style.width = numero+"%";
            document.getElementById("creando-reporte").ariaValueNow = numero;
            document.getElementById("creando-reporte").textContent = numero+"%";
        }
    }
    function validaDAta(data){
       if(document.getElementById("rolReportSelect").value != "" &&  document.getElementById("menuReport").value != ""){
                //Genrara el reporte o descargara
           if(data == "Generar Gráfico"){
               //Genera la gráfica
               document.getElementById("genera-report").style.display = "block";
               setTimeout(reporteCreado,2000);
               setTimeout(reporteCreado,4000);
               setTimeout(reporteCreado,6000);
               setTimeout(reporteCreado,8000);
               setTimeout(reporteCreado,12000);
               datos =  document.querySelectorAll('form > .form-control')
               // console.log(datos);
               parametros = [];
               for(var i = 0; i <= datos.length-1;  i++){
                   parametros.push({name: datos[i].name, value: datos[i].value})
               }
               $.get("includes/dispatch.php", {parametros : parametros, funcion: "generateReport", submitType: data }, function (data) {
                   // alert(data);
                   if(data == 'not_data_show'){
                       alert("El reporte que intentas realizar no tiene datos que mostrar")
                   }
                   if(data == 'not_query_associated'){
                       alert("No existe una consulta asociada a el item seleccionado, revistar el archivo datos.php")
                   }
                   if(data == 'success'){
                       alert("Da click en descargar")
                   }
               });

           }
       }else{
           if(document.getElementById("menuReport").value == 0){
               //Valor por defecto estudiante
               alert("Selecciona el reporte a generar")
           }
       }
    }
    $(document).ready(function () {

        $.get("includes/getRoles.php", {}, function (data) {
            $("#rolReportSelect").html(data);
            document.getElementById("rolReportSelect").value = "5|Estudiante"
        });
        $.get("includes/menuQuerys.php", {}, function (data) {
            $("#menuReport").html(data);
            if(localStorage.getItem("hay_grafica")){
                document.getElementById("hay_grafica").value=1;
                document.getElementById("menuReport").value=localStorage.getItem("tipo_reporte");
                document.getElementById("data").value=localStorage.getItem("dataExtra");
                localStorage.removeItem('hay_grafica');
            }
        });
        $("#menuReport").change(function () {
            document.getElementById("hay_grafica").value=0;
            document.getElementById("graph_generate").style.display = "none";
            document.getElementById("data").style.display = "none";
            document.getElementById("data").value = "";
            selectItem = $("#menuReport").val();
            // alert(selectItem);
            if(selectItem == "all_partipants_in_n_courses_whithout_access_not_lista_espera" || selectItem == "all_partipants_in_n_courses_whithout_access" || selectItem == 'report_access_n_course_with_rol'){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Ingresa el id o id´s de los cursos separados por ',' ")
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9])?$");
            }if(selectItem == "report_week_access"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Ingresa el id o id´s de los cursos separados por ',' ")
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,])?$");
            }if(selectItem == "all_users_active_n_days"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Ingresa el número de días")
                document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9]+)?$");
            }if(selectItem == "all_partipants_active_one_course_n_days"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Ingresa el número de días seguido del id o id´s de los cursos entre parentesis separados por '|' ej: 5|(2,5) ")
                // document.getElementById("data").setAttribute("pattern","[0-9]+([,()][0-9])");
            }if(selectItem == "all_participants_in_actual_category_and_old"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","IdCategoriaAnterior|idcategoriaActual|rangoDeCursosDelTrimestreAnterior 'ej: 2|2|2,8' ");
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9]+)?$")
            }if(selectItem == "report_status_acreditado"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","IdCourse 'ej: 19' ");
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9]+)?$")
            }if(selectItem == "count_participants_per_groups"||selectItem == "count_participants_per_groups_types"||selectItem == "report_all_data_with_course_and_group"){
                if(selectItem == "count_participants_per_groups"||selectItem == "count_participants_per_groups_types"){
                    document.getElementById("graph_generate").style.display = "inline-block";
                }
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Id de la categoria");
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9]+)?$")
            }
            if(selectItem == "report_access_with_rol"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Fecha de incio|fecha de fin 'ej: 25-10-2024|30-10-2024' ");
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9]+)?$")
            }
            if(selectItem == "report_access_with_rol_in_course"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Fecha de incio|fecha de fin|idcurso(s), separar por coma si es más de un curso 'ej: 25-10-2024|30-10-2024|5,15' ");
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9]+)?$")
            }if(selectItem == "report_paticipation_course"){
                document.getElementById("data").setAttribute("type","text");
                document.getElementById("data").style.display = "block";
                document.getElementById("data").setAttribute("placeholder","Fecha de incio(dd-mm-YYYY)|fecha de fin(dd-mm-YYYY)|idcurso(s), separar por coma si es más de un curso 'ej: 25-10-2024|30-10-2024|5,15' ");
                // document.getElementById("data").setAttribute("pattern","^[0-9]+([,][0-9]+)?$")
            }
        });

    });

</script>
<?php
echo $OUTPUT->footer();