<?php
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("location: index.php");
    die();
}

require_once(__DIR__.'/../../config.php');
global $CFG;
require_once($CFG->dirroot.'/group/lib.php');
require_once('mail/index.php');//Se incluye el archivo que enviará el correo

global $DB, $data, $PAGE;

$PAGE->set_context(\context_system::instance());

// ✅ CORREGIDO: OBTENER EL ORIGEN DEL REGISTRO
$origin = optional_param('origin', 'default', PARAM_TEXT);
$is_saberes_mx = optional_param('is_saberes_mx', 0, PARAM_INT);

// Si no viene por POST, intentar obtener de la sesión
if ($origin === 'default') {
    global $SESSION;
    if (isset($SESSION->registration_origin)) {
        $origin = $SESSION->registration_origin;
    }
}

// Validar origen
$allowed_origins = ['default', 'saberes_mx'];
if (!in_array($origin, $allowed_origins)) {
    $origin = 'default';
}

// ✅ NUEVO: FUNCIÓN PARA GUARDAR EL ORIGEN EN EL CAMPO DE PERFIL
function save_registration_origin_to_profile($userid, $origin) {
    global $DB;

    // Obtener el ID del campo de perfil personalizado
    $field = $DB->get_record('user_info_field', array('shortname' => 'registro'));

    if (!$field) {
        error_log("Campo de perfil 'registro' no encontrado");
        return false;
    }

    // Verificar si ya existe un registro para este usuario
    $existing = $DB->get_record('user_info_data', array(
        'userid' => $userid,
        'fieldid' => $field->id
    ));

    if ($existing) {
        // Actualizar registro existente
        $existing->data = $origin;
        return $DB->update_record('user_info_data', $existing);
    } else {
        // Crear nuevo registro
        $profile_data = new stdClass();
        $profile_data->userid = $userid;
        $profile_data->fieldid = $field->id;
        $profile_data->data = $origin;
        $profile_data->dataformat = 0;

        return $DB->insert_record('user_info_data', $profile_data);
    }
}

/**
 * Guarda campos extra dinámicos en user_info_data cuando existe su shortname.
 *
 * @param int $userid
 * @param array $extrafields
 * @return void
 */
function save_dynamic_profile_fields($userid, array $extrafields): void {
    global $DB;

    foreach ($extrafields as $shortname => $value) {
        if ($value === null) {
            continue;
        }
        if (is_string($value) && trim($value) === '') {
            continue;
        }
        $shortname = clean_param($shortname, PARAM_ALPHANUMEXT);
        if ($shortname === '') {
            continue;
        }
        $field = $DB->get_record('user_info_field', ['shortname' => $shortname]);
        if (!$field) {
            continue;
        }
        $existing = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $field->id]);
        if ($existing) {
            $existing->data = (string) $value;
            $existing->dataformat = 0;
            $DB->update_record('user_info_data', $existing);
        } else {
            $DB->insert_record('user_info_data', [
                'userid' => $userid,
                'fieldid' => $field->id,
                'data' => (string) $value,
                'dataformat' => 0,
            ]);
        }
    }
}

/**
 * Normaliza el código de país para cumplir el formato de la columna user.country.
 *
 * @param string $countrycode
 * @return string
 */
function local_qrcurp_normalize_country(string $countrycode): string {
    $countrycode = strtoupper(trim($countrycode));
    $countrycode = clean_param($countrycode, PARAM_ALPHANUMEXT);
    if (strlen($countrycode) !== 2) {
        return 'MX';
    }

    $countries = get_string_manager()->get_list_of_countries(true);
    if (!array_key_exists($countrycode, $countries)) {
        return 'MX';
    }

    return $countrycode;
}

/**
 * Guarda campos de perfil estándar y extra, omitiendo valores vacíos.
 *
 * @param int $userid
 * @param array $fieldmap shortname => value
 * @return void
 */
function save_profile_fields_from_map($userid, array $fieldmap): void {
    save_dynamic_profile_fields($userid, $fieldmap);
}

define('GROUPENTIDAD',10001);
define('GROUPMUNICIPIO',10002);
define('GROUPENTIDADMUNICIPIO',10003);
define('GROUPROL',10004);
define('GROUPOCUPACION',10005);
define('GROUPOA',10006);
define('GROUPOB',10007);
define('GROUPOC',10008);
define('GROUPOD',10009);
define('GROUPOE',100010);
define('GROUPOF',100011);
define('DEFAULTNAMEGROUP','GRUPO');

