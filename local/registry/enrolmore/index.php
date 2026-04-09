<?php
require_once(__DIR__ . '/../../../config.php');
//require_once('../globalVariables.php');
//require_once('mail/index.php');//Se incluye el archivo que enviará el correo
//if(isloggedin()){
//    redirect('/index.php');
//}

echo $OUTPUT->header();
$data = optional_param('data', '', PARAM_RAW);  // Formatted as:  username
if (!empty($data) || (!empty($p) && !empty($s))) {

    if (!empty($data)) {
        $dataelements = explode('/', $data, 2); // Stop after 1st slash. Rest is username. MDL-7647
        $username = $dataelements[0];
        $course = $dataelements[1];
    }
    $consultauser = $DB->get_record('user', array('username' => $username));
    $nombre = $consultauser->firstname;
    $email = $consultauser->email;
    $iduser = $consultauser->id;
}
$fechaLimiteRegistro = get_config('local_registry','dateregistro'); //Fecha limite de registro
$emailSupport = get_config('local_registry','mailsupport'); //correo de soporte
$fechaLimiteRegistro = strtotime($fechaLimiteRegistro);
//$fechaLimiteRegistro = strtotime($fechaLimiteRegistro);
$fechaActual = strtotime(date('d-m-Y'));
if($fechaActual > $fechaLimiteRegistro){
    $url = $CFG->wwwroot.'/index.php';
    $menssage = "La fecha de registro ha concluido, te sugerimos consultar la información para el próximo periodo.";
    redirect($url, $menssage , 5, \core\output\notification::NOTIFY_WARNING);
}
?>
<script src="../js/jquery.min.js"></script>
<script src="../js/sweetalert.min.js"></script>
<script>
    $(document).ready(function () {
        swal("Te recordamos que si has estado en una lengua en más de 2 trimestres, no tendras acceso a esa lenga y deberas escoger otra lengua que quieras tomar.",{
            closeOnClickOutside: false,
            button: "Aceptar"
        })
        $("#curp").val('')
        var mailsuport = '<?= $emailSupport?>';
        $("#curp").change(function () {
            var curpVal = $("#curp").val();
            if(curpVal != '') {
                $.post("../includes/getCoursesByCurp.php", {curp: curpVal}, function (data) {
                    var data = data.split("|")
                    $("#cursos").html(data[0]);
                    $("#iduser").val(data[1]);
                    $("#email").val(data[2]);
                    $("#typeuser").val(data[3]);

                    if ($("#cursos").val() == null) {
                        swal("Verifica que tu CURP es correcta, o que te encuentras registrado en la plataforma, si estas seguro de que es correcta, contacta a el administrador del sitio: "+mailsuport);
                        $("#cursos").html('');
                        $("#grupos").html('');
                    }
                    if(data[4] == 1){
                        swal("Solo puedes pertenecer a un curso del periodo actual").then((value)=>{
                            window.location.href =  "../../../";
                        })
                    }
                });
            }
        });
        $("#cursos").change(function(){
           var course = $("#cursos").val()
            $.post("../includes/getGroupsMoodle.php", {idcurso: course}, function (data) {
                $("#grupos").html(data);
                $("#curso").val($("#cursos").val());
            });
        });


    });
</script>
<style>
    .red-text{
        color: red;
    }
</style>
<form method="post" action="datos.php">
    <h1>Hola, actualmente te encuentras registrado en CVL, para poder registrarte en el nuevo perido o seleccionar una nueva lengua, por favor, ingresa tu CURP y posteriormente selecciona el curso y el grupo al que deseas pertenecer: </h1>
    <hr>
    <div class="form-group">
        <p>Clave Única de Registro de Población (CURP): <span class="red-text"> *</span> <br>    <input  class="form-control" name="curp" id="curp" type="text">
        </p>
    </div>
    <div class="form-group">
        <p>Curso:<span class="red-text"> *</span> <br><select title="Este campo es requerido"  required class="form-control" name="cursos" id="cursos"></select></p>
    </div>
    <div class="form-group">
    <div class="form-group">
        <p>Grupos disponibles:<span class="red-text"> *</span> <br><select title="Este campo es requerido" required class="form-control" name="grupos" id="grupos"></select></p>
    </div>
    <input name="email" id="email" type="hidden" >
    <input name="iduser" id="iduser" type="hidden">
    <input name="curso" id="curso" type="hidden">
    <input name="typeuser" id="typeuser" type="hidden">
    <input name="oldcurso" id="oldcurso" type="hidden">
    <input class="btn btn-primary" type="submit" value="Inscribirme">
    <br><br>
    <hr>

<!--    <span>Si lo prefieres, cambia tu contraseña <a href="#">aquí.</a></span>-->
</form>
