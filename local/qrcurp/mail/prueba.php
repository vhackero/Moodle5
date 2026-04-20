<?php

require_once(__DIR__.'/../../../config.php');

require_once($CFG->libdir.'/moodlelib.php');

require_once('index.php');

$sendMail = enviaCorreo(14455,5);
$data = get_string("noregismoodle","local_qrcurp");
echo $data;
if(!$sendMail){
    echo "Error al mandar correo";
}else{
    echo "Correo enviado";
}
?>