// ... (el resto del código permanece igual) ...

$rolestudent = get_config('local_qrcurp','rolstudent');        //rol de estudiantes en los cursos
$limitedegrupo = get_config('local_qrcurp','limitegroup');    //límite de alumnos en los grupos
$supportEmail = get_config('local_qrcurp','mailsupport');    //Correo de soporte

$curp = $_POST['curp'] ?? '';
$correo = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$alias = $_POST['session_alias'] ?? '';
($alias == '') ? $alias = ($_POST['pass'] ?? '') : $alias = $alias;
$nombres = $_POST['nombre'] ?? '';
$apellidop = $_POST['p_apellido'] ?? '';
$apellidos = $_POST['s_apellido'] ?? '';
$genero = $_POST['genero'] ?? '';
$fechanaci = $_POST['date_nacimientos'] ?? '';($fechanaci== "")?$fechanaci = "00-00-0000" : $fechanaci = $_POST['date_nacimientos'];//Todo agregar a configuraciones del pluggin
$estado = $_POST['e_nacimiento'] ?? '';($estado == "")?$estado = "N/A" : $estado = $_POST['e_nacimiento'];
$municipio = $_POST['municipios'] ?? '';($municipio == "")?$municipio = "N/A" : $municipio = $_POST['municipios'];
$ocupacion = $_POST['ocupacion'] ?? ''; ($ocupacion == "")? $ocupacion = "N/A": $ocupacion = $_POST['ocupacion'];
$pais = $_POST['id_country'] ?? 'MX';
$pais = local_qrcurp_normalize_country($pais);
$cp = $_POST['codigo-postal'] ?? ''; ($cp == "")?$cp = "N/A" : $cp = $_POST['codigo-postal'];
$edad = $_POST['edad'] ?? '';
$matricula = $_POST['matricula'] ?? ''; ($matricula == "")?$matricula = "N/A" : $matricula = $_POST['matricula'];
$estado_residen = $_POST['e_residencias'] ?? ''; ($estado_residen == "")?$estado_residen= "N/A" : $estado_residen = $_POST['e_residencias'];
$rol = $_POST['rol'] ?? '';
$rolname = $_POST['rolname'] ?? '';
$idcourse = $_POST['idcourse'] ?? '';
$idcreategroup = $_POST['typegrouping'] ?? '';
$namecategory = $_POST['namecategory'] ?? '';
$typeuser = $_POST['typeuser'] ?? '';
$extrafields = optional_param_array('extra_fields', [], PARAM_RAW_TRIMMED);
if (empty($extrafields) && isset($_POST['extra_fields']) && is_array($_POST['extra_fields'])) {
    $extrafields = clean_param_array($_POST['extra_fields'], PARAM_RAW_TRIMMED, true);
}

//Extra data formulario de registro
$curpvalida = $_POST['curpvalida'];

//comprobacion de confirmación
if($typeuser == 1){
    //usuario bdexterna
    //verifica si se envia confirmación
    $confirmexternos = get_config('local_qrcurp','confirmemailexternos');
    ($confirmexternos == 1)?$confirmemail = 1 : $confirmemail = get_config('local_qrcurp','confirmemail');
}else{
    //usuario publico general
    $confirmpublicogeneral = get_config('local_qrcurp','confirmemailgeneral');
    ($confirmpublicogeneral == 1)?$confirmemail = 1 : $confirmemail = get_config('local_qrcurp','confirmemail');
}

