<?php
require_once(__DIR__ . '/../../../config.php');
//require_once('mail/index.php');//Se incluye el archivo que enviará el correo
//if(isloggedin()){
//    redirect('/index.php');
//}

echo $OUTPUT->header();
$data = optional_param('data', '', PARAM_RAW);  // Formatted as:  username
$idcurso = optional_param('idcurso', 0,PARAM_INT);  // Formatted as:  username
$defaulcategory = get_config("local_registry","defaultcategoryid");
$idcategoria = optional_param('idcategory', $defaulcategory,PARAM_INT);  // Formatted as:  username
$emailSuport = get_config("local_registry","mailsupport");
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

function redirecionarUsuario($url,$menssage = '')
{
    redirect($url, $menssage, 15, \core\output\notification::NOTIFY_WARNING);

}

$fechaLimiteRegistro = get_config('local_registry','dateregistro');
$fechalimitesinformato = $fechaLimiteRegistro;
$fechaLimiteRegistro = strtotime($fechaLimiteRegistro);
//$fechaLimiteRegistro = strtotime($fechaLimiteRegistro);
$fechaActual = strtotime(date('d-m-Y'));

//CUANDO EL USUARIO ESTA LOGEADO.
$url = $CFG->wwwroot.'/index.php';
if(isloggedin() && !is_siteadmin()){
    redirecionarUsuario($url);
}

//FECHA LÍMITE DEL REGISTRO
$fechaLimiteRegistro = strtotime(get_config('local_registry','dateregistro')); //Fecha limite de registro en config del pluggin
$fechaporperidos = get_config('local_registry','dateperiodos'); //Fecha de apertura y cierre por peridos
$fechaActual = strtotime(date('d-m-Y'));
$menssage = get_config('local_registry','textregistro'); //Texto pasado el límite de registro

