<?php
/**
 * @package local_qrcurp
 * @author  Luis_Felipe @FelipeAlcocers
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//IMPORTACIÓN DEL CONFIG Y FUNCIÓN PARA EL ENVIO DE CORREOS
require_once(__DIR__.'/../../config.php');
require_once('globalVariables.php');

use local_qrcurp\local\config;

global $DB,$PAGE,$CFG,$NAMEPLATAFORMQRCURP,$NAMEEXTERNALDBQRCURP;

$PAGE->set_url(new moodle_url('/local/qrcurp/index.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title($NAMEPLATAFORMQRCURP);

// ✅ MEJORADO: DETECTAR PARÁMETROS UTM Y EXTRACT COURSE ID
$utm_source = optional_param('utm_source', '', PARAM_ALPHANUMEXT);
$utm_medium = optional_param('utm_medium', '', PARAM_ALPHANUMEXT);
$utm_campaign = optional_param('utm_campaign', '', PARAM_ALPHANUMEXT);
$utm_content = optional_param('utm_content', '', PARAM_ALPHANUMEXT);
$utm_term = optional_param('utm_term', '', PARAM_ALPHANUMEXT);

// ✅ NUEVO: EXTRAER ID DEL CURSO DIRECTAMENTE DESDE UTM_CONTENT
$saberes_course_id = '';
$saberes_course_name = '';
$saberes_user_id = '';

// DETECTAR SI ES DE SABERES MX
$is_from_saberes_mx = false;
if (!empty($utm_source) && strpos(strtolower($utm_source), 'saberesmx') !== false) {
    $is_from_saberes_mx = true;

    // Extraer ID del curso directamente desde utm_content
    if (!empty($utm_content)) {
        $saberes_course_id = $utm_content;

        // ✅ NUEVO: VALIDAR QUE EL utm_content ES UN ID VÁLIDO DE CURSO
        $valid_course_ids = array('65', '66', '67', '68', '69', '70', '71', '72', '73', '74');
        if (in_array($utm_content, $valid_course_ids)) {
            // Sobrescribir el idcourse con el ID del utm_content
            $idcourse_from_utm = $utm_content;
        }
    }

    // Extraer información de campaña
    if (!empty($utm_campaign)) {
        $saberes_course_name = $utm_campaign;
    }

    // Extraer información de usuario desde utm_term
    if (!empty($utm_term)) {
        $saberes_user_id = $utm_term;
    }
}

// Determinar el origen
$origin = $is_from_saberes_mx ? 'saberes_mx' : 'default';

// Si no hay UTM, verificar parámetro legacy para compatibilidad
if ($origin === 'default') {
    $legacy_origin = optional_param('origin', 'default', PARAM_TEXT);
    if ($legacy_origin === 'saberes_mx') {
        $origin = 'saberes_mx';
        $is_from_saberes_mx = true;
    }
}

// Guardar parámetros UTM en localStorage y sesión
echo "<script>
        localStorage.setItem('nameExternalData','$NAMEEXTERNALDBQRCURP');
        localStorage.setItem('namePlataform','$NAMEPLATAFORMQRCURP');
        localStorage.setItem('registrationOrigin','$origin');
        localStorage.setItem('utm_source','$utm_source');
        localStorage.setItem('utm_medium','$utm_medium');
        localStorage.setItem('utm_campaign','$utm_campaign');
        localStorage.setItem('utm_content','$utm_content');
        localStorage.setItem('is_from_saberes_mx','$is_from_saberes_mx');
        localStorage.setItem('saberes_course_id','$saberes_course_id');
        localStorage.setItem('saberes_course_name','$saberes_course_name');
      </script>";

// Guardar en sesión PHP
global $SESSION;
$SESSION->registration_origin = $origin;
$SESSION->utm_source = $utm_source;
$SESSION->utm_medium = $utm_medium;
$SESSION->utm_campaign = $utm_campaign;
$SESSION->utm_content = $utm_content;
$SESSION->is_from_saberes_mx = $is_from_saberes_mx;
$SESSION->saberes_course_id = $saberes_course_id;
$SESSION->saberes_course_name = $saberes_course_name;

function redirecionarUsuario($url,$menssage = '')
{
    redirect($url, $menssage, 15, \core\output\notification::NOTIFY_WARNING);
}

echo $OUTPUT->header();

//CUANDO EL USUARIO ESTA LOGEADO.
$url = $CFG->wwwroot.'/index.php';
if(isloggedin()){
    redirecionarUsuario($url);
}

//FECHA LÍMITE DEL REGISTRO
$fechaLimiteRegistro = strtotime(config::get_string('dateregistro'));
$fechaporperidos = config::get_string('dateperiodos');
$fechaActual = strtotime(date('d-m-Y'));
$menssage = config::get_string('textregistro');

if($fechaporperidos != ''){
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
        $datavalidate = explode(',',$fechaporperidos);
        foreach ($datavalidate as $dataperiodo){
            $datalimpia = trim($dataperiodo);
            $datafecha = explode('|',$datalimpia);
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

            if($fechaActual >= $fechainicialperiodo){
                if($fechafinalperiodo <= $fechaActual){
                    $novalida++;
                }
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
    if ($fechaLimiteRegistro < $fechaActual) {
        $url = $CFG->wwwroot . '/index.php';
        redirecionarUsuario($url,$menssage);
    }
}

//DATOS POR DEFECTO EN LA URL SIN PARAMETROS
$defaultcategory = config::get_int('defaultcategoryid');
$defaultcourse = config::get_int('defaultcourseid');
$defaultgroup = config::get_int('defaultgroupid');

$categoryid = optional_param('categoryid', $defaultcategory, PARAM_INT);
$idcourse = optional_param('courseid', $defaultcourse, PARAM_INT);
$grouping = optional_param('grouping', $defaultgroup, PARAM_INT);

// ✅ NUEVO: SI VIENE DE SABERES MX Y TENEMOS ID DE CURSO VÁLIDO, SOBRESCRIBIR
if ($is_from_saberes_mx && !empty($idcourse_from_utm)) {
    $idcourse = $idcourse_from_utm;
}

//COMPROBACIÓN DEL ID PARA CONTINUAR CON EL REGISTRO.
if($categoryid != '') {
    $rolStudent = config::get_int('rolstudent');
    $consultaNumAlumnosXCategoria = "SELECT COUNT(*) AS total
      FROM {course} c
      JOIN {course_categories} ct ON ct.id = c.category
      JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
      JOIN {role_assignments} ra ON ra.contextid = ctx.id
      JOIN {role} rl ON rl.id = ra.roleid
      JOIN {user} u ON u.id = ra.userid
     WHERE ct.id = :categoryid AND rl.id = :roleid";
    $params = [
        'contextlevel' => CONTEXT_COURSE,
        'categoryid' => $categoryid,
        'roleid' => $rolStudent,
    ];
    $numStundentToCategory = (int) $DB->count_records_sql($consultaNumAlumnosXCategoria, $params);
    $studentlimits = config::get_category_limits('studentxcategory');
    $limitforcategory = $studentlimits['categories'][$categoryid] ?? $studentlimits['default'];

    // Si no existe configuración o el límite es <= 0, se considera sin límite.
    if (!empty($limitforcategory) && $limitforcategory > 0 && $numStundentToCategory >= $limitforcategory) {
        $url = $CFG->wwwroot.'/index.php';
        $menssage = config::get_string('studentxcategorytext');
        redirect($url, $menssage , 15, \core\output\notification::NOTIFY_WARNING);
    }

    $nameCategoria = $DB->get_record('course_categories', array('id' => $categoryid));
    $nameCategoria = $nameCategoria->name;
    echo "<script> localStorage.setItem('nameCategoria', '$nameCategoria'); </script>";
    echo "<script>document.title = 'Registro - '+'$nameCategoria';</script>";

    $name = "iconos/".$nameCategoria.".jpg";
    if(!file_exists($name)) {
        echo "<script>localStorage.setItem('nameCategoria', 'not-image');</script>";
    }
}
else{
    $nameCategoria = config::get_string('defaultnamecategory');
    $name = "iconos/".$nameCategoria.".jpg";

    if($nameCategoria != ""){
        if(file_exists($name)) {
            echo "<script> localStorage.setItem('nameCategoria', '$nameCategoria'); </script>";
            echo "<script>document.title = 'Registro - '+'$nameCategoria'; ;</script>";
        }else{
            echo "<script> localStorage.setItem('nameCategoria', 'not-image-site');</script>";
        }
    }else{
        $menssage = "Agrega un nombre del registro sin categoría válido en las configuraciones del pluggin";
        redirect($url, $menssage, 15, \core\output\notification::NOTIFY_WARNING);
    }

    if (!config::get_bool('sampleregister')) {
        $menssage = "La URL debe incluir un id de categoría para continuar con el registro. Revisar la configuración del pluggin e ingresa el id de la categoría por defecto o activar los registros sin matriculación";
        redirect($url, $menssage, 15, \core\output\notification::NOTIFY_WARNING);
    }
}

// MOSTRAR INDICADOR VISUAL MEJORADO PARA SABERES MX
if ($is_from_saberes_mx) {
    echo '<div class="alert alert-info text-center" style="margin: 10px; border-left: 5px solid #17a2b8; display: none">';
    echo '<strong>🎓 Registro desde Saberes MX</strong>';

    if (!empty($saberes_course_name)) {
        echo '<br><small>Curso referido: ' . s(ucwords(str_replace('_', ' ', $saberes_course_name))) . '</small>';
    }

    if (!empty($utm_campaign)) {
        echo '<br><small>Campaña: ' . s($utm_campaign) . '</small>';
    }

    if (!empty($saberes_course_id)) {
        echo '<br><small>ID Curso Saberes: ' . s($saberes_course_id) . '</small>';
    }

    echo '</div>';
} else if (!empty($utm_source)) {
    echo '<div class="alert alert-warning text-center" style="margin: 10px; display:none">';
    echo '<strong>🔗 Registro desde Fuente Externa</strong>';
    echo '<br><small>Fuente: ' . s($utm_source) . '</small>';

    if (!empty($utm_medium)) {
        echo ' | Medio: ' . s($utm_medium);
    }

    if (!empty($utm_campaign)) {
        echo '<br><small>Campaña: ' . s($utm_campaign) . '</small>';
    }
    echo '</div>';
} else {
    echo '<div class="alert alert-primary text-center" style="margin: 10px;display:none">';
    echo '<strong>🏠 Registro desde Portal Interno</strong>';
    echo '</div>';
}
?>
    <head>
        <script src="js/jquery/jquery.min.js"  ></script>
        <script src="js/sweetalert.min.js"></script>
        <script type="text/javascript" src="js/index.min.js"></script>
        <link rel="stylesheet" href="css/style.css?version=1.0">
    </head>
    <div class="colors">
        <div class="container">
            <br><br>
            <div style="display: none" id="dos_form" class="row">
                <div class="col-md-6 offset-md-3 card" id="medio" >
                    <div class="panel-heading">
                        <h1 id="texto-a-mostrar" >Por favor, teclea tu CURP. Si no la tienes consúltala aquí: <a target="_blank" href="https://www.gob.mx/curp/">Consultar CURP.</a></h1>
                    </div>
                    <hr>
                    <form id="controler-curp" action="decode.php" method="post" enctype="multipart/form-data">
                        <!-- Campos ocultos para UTM de Saberes MX -->
                        <input type="hidden" name="utm_source" value="<?php echo s($utm_source); ?>">
                        <input type="hidden" name="utm_medium" value="<?php echo s($utm_medium); ?>">
                        <input type="hidden" name="utm_campaign" value="<?php echo s($utm_campaign); ?>">
                        <input type="hidden" name="utm_content" value="<?php echo s($utm_content); ?>">
                        <input type="hidden" name="origin" value="<?php echo s($origin); ?>">
                        <input type="hidden" name="is_from_saberes_mx" value="<?php echo $is_from_saberes_mx ? '1' : '0'; ?>">
                        <input type="hidden" name="saberes_course_id" value="<?php echo s($saberes_course_id); ?>">
                        <input type="hidden" name="saberes_course_name" value="<?php echo s($saberes_course_name); ?>">

                        <div id="muestra-curp" style=" padding: 0px 5% 10px;">
                            <label style="font-size: x-large; ">Para usar en tu computadora o dispositivo:</label>
                            <ol style="padding: 0px 15% 10px;">
                                <li>Ten a la mano tu QR de la CURP.</li>
                                <li>Da clic sobre el icono para activar :
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-camera-video-fill" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z"/>
                                    </svg>
                                </li>
                                <li>Intercambia cámara si es necesario (móviles):
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                        <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                        <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                    </svg>
                                </li>
                                <li>Cuando se active la cámara, apunta hacia el código QR.</li>
                            </ol>
                            <span>Si tienes dudas puedes consultar el manual dando </span><a target="_blank" href="docs/guia-qrcurp.pdf"><b>clic aquí</b></a>
                        </div>
                        <div style=" padding: 0px 30% 10px;">
                            <label class="checkeable">
                                <input id="cam-principal" type="checkbox" name="cap1" />
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="currentColor" class="bi bi-camera-video-fill" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5z"/>
                                </svg>
                            </label>
                            <label id="secondary" style="display:none" class="checkeable">
                                <input id="cam-secondary" type="checkbox" name="cap2" />
                                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                    <path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/>
                                    <path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"/>
                                </svg>
                            </label>
                            <video id="video" width="200" height="200" style="border: 1px solid black"></video>
                        </div>
                        <pre><code style="display:none"id="result"></code></pre>
                        <input style="display: none" name="curp" id="curp" type="text" required>
                        <input type="hidden" name="idcourse" id="idcourse" value="<?=$idcourse?>"  >
                        <input type="hidden" name="grouping" id="grouping" value="<?=$grouping?>"  >
                        <input type="hidden" name="categoryid" id="categoryid" value="<?=$categoryid?>"  >
                    </form>
                    <div id="controler-text-curp">
                        <form id="envia-info" action="decode.php" method="post" enctype="multipart/form-data" >
                            <!-- Campos ocultos para UTM de Saberes MX -->
                            <input type="hidden" name="utm_source" value="<?php echo s($utm_source); ?>">
                            <input type="hidden" name="utm_medium" value="<?php echo s($utm_medium); ?>">
                            <input type="hidden" name="utm_campaign" value="<?php echo s($utm_campaign); ?>">
                            <input type="hidden" name="utm_content" value="<?php echo s($utm_content); ?>">
                            <input type="hidden" name="origin" value="<?php echo s($origin); ?>">
                            <input type="hidden" name="is_from_saberes_mx" value="<?php echo $is_from_saberes_mx ? '1' : '0'; ?>">
                            <input type="hidden" name="saberes_course_id" value="<?php echo s($saberes_course_id); ?>">
                            <input type="hidden" name="saberes_course_name" value="<?php echo s($saberes_course_name); ?>">

                            <div class="form-group">
                                <p>Clave Única de Registro de Población (CURP) <span class="red-text">*</span> :
                                    <input style="text-transform:uppercase" placeholder="Ingresa tu CURP" class="form-control" id="curp" name="curp" type="text" value="" oninput="validarInput(this)" required>
                                    <input type="hidden" name="idcourse" id="idcourse" value="<?=$idcourse?>"  >
                                    <input type="hidden" name="grouping" id="grouping" value="<?=$grouping?>"  >
                                    <input type="hidden" name="categoryid" id="categoryid" value="<?=$categoryid?>"  >
                                <pre id="resultado"></pre>
                                </p>
                            </div>
                            <input id="continuar" type="submit" disabled class="btn btn-md btn-block btn-info" style="background-color: #611232 !important;" value="Continuar">
                            <input type="button" class="btn btn-md btn-block btn-danger" onclick="javascript:history.back();" value="Cancelar">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="js/decode.js?version=1.0"></script>
        <script type="text/javascript" src="js/compruebacurps.js?version=1.0"></script>
        <script type="text/javascript" src="js/welcome.js?version=2.0"></script>
    </div>
<?php
echo $OUTPUT->footer();