//COMPRUEBA NUEVAMENTE EL NÚMERO DE USUARIOS EN UN GRUPO
$url = $CFG->wwwroot.'/login/index.php';
if($idcreategroup == '' OR $idcourse == '') {
    print_error("El id de curso o grupo se encuentra vacío, debes seleccionar el curso y grupo al que deseas inscribirte.");
}else if(1){
    if($idcreategroup != '10001') {
        $eslistaEspera = $DB->get_record("groups", array('id' => $idcreategroup));
        $nombredelGrupo = $eslistaEspera->name;
        $nohaylimite = 0;
        if (get_config("local_qrcurp", "haygroupespera") == 1) {
            $nameListaEspera = get_config("local_qrcurp", "namegroupespera");
            if (strstr("$nombredelGrupo", "$nameListaEspera")) {
                $nohaylimite = 1;
            }
            if (strstr("$nombredelGrupo", "cultura")) {
                $limitedegrupo = 40; //cup para practica de la cultura
            }
        }
        if ($nohaylimite == 0) {
            $totalUserGroup = (count(groups_get_members($idcreategroup, 'u.*')));
            if ($totalUserGroup >= $limitedegrupo + 1) {
                //límite superado de usuarios para el grupo
                redirect($url, "Lo sentimos, el grupo al que intentas registrarte ha superado el límite permitido.", null, \core\output\notification::NOTIFY_INFO);
            }
        }
    }
}

$pass = md5($alias);
$nombres = strtoupper($nombres);
$apellidop = strtoupper($apellidop);
$apellidos = strtoupper($apellidos);
$apellidos = array($apellidop, $apellidos);
$apellidos = join(' ',$apellidos);

$record =  new stdClass();
$record->username =  strtolower($username);
$record->password = $pass;
$record->idnumber = $alias;
$record->firstname = $nombres;
$record->lastname = $apellidos;
$record->email = $correo;
$record->city = $municipio;
$record->country = $pais;
$record->confirmed = "1";
$record->mnethostid = "1";
$record->emailstop = "0";
$record->lang = "es_utf8";
$record->picture = "0";
$record->maildisplay = "2";
$record->autosubscribe = 0;

//Comprueba si el usuario esta registrado
$dataname = '';
$dataemail = '';

$datos = $DB->get_record('user',array('username' => $username));

if(isset($datos) && is_object($datos)) {
    $dataname = $datos->username;
    $userid = $datos->id;
}

$datos = $DB->get_record('user',array('email' => $correo));

if(isset($datos) && is_object($datos)) {
    $dataemail = $datos->email;
    if ($userid == '') {
        $userid = $datos->id;
    }
}