if($fechaporperidos != '' && !is_siteadmin()){
    if(!strstr($fechaporperidos,'|')){
        $menssage = 'Configura correctamente el parametro "dateperiodos" en la configuración del plugin';
        redirecionarUsuario($url,$menssage);
    }
    $nombreperiodo = '';
    $fechainicialperiodo = '';
    $fechafinalperiodo = '';
    $novalida = 0;

    function messagePeridos($url,$fechainicial,$fechafinal,$time = 35){

        $fechaamostrar1 = $fechainicial;
        $fechaamostrar2 = $fechafinal;
        $fechaActual = date('d-m-Y');

        if($fechaActual < $fechainicial AND $fechaActual < $fechafinal){
            $text1 = 'comenzará';
            $text2 = 'finalizará';
        }elseif ($fechaActual > $fechainicial AND $fechaActual < $fechafinal){
            $text1 = 'comenzó';
        }
        elseif ($fechaActual > $fechainicial AND $fechaActual > $fechafinal){
            $text1 = 'comenzó';
            $text2 = 'finalizó';
        }


        $menssage = 'Estimado participante, el período de registro se encuentra en pausa, '.$text1 .' '.$fechaamostrar1.' y '.$text2.' el '.$fechaamostrar2.', revisa la página principal para obtener más información acerca del próximo período.';
        redirecionarUsuario($url,$menssage);

    }

    if(strstr($fechaporperidos,',')){
        //Tiene mas de un periodo
        $datavalidate = explode(',',$fechaporperidos);
        foreach ($datavalidate as $dataperiodo){
            $datalimpia = trim($dataperiodo);
            $datafecha = explode('|',$datalimpia);
//            print_object($datafecha);
            if(sizeof($datafecha)>3){
                $menssage = 'Configura correctamente el parametro "dateperiodos" en la configuración del plugin';
                redirecionarUsuario($url,$menssage);

            }
            $nombreperiodo =  trim($datafecha[0]);
            $fechainicialperiodo = strtotime(trim($datafecha[1]));
            $fechafinalperiodo = strtotime(trim($datafecha[2]));
            if($fechainicialperiodo == '' OR $fechafinalperiodo == '' OR $nombreperiodo == ''){
                $menssage = 'Configura correctamente el parametro "dateperiodos" en la configuración del plugin';
                redirecionarUsuario($url,$menssage);

            }

            //solo valida si la fecha actual es mayor
            if($fechaActual >= $fechainicialperiodo){
//                echo 'fecha actual es mayor';
                if($fechafinalperiodo <= $fechaActual){
                    $novalida++;
                    //Omite la valdiación
                }
//                else if($fechaActual >= $fechainicialperiodo AND $fechaActual <= $fechafinalperiodo ){
//                    break;
//                }
                else if (!($fechaActual >= $fechainicialperiodo AND $fechaActual <= $fechafinalperiodo) ) {
                    messagePeridos($url,trim($datafecha[1]),trim($datafecha[2]));
                }else{
                    break;
                }

            }else{
                if($fechafinalperiodo > $fechaActual){
                    messagePeridos($url,trim($datafecha[1]),trim($datafecha[2]));
                }
                $novalida++;
            }
        }
        if($novalida == sizeof($datavalidate)){
            messagePeridos($url,trim($datafecha[1]),trim($datafecha[2]));
        }
    }else{
        //solo tiene un periodo configurado
        $datafecha = explode('|',$fechaporperidos);
        if(sizeof($datafecha)>3){
            $menssage = 'Configura correctamente el parametro "dateperiodos" en la configuración del plugin';
            redirecionarUsuario($url,$menssage);

        }
        $nombreperiodo =  trim($datafecha[0]);
        $fechainicialperiodo = strtotime(trim($datafecha[1]));
        $fechafinalperiodo = strtotime(trim($datafecha[2]));
        if($fechainicialperiodo == '' OR $fechafinalperiodo == '' OR $nombreperiodo == ''){
            $menssage = 'Configura correctamente el parametro "dateperiodos" en la configuración del plugin';
            redirecionarUsuario($url,$menssage);

        }
        if (!($fechaActual >= $fechainicialperiodo AND $fechaActual <= $fechafinalperiodo) ) {
            messagePeridos($url,trim($datafecha[1]),trim($datafecha[2]));
        }
    }
}else {
    if(is_siteadmin()){}
    if ($fechaLimiteRegistro < $fechaActual && !is_siteadmin()) {
        $url = $CFG->wwwroot . '/index.php';
//    $url = $CFG->wwwroot.'/login/index.php';
        redirecionarUsuario($url,$menssage);

    }
}


//valida que el id de curso exista
if($idcurso != 0) {
    $existecurso = $DB->get_record('course', array('id' => $idcurso));
    if (!$existecurso) {
        print_error('El id de curso ingresado no existe.');
    }
}

?>
<script src="../js/jquery.min.js"></script>
<script src="../js/sweetalert.min.js"></script>
<script>
    $(document).ready(function () {
        $("#curp").val('')


        var idcurso = "<?= $idcurso; ?>";
        var iduser = '';


        $("#curp").change(function () {
            $("#cursos").html('');
            var curpVal = $("#curp").val().trim();
            $("#curp").val(curpVal.trim())
            if(curpVal.length == 18){
            $.post("../includes/getUserByCurp.php", {curp: curpVal}, function (data) {
                var data = data.split("|");
                iduser = data[1];
                // alert(iduser);

                if(iduser != '') {
                    $("#iduser").val(data[1]);
                    $("#email").val(data[2]);
                    $("#typeuser").val(data[3]);
                    showCurses(idcurso);
                    if(idcurso != '' && idcurso != 0){
                        validateconditions(idcurso,iduser)
                    }
                }else{
                    validateuser()
                }
            });
            }
        });

        $("#cursos").change(function () {
            idcurso = $("#cursos").val();
            if(idcurso != 0) {
                validateconditions(idcurso, iduser)
            }
        });

        function validateconditions(idcurso,iduser){
            $.post("../includes/validatecustomcertuser.php", { courseid: idcurso, iduser: iduser}, function (data) {
                if (data != 0) {
                    //vERIFICA RL CURSO AL CUAL SE VA A INCRIBIR
                } else {
                    $("#cursos").html('');
                    swal("Estimada(o) participante para poder inscribirte en este curso, es necesario que primero hayas concluido un curso y descargado la constancia de participación correspondiente.").then((value) => {
                        // window.location.href = "../../../";
                    })
                }
            });
            $("#curso").val($("#cursos").val());
        }

        function showCurses(idcurso = 0){
            idcategoria = "<?= $idcategoria; ?>";
            idrol = $("#typeuser").val();
            $.post("../includes/CategoriasMoodle.php", {idcategoria: idcategoria,
                idrol: idrol}, function (data) {
                if (data == '') {
                    swal("El curso al que intentas inscribirte no se encuentra disponible.")
                }
                else if(data == 'not_config_courses'){
                    notconfigcousese = "<option value=''>Actualmente no existen cursos disponibles para inscribirse.</option>";
                    $("#cursos").html(notconfigcousese);
                }
                else {
                    $("#cursos").html(data);
                    if(idcurso != 0) {
                        //selecciona el curso
                        $("#curso").val(idcurso);
                        $("#cursos").val(idcurso);
                    }
                }
                // validateuser();
            });

        }
        function validateuser() {
            var emailSuport = "https://mesadeservicio.unadmexico.mx/"

            if ($("#cursos").val() == null) {
                swal("La CURP proporcionada no se encuentra registrada, verifica que tu CURP sea correcta.", {
                    button: "Aceptar"
                });
                $("#cursos").html('');
                $("#curp").val('')
            }
        }

    });
