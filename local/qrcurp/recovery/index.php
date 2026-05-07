<?php
require_once(__DIR__ . '/../../../config.php');
//require_once('mail/index.php');//Se incluye el archivo que enviará el correo
if(isloggedin()){
    redirect('/index.php');
}

$PAGE->set_url(new \moodle_url('/local/qrcurp/recovery/index.php'));
$PAGE->set_context(\context_system::instance());

echo $OUTPUT->header();
?>
<form method="post" action="datos.php">
    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
    <h1>Por favor, teclea tu CURP para recuperar tus credenciales de acceso:</h1>
    <hr>
    <div class="form-row col-md-8 align-items-center">
        <div class="col">
            <input oninvalid="this.setCustomValidity('Este campo es requerido.')" required  type="text" class="form-control mb-2" id="email" name="email" placeholder="CURP">
        </div>
        <div class="col-5">
            <button style="width: 100%" type="submit" class="btn btn-info mb-2">Enviar</button>
        </div>
    </div>
<!--    <span>Si lo prefieres, cambia tu contraseña <a href="#">aquí.</a></span>-->
</form>