if($dataemail != '' || $dataname != '') {
    $destination = "$CFG->wwwroot/login/index.php";
    $enviacorreo = 0; // ya está registrado
    $seenviaCorreo = enviaCorreo($userid,$enviacorreo);//PARA ENVIAR EL CORREO CON EL USUARIO Y CONTRASEÑA
    $message = "El usuario con el que estás intentando inscribirte ya está registrado.\n
    Se ha enviado un mensaje a la dirección de correo electrónico asociado a la cuenta, sigue las instrucciones para iniciar sesión.";
    redirect($destination, $message, null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    //VERIFICA SI SE ENVIARÁ CORREO DE CONFIRMACIÓN
    if($confirmemail == 1){
        $record->confirmed = "0";
        $record->secret = random_string(15);
        $record->institution = $idcourse; //ID DEL COURSE EN EL QUE SE AGREGARA UNA VEZ CONFIRMADO
        $record->department = $idcreategroup; //ID DEL GROUP EN CASO DE QUE SE ENCUENTRE
        if($namecategory != ''){
            $record->address = $namecategory; //Guarda el nombre de la categoría desde la que se está registrando
        }

        //Solo se registra el usuario y se guardan los datos
        $datosinsert = $DB->insert_record('user', $record);
        //Se enviará correo con la verificación
        $verificaiduser = $DB->get_record('user', array('username' => $username));
        $iduserinsert = $verificaiduser->id;

        // ✅ NUEVO: GUARDAR EL ORIGEN DEL REGISTRO (CONFIRMACIÓN REQUERIDA)
        save_registration_origin_to_profile($iduserinsert, $origin);

        $idnameCategoria = $DB->get_record('course', array('id' => $idcourse));
        $idCategoria = $idnameCategoria->category;
        $idNameCategoria = $DB->get_record('course_categories', array('id' => $idCategoria));
        $nameCategoria = $idNameCategoria->name;
        $destination = "$CFG->wwwroot/login/index.php";

        // Agrega los datos del formulario al perfil de forma dinámica y omitiendo vacíos.
        $profilefieldmap = [
            'ocupacion' => $ocupacion,
            'cp' => $cp,
            'estado_residencia' => $estado_residen,
            'estado_nacimiento' => $estado,
            'fecha_nacimiento' => $fechanaci,
            'curp' => $curp,
            'genero' => $genero,
            'edad' => $edad,
            'matricula' => $matricula,
            'rol' => $rol,
            'rol_name' => $rolname,
        ];
        save_profile_fields_from_map($iduserinsert, $profilefieldmap);
        save_profile_fields_from_map($iduserinsert, $extrafields);

        //Datos extras a importar 19-08-2024
        $insertafiel = $DB->get_record('user_info_field', array('shortname' => 'curpvalida'));
        if($insertafiel){
            $recordinfodata = $DB->insert_record('user_info_data', array('userid' => $iduserinsert, 'fieldid' => $insertafiel->id, 'data' => $curpvalida, 'dataformat' => 1));
        }else{
            print_error("El campo de perfil del usuario 'curpvalida' no se encuentra en la base de datos");
        }

        $seenviaCorreo = enviacorreo($iduserinsert,4,$alias,$idcourse,$idcreategroup,$nameCategoria);
        if($seenviaCorreo){
            $message = "¡Registro completado con éxito! Se ha enviado un mensaje a tu dirección de correo electrónico $correo para confirmar tu cuenta.";
            redirect($destination, $message, null, \core\output\notification::NOTIFY_SUCCESS);
        }else{
            $message = "¡Registro completado! Tenemos problemas para enviar el correo. Favor de contactar al administrador del sitio: $supportEmail";
            redirect($destination, $message, null, \core\output\notification::NOTIFY_INFO);
        }
    }
    else {
        //Continua con el registro sin enviar correo de confirmación
        //REGISTRA A EL NUEVO USUARIO
        $datosinsert = $DB->insert_record('user', $record);
        if ($datosinsert) {
            //EL USUARIO SE REGISTRO CON ÉXITO
            $verificaiduser = $DB->get_record('user', array('username' => $username));
            $iduserinsert = $verificaiduser->id;

            // ✅ NUEVO: GUARDAR EL ORIGEN DEL REGISTRO (SIN CONFIRMACIÓN)
            save_registration_origin_to_profile($iduserinsert, $origin);
            $profilefieldmap = [
                'ocupacion' => $ocupacion,
                'cp' => $cp,
                'estado_residencia' => $estado_residen,
                'estado_nacimiento' => $estado,
                'fecha_nacimiento' => $fechanaci,
                'curp' => $curp,
                'genero' => $genero,
                'edad' => $edad,
                'matricula' => $matricula,
                'rol' => $rol,
                'rol_name' => $rolname,
            ];
            save_profile_fields_from_map($iduserinsert, $profilefieldmap);
            save_profile_fields_from_map($iduserinsert, $extrafields);

            if ($idcourse != '') {
                // ... (resto del código existente de matriculación) ...

                // Al final del proceso exitoso:
                $destination = "$CFG->wwwroot/course/view.php?id=$idcourse";
                $message = "¡Registro completado con éxito!.
Se ha enviado un mensaje a tu dirección de correo electrónico con los detalles de tu registro y las instrucciones para iniciar sesión.";
                redirect($destination, $message, null, \core\output\notification::NOTIFY_SUCCESS);
            }
            else {
                //SOLO se registra sin guardar los datos
                $destination = "$CFG->wwwroot/login/index.php";
                if($seenviaCorreo){
                    $message = "¡Registro completado! Hemos enviado un correo electrónico a la dirección de correo $correo, sigue las instrucciones e inicia sesión.";
                    redirect($destination, $message, null, \core\output\notification::NOTIFY_SUCCESS);
                }else{
                    $message = "¡Registro completado! Tenemos problemas para enviar el correo. Favor de contactar al administrador del sitio: $supportEmail";
                    redirect($destination, $message, null, \core\output\notification::NOTIFY_INFO);
                }
            }
        } else {
            print_error("No se agregó el nuevo usuario, verificar los datos a insertar.");
        }
    }
}

//envia correo
//0 ya esta registrado en moodle $userid
//1 Solo se registro en moodle
//2 Solo se registro y añadio a un curso
//3 se registro y añadio a un curso y un grupo
if($enviacorreo == 0){
    $user = get_complete_user_data('username', $userid);
}
?>