</script>
<style>
    .red-text{
        color: red;
    }
    .control-data-form {
        pointer-events: none;
    }
</style>
<form id="form" method="post" action="datos.php">
    <h1>Estimada(o) participante, si ya realizaste tu registro y deseas incribirte en el nuevo periodo, por favor <b>ingresa tu CURP</b> y posteriormente confirma tu inscripción.
        <br>Si aún no te has registrado, da <a style="color: #7a6a59" href="../index.php">clic aquí</a>
        : </h1>
    <hr>
    <div class="form-group">
        <p>Clave Única de Registro de Población (CURP): <span class="red-text"> *</span> <br>    <input class="form-control" name="curp" id="curp" type="text">
        </p>
    </div>
    <div class="form-group">
        <p>Curso a inscribir:<span class="red-text"> *</span> <br><select required class="form-control" name="cursos" id="cursos"></select></p>
    </div>
    <input name="email" id="email" type="hidden" >
    <input name="iduser" id="iduser" type="hidden">
    <input name="curso" id="curso" type="hidden">
    <input name="typeuser" id="typeuser" type="hidden">
<!--    <input name="oldcurso" id="oldcurso" type="hidden">-->
    <input id="btnsubmit"  class="btn btn-primary" type="button" value="Inscribirme">
    <a href="../../../index.php" class="btn btn-secondary" >Cancelar</a>
    <br><br>
    <hr>

    <!--    <span>Si lo prefieres, cambia tu contraseña <a href="#">aquí.</a></span>-->
</form>
<script>
    const element = document.querySelector('#btnsubmit');
    element.addEventListener('click', event => {
        if(document.querySelector('#cursos').firstChild != null & document.querySelector('#cursos').value > 0) {

            if(document.querySelector('#cursos').value == 0 | document.querySelector('#cursos').value == ''){
                swal("No existen cursos disponibles por el momento.")
            }else {

                cursoname = document.querySelector('#cursos').firstChild.textContent
                var inicial = document.createElement("span");
                inicial.innerHTML = '¿Esta seguro de incribirte al curso ' + cursoname + '?';
                swal({
                    content: {
                        element: inicial,
                    },
                    title: "Confirmación de incripción:",
                    buttons: {
                        cancel: "Si",
                        catch: {
                            text: "No",
                            value: "cancel",
                        },

                    },

                })
                    .then((value) => {
                        switch (value) {
                            case "cancel":
                                break;
                            default:
                                document.querySelector('#form').submit();
                        }
                    });
            }
        }else{
            swal("El curso al que intentas inscribirte no se encuentra disponible.")
        }
    });
</script>
<?php
echo $OUTPUT->footer();