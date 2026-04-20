<?php
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("location: index.php");
    die();
}

require_once(__DIR__.'/../../config.php');
require_once ('remotedb.php');
require_once('mail/index.php');//Se incluye el archivo que enviará el correo
require_once('notificaciones.php');
require_once('globalVariables.php');
require_once('AvisosdePrivacidad.php');

use local_qrcurp\local\config;

/**
 * Ejecuta una consulta SQL con placeholders tipo {{param}} de forma preparada.
 *
 * @param mysqli $connection
 * @param string $template
 * @param array $values
 * @return mysqli_result|false
 */
function local_qrcurp_execute_template_query(mysqli $connection, string $template, array $values) {
    if (trim($template) === '') {
        return false;
    }

    $params = [];
    $sql = preg_replace_callback('/\{\{([a-z_]+)\}\}/', static function($matches) use ($values, &$params) {
        $key = $matches[1];
        if (!array_key_exists($key, $values)) {
            return $matches[0];
        }
        $params[] = (string) $values[$key];
        return '?';
    }, $template);

    $statement = $connection->prepare($sql);
    if (!$statement) {
        return false;
    }

    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $bindargs = [$types];
        foreach ($params as $index => $param) {
            $bindargs[] = &$params[$index];
        }
        call_user_func_array([$statement, 'bind_param'], $bindargs);
    }

    $statement->execute();

    return $statement->get_result();
}

$PAGE->set_url(new moodle_url('/local/qrcurp/decode.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Registro');

// ✅ MEJORADO: OBTENER TODOS LOS PARÁMETROS UTM
$utm_source = optional_param('utm_source', '', PARAM_ALPHANUMEXT);
$utm_medium = optional_param('utm_medium', '', PARAM_ALPHANUMEXT);
$utm_campaign = optional_param('utm_campaign', '', PARAM_ALPHANUMEXT);
$utm_content = optional_param('utm_content', '', PARAM_ALPHANUMEXT);
$utm_term = optional_param('utm_term', '', PARAM_ALPHANUMEXT);
$origin = optional_param('origin', 'default', PARAM_TEXT);
$is_from_saberes_mx = optional_param('is_from_saberes_mx', 0, PARAM_INT);
$saberes_course_id = optional_param('saberes_course_id', '', PARAM_ALPHANUMEXT);
$saberes_course_name = optional_param('saberes_course_name', '', PARAM_TEXT);
$saberes_user_id = optional_param('saberes_user_id', '', PARAM_ALPHANUMEXT);


// Validar origen
$allowed_origins = ['default', 'saberes_mx'];
if (!in_array($origin, $allowed_origins)) {
    $origin = 'default';
}

// ✅ MEJORADO: Guardar todos los parámetros UTM en la sesión
global $SESSION;
$SESSION->registration_origin = $origin;
$SESSION->utm_source = $utm_source;
$SESSION->utm_medium = $utm_medium;
$SESSION->utm_campaign = $utm_campaign;
$SESSION->utm_content = $utm_content;
$SESSION->utm_term = $utm_term;
$SESSION->is_from_saberes_mx = $is_from_saberes_mx;
$SESSION->saberes_course_id = $saberes_course_id;
$SESSION->saberes_course_name = $saberes_course_name;
$SESSION->saberes_user_id = $saberes_user_id;

// ✅ NUEVO: REGISTRAR UTM EN LOG TEMPORAL
if (!empty($utm_source)) {
    $utm_log_entry = array(
        'timestamp' => time(),
        'curp' => optional_param('curp', '', PARAM_TEXT),
        'utm_source' => $utm_source,
        'utm_medium' => $utm_medium,
        'utm_campaign' => $utm_campaign,
        'utm_content' => $utm_content,
        'utm_term' => $utm_term,
        'origin' => $origin,
        'saberes_course_id' => $saberes_course_id,
        'saberes_course_name' => $saberes_course_name,
        'saberes_user_id' => $saberes_user_id,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    );

    error_log("SABERES_MX_REGISTRO: " . json_encode($utm_log_entry));
}


$contadorVisitas = get_config("local_qrcurp","counterviews");
if($contadorVisitas){
    include_once "contadorvisitaspre-registro.php"; //comentar mientras se estén haciendo pruebas
}

//VARAIBLES GLOBALES USADAS
global $CFG,$DB,$DBEXTERNAL,$NAMEEXTERNALDBQRCURP,$NAMEPLATAFORMQRCURP;
echo $OUTPUT->header();


$url = $CFG->wwwroot.'/index.php';
if(isloggedin() AND !is_siteadmin()){
    redirect($url);
}
//PARAMETROS QUE ESTÁN EN URL.
$text = optional_param('curp', '', PARAM_TEXT);   // Course id (defaults to Site).
$idcourse = optional_param('idcourse', '', PARAM_INT);   // Course id (defaults to Site).
$typegrouping = optional_param('grouping', '', PARAM_INT);   // Group id (defaults to Site).
$gruoactive = get_config("local_qrcurp",'creategroupenrol');
$gruoidcreate = get_config("local_qrcurp",'idcreategroup');
$validaconrenapo = get_config("local_qrcurp",'validaterenapo');
$omitevalidacionrenapo = get_config("local_qrcurp",'acceptnotvalidaterenapo');

if($gruoactive == 1){
    if($typegrouping == ''){
        ($gruoidcreate == "")? notifyQrCurp(3,$CFG->wwwroot.'/local/qrcurp/') : $typegrouping=$gruoidcreate;
    }
}
$categoryid = optional_param('categoryid','', PARAM_INT);   // Category id (defaults to Site).
$registropublicogeneral = get_config('local_qrcurp','publicogeneral');   // Aceptara registros de publico externo.
$soloregistropublicogeneral = get_config('local_qrcurp','onlypublicogeneral');   //Solo aceptara registros de publico externo

//NOMBRE DE LA CATEGORÍA
$nameCategoria =  $DB->get_record('course_categories',array('id'=>$categoryid));
$nameCategoria = $nameCategoria->name;

//valida que no exista nombre de plataforma para tomar el nombre de la categoría
if($_POST['categoryid'] > 0){
    if($NAMEPLATAFORMQRCURP == ''){
        //coloca el nombre de la categoría al nombre de la plataforma
        $NAMEPLATAFORMQRCURP = $nameCategoria;
        echo "<script>localStorage.setItem('namePlataform','$NAMEPLATAFORMQRCURP');</script>";
    }
}
if($categoryid == ''){$nameCategoria = get_config('local_qrcurp','defaultnamecategory'); }

//DATOS QUE CONTIENE EL ESCANEO DE LA CURP
$campos = explode("|", $text);

if ($DBEXTERNAL->errordbportname == '') {
    $DBEXTERNAL->errordbportname =0;
}
//DATOS DE LA BD EXTERNA
$remotedbtable = $DBEXTERNAL->dbtable;          //NOMBRE DE LA BD
$existeerror =  $DBEXTERNAL->errordbportname;   //INFORMACIÓN PARA SABER SI FALTA ALGUN PARAMETRO POR CONFIGURAR
$remoteinsertdb = $DBEXTERNAL->dbinsert;        //INFORMACIÓN PARA SABER SI SE INSERTARAN LOS DATOS EN UN BD EXTERNA

//CONSULTA PRINCIPAL CON LA QUE TRABAJA EL FORMULARIO  EN LA BD EXTERNA
//$consulta ="SELECT curp FROM $remotedbtable where curp = '$campos[0]'"; //REVISAR SI LA COLUMNA ES LA CORRECTA
$consulta = config::get_string('externalcurpquery', "SELECT curp FROM tsige_persona WHERE curp = '{{curp}}'");

//VALIDACIÓN PARA VERIFICAR QUE EL USUARIO NO SE ENCUENTRA REGISTRADO

//ID DE LA TABLA DONDE SE ENCUETRA GUARDADO EL CURP
$buscaid = $DB->get_record("user_info_field",array("shortname"=>"curp"));//Validación por medio del curp
if($buscaid){
    $idvalida=$buscaid->id;
}else{
    $menssage = "Verifica que exista el campo curp en Campos de perfil de usuario, revisar el manual de instalación.";
    redirect($url, $menssage, 15, \core\output\notification::NOTIFY_WARNING);
}
$compruebausuario ="SELECT c.id as courseid, u.id as userid FROM mdl_course c
JOIN mdl_context ct ON c.id = ct.instanceid JOIN mdl_role_assignments ra ON ra.contextid = ct.id
    JOIN mdl_user u ON u.id = ra.userid JOIN mdl_role r ON r.id = ra.roleid
    JOIN mdl_user_info_data id ON id.userid = u.id WHERE id.fieldid = $idvalida AND id.data = '$campos[0]'";
$datosUser= $DB->get_records_sql($compruebausuario);
$yaestasregistrado = false;
$nombreDeUsuario = '';
//SI ENCUENTRA DATOS QUIERE DECIR QUE EL USUARIO ESTA REGISTRADO EN LA PLATOFORMA.
if($datosUser){
    $nombreDeCurso = $DB->get_record('course',array('id'=> reset($datosUser)->courseid),'fullname');
    $nombreUsuario = $DB->get_record('user',array('id'=> reset($datosUser)->userid));
    $nombreDeCurso = $nombreDeCurso->fullname;
    $nombreDeUsuario = $nombreUsuario->firstname." ".$nombreUsuario->lastname;
    $yaestasregistrado = true;
}

//CONSULTA EN LA BD DE MOODLE
$idcurp = $campos[0]; //CURP DE LA QR ESCANEADA
$consultamoodle = array_keys($DB->get_records('user',array('username'=>$idcurp),'','username')); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$estaregis = '';
if(isset($consultamoodle[0])) {
    $estaregis = $consultamoodle[0]; //SE EXTRAE EL USUARIO CON ESA CURP
}
$encuentracurp = 0;
$esinactivo = '';
$datosencontrados = null;
$skipexternalqueries = ($existeerror > 0);
$curp = '';
$inativotogeneral = 0;

if (!$skipexternalqueries) {
    $message = 'Consulta fallida: revisar la consulta configurada en externalcurpquery.';
    $datos = local_qrcurp_execute_template_query($DBEXTERNAL, $consulta, ['curp' => $campos[0]]);
    if ($datos === false) {
        redirect('index.php', $message .\core\notification::error("Informar al administrador del sitio.") , null, \core\output\notification::NOTIFY_ERROR);
    }
    //EXTRAE LA CURP ENCONTRADA EN LA BASE DE DATOS EXTERNA
    $curp = ''; //INICIA VACIO
    while($row = mysqli_fetch_array($datos)) {
        $curp = $row[0];
        //VERIFICA QUE RETORNE UN VALOR
        if($curp != ''){
            //EXISTE UN USUARIO CON LA CURP
            //$curps  = $curp; //GUARDA LA CURP EN UNA NUEVA VARIABLE
            $fechaactual = date('Y-m-d');
            $encuentracurp = 1; //1 si encontro el curp en la primera consulta y 0 no encontro el dato
            $despachador =1;     //PARA SABER CUANDO REGRESA UN VALOR
            $muestramensaje = "Tus datos han sido registrados previamente en la base de datos de $NAMEEXTERNALDBQRCURP, da clic en aceptar, revisa tu información antes de continuar con el registro $nameCategoria"; //MENSAJE A MOSTRAR
            //$datosconsulta = "SELECT rol,username, password, correo,nombre,apellido_p,apellido_m,genero,date_nacimiento,estado,municipio,ocupacion,pais,estado_residencia FROM $remotedbtable where curp = '$idcurp'";
            //            Validación para cuando se encuentra activo
            $datosconsulta ="SELECT
       pa.curp,
       pa.nombre,
       pa.primer_apellido,
       pa.segundo_apellido,
       pa.usuario,
       pa.contrasenia,
       MAX(ps.rol_id),
       r.nombre,
       ps.matricula,
       pa.fecha_nacimiento,
       pa.sexo,
       (SELECT DISTINCT LOWER(con.dato_contacto) FROM tsige_contacto con WHERE con.tipo_contacto_id = 4 AND con.vigente = 1 AND con.dato_contacto NOT LIKE '%@unad%' AND con.persona_id = ps.persona_id) 'Correo Institucional',
       (SELECT edo.v_estado
        FROM (SELECT SUBSTRING(pp.curp, 12, 2) ed, pp.persona_id 'per'
              FROM tsige_persona pp) x
                 INNER JOIN cat_estado edo ON edo.v_abreviacion = x.ed
        WHERE x.per = pa.persona_id)                                                                                                                                                                     'Estado de Nacimiento',
       TIMESTAMPDIFF(YEAR, pa.fecha_nacimiento, '$fechaactual')                                                                                                                                            'Edad',
       (SELECT DISTINCT LOWER(con.dato_contacto)
        FROM tsige_contacto con
        WHERE con.tipo_contacto_id = 4
          AND con.vigente = 1
          AND con.dato_contacto
            NOT LIKE '%@unad%'
          AND con.persona_id = ps.persona_id)                                                                                                                                                            email,
       (SELECT IF(cp.v_codigopostal IS NULL, '-', cp.v_codigopostal)
        FROM direccion d
                 LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
        WHERE d.i_fk_persona = ps.persona_id
          AND d.b_activo = 1
          AND d.b_esAlternativo = 0)                                                                                                                                                                     'Codigo Postal',
       (SELECT IF(muni.v_municipio IS NULL, '-', muni.v_municipio)
        FROM direccion d
                 LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
                 LEFT JOIN cat_asentamiento asen ON asen.i_pk_asentamiento = cp.i_fk_asentamiento
                 LEFT JOIN cat_municipio2 muni ON muni.i_pk_municipio = asen.i_fk_municipio
        WHERE d.i_fk_persona = ps.persona_id
          AND d.b_activo = 1
          AND d.b_esAlternativo = 0)                                                                                                                                                                     'Municipio de residencia',
       (SELECT IF(edo.v_estado IS NULL, '-', edo.v_estado)
        FROM direccion d
                 LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
                 LEFT JOIN cat_asentamiento asen ON asen.i_pk_asentamiento = cp.i_fk_asentamiento
                 LEFT JOIN cat_municipio2 muni ON muni.i_pk_municipio = asen.i_fk_municipio
                 LEFT JOIN cat_estado edo ON edo.i_pk_estado = muni.i_fk_estado
        WHERE d.i_fk_persona = ps.persona_id
          AND d.b_activo = 1
          AND d.b_esAlternativo = 0)                                                                                                                                                                     'Estado de residencia',
       (SELECT IF(pai.vc_clavealfa2 IS NULL, '-', pai.vc_clavealfa2)
        FROM direccion d
                 LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
                 LEFT JOIN cat_asentamiento asen ON asen.i_pk_asentamiento = cp.i_fk_asentamiento
                 LEFT JOIN cat_municipio2 muni ON muni.i_pk_municipio = asen.i_fk_municipio
                 LEFT JOIN cat_estado edo ON edo.i_pk_estado = muni.i_fk_estado
                 LEFT JOIN cat_pais pai ON pai.i_pk_pais = edo.i_fk_pais
        WHERE d.i_fk_persona = ps.persona_id
          AND d.b_activo = 1
          AND d.b_esAlternativo = 0)                                                                                                                                                                     'País de residencia',
       ps.activo                                                                                                                                                                                    'Usuario activo',
       (SELECT tb.descripcion
     FROM tsige_solicitud_baja s
              INNER JOIN tsige_cat_tipo_baja tb ON tb.tipo_baja_id = s.tipo_baja_id
     WHERE s.tipo_baja_id = 6
       AND s.solicitante_id = ps.perfil_id)                                                                                                                                                         'Tipo de baja'
       FROM tsige_persona as pa
         INNER JOIN tsige_perfiles as ps ON ps.persona_id = pa.persona_id
         INNER JOIN tsige_rol as r ON r.rol_id = ps.rol_id
WHERE ps.activo = 1 and pa.curp  = '$curp' AND ps.matricula NOT LIKE 'AS%' HAVING MAX(ps.rol_id) IS NOT NULL";

            $datosconsulta = config::get_string('externaluserinfoquery', $datosconsulta);
            $message = "Error al obtener la data en la base de datos externa, revisar que la consulta configurada sea correcta.";
            $datosencontrados = local_qrcurp_execute_template_query($DBEXTERNAL, $datosconsulta, [
                'curp' => $curp,
                'today' => $fechaactual,
            ]);
            if ($datosencontrados === false) {
                redirect('index.php', $message , null, \core\output\notification::NOTIFY_ERROR);
            }

            if($datosencontrados->num_rows == 0 AND $registropublicogeneral == 1){
//                echo "El usuario es inactivo";
//                Comprobación del usuario para validar que tipo de inactivo es
                $datosconsulta ="SELECT
            pa.curp,
            pa.nombre,
            pa.primer_apellido,
            pa.segundo_apellido,
            pa.usuario,
            pa.contrasenia,
            MAX(ps.rol_id),
            r.nombre,
            ps.matricula,
            pa.fecha_nacimiento,
            pa.sexo,
            (SELECT DISTINCT LOWER(con.dato_contacto) FROM tsige_contacto con WHERE con.tipo_contacto_id = 4 AND con.vigente = 1 AND con.dato_contacto NOT LIKE '%@unad%' AND con.persona_id = ps.persona_id) 'Correo Institucional',
            (SELECT edo.v_estado FROM(
            SELECT SUBSTRING(pp.curp,12,2)ed, pp.persona_id'per'
            FROM tsige_persona pp)x
            INNER JOIN cat_estado edo ON edo.v_abreviacion = x.ed
            WHERE x.per = pa.persona_id)'Estado de Nacimiento',
            TIMESTAMPDIFF(YEAR, pa.fecha_nacimiento, '$fechaactual')'Edad',
            (SELECT DISTINCT LOWER(con.dato_contacto)
            FROM tsige_contacto con
            WHERE con.tipo_contacto_id = 4
            AND con.vigente = 1
            AND con.dato_contacto
            NOT LIKE '%@unad%'
            AND con.persona_id = ps.persona_id) email,
            (SELECT IF(cp.v_codigopostal IS NULL,'-',cp.v_codigopostal)
            FROM direccion d
            LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
            WHERE d.i_fk_persona = ps.persona_id AND d.b_activo = 1 AND d.b_esAlternativo = 0)'Codigo Postal',
            (SELECT IF(muni.v_municipio IS NULL,'-',muni.v_municipio)
            FROM direccion d
            LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
            LEFT JOIN cat_asentamiento asen ON asen.i_pk_asentamiento = cp.i_fk_asentamiento
            LEFT JOIN cat_municipio2 muni ON muni.i_pk_municipio = asen.i_fk_municipio
            WHERE d.i_fk_persona = ps.persona_id AND d.b_activo = 1 AND d.b_esAlternativo = 0)'Municipio de residencia',
            (SELECT IF(edo.v_estado IS NULL,'-',edo.v_estado)
            FROM direccion d
            LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
            LEFT JOIN cat_asentamiento asen ON asen.i_pk_asentamiento = cp.i_fk_asentamiento
            LEFT JOIN cat_municipio2 muni ON muni.i_pk_municipio = asen.i_fk_municipio
            LEFT JOIN cat_estado edo ON edo.i_pk_estado = muni.i_fk_estado
            WHERE d.i_fk_persona = ps.persona_id AND d.b_activo = 1 AND d.b_esAlternativo = 0)'Estado de residencia',
            (SELECT IF(pai.vc_clavealfa2 IS NULL,'-',pai.vc_clavealfa2)
            FROM direccion d
            LEFT JOIN cat_codigopostal cp ON d.i_fk_codigo_postal = cp.i_pk_codigopostal
            LEFT JOIN cat_asentamiento asen ON asen.i_pk_asentamiento = cp.i_fk_asentamiento
            LEFT JOIN cat_municipio2 muni ON muni.i_pk_municipio = asen.i_fk_municipio
            LEFT JOIN cat_estado edo ON edo.i_pk_estado = muni.i_fk_estado
            LEFT JOIN cat_pais pai ON pai.i_pk_pais = edo.i_fk_pais
            WHERE d.i_fk_persona = ps.persona_id AND d.b_activo = 1 AND d.b_esAlternativo = 0)'País de residencia',
            ps.activo'Usuario activo',
            (SELECT tb.descripcion
            FROM tsige_solicitud_baja s 
            INNER JOIN tsige_cat_tipo_baja tb ON tb.tipo_baja_id = s.tipo_baja_id
            WHERE s.tipo_baja_id = 6
            AND s.solicitante_id = ps.perfil_id)'Tipo de baja'
            FROM tsige_persona as pa
            INNER JOIN tsige_perfiles as ps ON ps.persona_id = pa.persona_id
            INNER JOIN tsige_rol as r ON r.rol_id = ps.rol_id
            WHERE ps.activo =0 and pa.curp  ='$curp' AND ps.matricula NOT LIKE 'AS%' HAVING MAX(ps.rol_id) IS NOT NULL";

                $message = "Error al obtener la data en la base de datos externa, revisar que los nombres de los campos sean correctos";
                $datosencontrados = mysqli_query($DBEXTERNAL,$datosconsulta)or die(
                redirect('index.php', $message , null, \core\output\notification::NOTIFY_ERROR)//SI NO SE EJECUTA LA CONSULTA SE RETORNARA A LA PANTALLA INICAL
                );
                $esinactivo =1;
            }

        }
    }

    //Validación de numero de roles para selccionar entre cada uno de ellos
    $masdeunrol = 0;
    $listahtmlroles = '';
    if($esinactivo == '' && !$skipexternalqueries){
        $listaroles = "SELECT
                DISTINCT(ps.rol_id),
                pa.contrasenia,
                ps.rol_id,
                r.nombre nombre_rol,
                ps.matricula,
                (SELECT DISTINCT LOWER(con.dato_contacto) FROM tsige_contacto con WHERE con.tipo_contacto_id = 4 AND con.vigente = 1 AND con.dato_contacto NOT LIKE '%@unad%' AND con.persona_id = ps.persona_id) 'correo_institucional'
            FROM tsige_persona as pa
                     INNER JOIN tsige_perfiles as ps ON ps.persona_id = pa.persona_id
                     INNER JOIN tsige_rol as r ON r.rol_id = ps.rol_id
            WHERE ps.activo = 1 and pa.curp  = '$curp' AND ps.matricula NOT LIKE 'AS%'";
        $message = "Error al obtener la data en la base de datos externa";
        $rolesencontrados = mysqli_query($DBEXTERNAL,$listaroles)or die(
        redirect('index.php', $message , null, \core\output\notification::NOTIFY_ERROR)//SI NO SE EJECUTA LA CONSULTA SE RETORNARA A LA PANTALLA INICAL
        );
        if(isset($rolesencontrados) AND $rolesencontrados->num_rows > 1 ) {
            $numrolesencontrados = $rolesencontrados->num_rows;
            $masdeunrol = 1;
            $listaroles = [];
            $listarolestohtml = [];
            while ($row = mysqli_fetch_array($rolesencontrados)) {
                if($row['contrasenia' != '']){
                    $informacion = strtolower($row['matricula']).'|'.$row['contrasenia'].'|'.$row['rol_id'].'|'.$row['nombre_rol'].'|'.$row['correo_institucional'];
                    array_push($listarolestohtml, $informacion);
                    array_push($listaroles, $row);
                }
            }
            $listarolesdecode = json_encode($listarolestohtml);
        }
        foreach ($listaroles as $itemrole){
            $listahtmlroles = $listahtmlroles . '<button  class="swal-button swal-button--confirm m-2" data-role="'.$itemrole["rol_id"].'">
                        '.$itemrole["nombre_rol"].'
                    </button>';
        }
    }

    $idcurpuser = strtolower($idcurp); //CURP EN MINUSCULAS PARA EL NOMBRE DE USUARIO

    //EN CASO QUE LOS DATOS SE OBTENGAN DEL CURP Y NO ESTE REGISTRADO EN BASE DE DATOS EXTERNA
    $username = $idcurpuser;    //NOMBRE DE Usuario
    $apellido_p = (isset($campos[2])?$campos[2]:'') ;   //PRIMER APELLIDO
    $apellido_m = (isset($campos[3])?$campos[3]:'') ;   //SEGUNDO APELLIDO
    $nombre = (isset($campos[4])?$campos[4]:'') ;     //NOMBRE CURP
    $genero = (isset($campos[5])?substr($campos[5],0,1):'') ;       //GÉNERO
    $fecha_nacimiento = (isset($campos[6])?$campos[6]:'') ;  //FECHA DE NACIMIENTO
    $estado = (isset($campos[7])?$campos[7]:'') ;      //ESTADO DE RESIDENCIA
    $pais = 'MX';       //Pais por defecto
    $ocupacion = "OTRO"; //OCUPACION por defecto LFAS Modificación 25/11/22
    $idrol = "63"; //ID DEL ROL POR DEFECTO ESTUDIANTE LIC/TSU
    $status = 0;
    $tipodeusuario = 0; // 0 es publico general 1 pertenece a la bd externa
    $inativotogeneral =0; //Para cuando se encontro en la bd externa pero no la general LFAS 26/01/23
    $tipodebaja = null;
    $correo = '';
    $matricula = '';
    $cp = '';
    $edad = '';
    if(isset($datosencontrados)) {
        while ($row = mysqli_fetch_array($datosencontrados)) {
            $curp = $row[0];    //CURP
            $nombre = $row[1];   //NOMBRE
            $apellido_p = $row[2]; //APELLIDO PATERNO
            $apellido_m = $row[3]; //APELLIDO MATERNO
//        $username = $row[4]; //USERNAME
            $username = $row[8]; //USERNAME
            $alias = $row[5];    //ALIAS
            $idrol = $row[6];    //ID DE ROL
            $roluser = $row[7];  //ROL NAME
            ($row[8] == '-') ? $matricula = '' : $matricula = $row[8];   //MATRICULA
            ($row[9] == '-') ? $fecha_nacimiento = '' : $fecha_nacimiento = $row[9]; //FECHA DE NACIMIENTO
            ($row[10] == '-') ? $genero = '' : $genero = $row[10];     //GÉNERO
            ($row[11] == '-') ? $correo = '' : $correo = $row[11];   //CORREO
            ($row[12] == '-') ? $estado = '' : $estado = $row[12];  //ESTADO DE NACIMIENTO
            ($row[13] == '-') ? $edad = '' : $edad = $row[13];  //EDAD
            $ocupacion = "EDUCATIVO"; //OCUPACION
            ($row[15] == '-') ? $cp = '' : $cp = $row[15];  //CÓDIGO POSTAL
            ($row[16] == '-') ? $municipio = '' : $municipio = $row[16]; //MUNICIPIO
            ($row[17] == '-') ? $estadoresidencia = '' : $estadoresidencia = $row[17]; //ESTADO DE RESIDENCIA
            ($row[18] == '-' || $row[18] == '') ? $pais = 'MX' : $pais = $row[18];     //PAIS
            $status = $row[19];     //STATUS USUARIO
            $tipodebaja = $row[20];     //tipo de baja
            $tipodeusuario = 1;
        }
    }
    //verifica si el tipo de baja es la definitiva
    if($tipodebaja != null AND $registropublicogeneral == 0){
        $esinactivo = 0;
    }

    if(isset($datosencontrados) AND $datosencontrados->num_rows == 1){
        if($username == null) {
            $esinactivo = 1;
            $tipodebaja = 6;
        }
    }

    //Comprueba los inactivos para dejarlos pasar como publico en general
    if($esinactivo == 1){
        $inativotogeneral = 1;
        if($curp == ''){
            $curp = $campos[0];
        }
        $idcurp = $curp;
        $idcurpuser = strtolower($idcurp); //CURP EN MINUSCULAS PARA EL NOMBRE DE USUARIO
        if($tipodebaja != null){
            $username = $idcurpuser;
        }
//        echo $username ;

        $tipodeusuario =0;
        $status = 0;
        $encuentracurp =0;
        $curp ='';
        $alias = '';
        $correo ='';
        $idrol = "63"; //ID DEL ROL POR DEFECTO publico en general
//        echo "El estado es :".$status;
    }

    //Valida si solo aceptará publico en general omitiendo los de la base de datos externa
    $omiteuserdbexterna = 0;
    if($soloregistropublicogeneral == 1 AND $registropublicogeneral == 1 AND isset($datosencontrados) AND $datosencontrados->num_rows > 0 ){
        $omiteuserdbexterna = 1;
    }

    $estado = strtoupper($estado);

    //SI CONTINUA VACIO NO ENCONTRO LA CURP EN LA BD EXTERNA
    if($curp == ''){
        $despachador = 4;
        $muestramensaje = get_string('noregisexdb','local_qrcurp');
        //$muestramensaje = "Tus datos no han sido registrados previamente, por favor da clic en cada uno de los botones de Registrar para registrarte en la base de datos de la UnADM  o en el Portal de educación contínua.";
    }
    if ($curp == '' AND $estaregis != '' AND $remoteinsertdb == 0){
        //Solo esta registrado en moodle, no en bd externa y no se debe registrar en bdexterna
        $despachador = 3;
        $muestramensaje = get_string('regismoodlenotexdb0','local_qrcurp');
    }
    if ($curp == '' AND $estaregis != '' AND $remoteinsertdb == 1){
        //Solo esta registrado en moodle, no en bd externa y se debe registrar en bdexterna
        $despachador = 0;
        $soloundm = 1;//para cuando ya esta registrado en moodle pero no en undm y se requiere registar
        $muestramensaje = get_string('regismoodlenotexdb1','local_qrcurp');
    }
    if ($curp != '' AND $estaregis != '' ){
        //esta registrado en moodle, y en bd externa
        $despachador = 3;
        //$soloundm = 1;//para cuando ya esta registrado en moodle pero no en undm y se requiere registar
        $muestramensaje = get_string('regismoodleyexdb','local_qrcurp');

    }
    if ($curp == '' AND $estaregis == '' AND $remoteinsertdb == 1){
        //No esta registrado en moodle, ni en bd externa y se debe registrar en bdexterna
        $despachador = 0;
        $soloundm = 0;//para cuando no esta registrado en moodle ni en undm y se requiere registar
        $muestramensaje = get_string('regismoodlenotexdb','local_qrcurp');
    }


}
if($despachador == 3){
    $user = get_complete_user_data('username', $idcurpuser);
    //enviaCorreo($user->id);
}
$correoenuso = get_string('correoregimoodle','local_qrcurp');
$url = $CFG->wwwroot.'/login/signup.php?';
$urlunadm = 'insertardb.php';
$urlsesion = $CFG->wwwroot.'/login/index.php';
$urlsession = $CFG->wwwroot.'/login/index.php';
$registramoodle = 'registramoodle.php';
$urlprincipal = $CFG->wwwroot.'/index.php';

// Configuración dinámica de campos del formulario.
$formfieldsconfigraw = config::get_string('formfieldsconfig');
$formfieldconfig = [];
foreach (preg_split('/\r\n|\r|\n/', $formfieldsconfigraw) as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '|') === false) {
        continue;
    }
    $parts = array_map('trim', explode('|', $line));
    $fieldname = $parts[0] ?? '';
    if ($fieldname === '') {
        continue;
    }
    $formfieldconfig[$fieldname] = [
        'label' => $parts[1] ?? $fieldname,
        'visible' => (isset($parts[2]) && (int) $parts[2] === 0) ? 0 : 1,
        'required' => (isset($parts[3]) && (int) $parts[3] === 0) ? 0 : 1,
    ];
}

$formextrafieldsraw = config::get_string('formextrafields');
$formextrafields = [];
foreach (preg_split('/\r\n|\r|\n/', $formextrafieldsraw) as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '|') === false) {
        continue;
    }
    $parts = array_map('trim', explode('|', $line));
    $shortname = $parts[0] ?? '';
    if ($shortname === '') {
        continue;
    }
    $type = $parts[2] ?? 'text';
    if (!in_array($type, ['text', 'email', 'number', 'date'])) {
        $type = 'text';
    }
    $formextrafields[] = [
        'shortname' => $shortname,
        'label' => $parts[1] ?? ucfirst(str_replace('_', ' ', $shortname)),
        'type' => $type,
        'required' => isset($parts[3]) && (int) $parts[3] === 1,
    ];
}

?>
    <head>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.validate.min.js"></script>
        <link rel="stylesheet" href="css/style.css?version=1.0">
        <link href="https://framework-gb.cdn.gob.mx/assets/styles/main.css" rel="stylesheet">
        <script>
            iddelestado = 0;

            // NUEVO: FUNCIÓN PARA SELECCIONAR CURSO DESDE UTM_CONTENT @LCB
            function selectCourseFromUtm() {
                var urlParams = new URLSearchParams(window.location.search);
                var utmContent = urlParams.get('utm_content');
                var courseSelect = document.getElementById('categorias');

                // LISTA DE IDs VÁLIDOS DE CURSOS @LCB
                var validCourseIds = ['65', '66', '67', '68', '69', '70', '71', '72', '73', '74'];

                if (utmContent && courseSelect && validCourseIds.includes(utmContent)) {
                    // Buscar la opción con el valor del courseId
                    for (var i = 0; i < courseSelect.options.length; i++) {
                        if (courseSelect.options[i].value === utmContent) {
                            courseSelect.value = utmContent;

                            // ACTUALIZAR EL CAMPO HIDDEN idcourse @LCB
                            document.getElementById('idcourse').value = utmContent;

                            // DISPARAR EVENTO CHANGE PARA CARGAR GRUPOS @LCB
                            var event = new Event('change');
                            courseSelect.dispatchEvent(event);

                            console.log('Curso seleccionado automáticamente desde UTM: ' + utmContent + ' - ' + courseSelect.options[i].text);

                            // ✅ MOSTRAR INDICADOR VISUAL
                            showUtmCourseSelection(utmContent, courseSelect.options[i].text);
                            break;
                        }
                    }
                }
            }

            // ✅ NUEVO: FUNCIÓN PARA MOSTRAR INDICADOR DE SELECCIÓN AUTOMÁTICA
            function showUtmCourseSelection(courseId, courseName) {
                // Verificar si ya existe un indicador
                var existingIndicator = document.getElementById('utm-course-indicator');
                if (existingIndicator) {
                    existingIndicator.remove();
                }

                var indicatorHtml = `
            <div id="utm-course-indicator" class="alert alert-success" style="margin: 10px; padding: 10px; border-left: 5px solid #28a745;">
                <strong>✅ Curso preseleccionado desde SaberesMX</strong><br>
                <small>ID: ${courseId} - ${courseName}</small>
            </div>
        `;

                // Insertar antes del formulario
                var form = document.getElementById('envia-info');
                if (form) {
                    form.insertAdjacentHTML('beforebegin', indicatorHtml);
                }
            }

            // ✅ NUEVO: FUNCIÓN PARA CERRAR EL MODAL DE ROLES
            function closeRolesModal() {
                var modal = document.getElementById('modalroles');
                if (modal) {
                    modal.classList.add('not-view');
                    // Opcional: agregar animación de desvanecimiento
                    setTimeout(function() {
                        modal.style.display = 'none';
                    }, 300);
                }
            }

            function updateDateRenapo(nombre,papellido,sapellido,fecha_nacimiento,genero,idestado = 0){
                document.getElementById('nombre').value = nombre;
                document.getElementById('p_apellido').value = papellido;
                document.getElementById('s_apellido').value = sapellido;
                document.getElementById('s_apellido').value = sapellido;
                iddelestado = idestado;
                hayFechaCurp(fecha_nacimiento)
                if(genero == "H"){
                    document.getElementById('genero').value = genero;
                }
            }

            var listarolesdecode = '';
            $(document).ready(function () {

                // ✅ NUEVO: EJECUTAR SELECCIÓN AUTOMÁTICA DE CURSO AL CARGAR LA PÁGINA
                setTimeout(function() {
                    selectCourseFromUtm();
                }, 500);

                var yaestaregsitrado = '<?= $yaestasregistrado ?> ';

                if( yaestaregsitrado == true){
                    const elurl = document.createElement('div')
                    elurl.innerHTML = "<a href='/local/qrcurp/recovery/' target='_blank'>Clic aquí</a>"
                    menssage="Estimada(o) "+"<?= $nombreDeUsuario ?>"+", ya te encuentras registrado en "+"<?=$nameCategoria?>"+" . \n\n Inicia sesión con las credenciales de acceso que te enviamos previamente a tu correo electrónico. \n\n Si no recuerdas o no encuentras tus credenciales, da clic a continuación para recibirlas nuevamente: \n\n "
                    document.getElementById("envia-info").remove();
                    document.getElementsByClassName("colors")[0].remove();
                    swal(menssage, {
                        content: elurl,
                        buttons: "Aceptar",
                        timer: 19000,
                    })
                        .then((value) => {
                            redirect = "<?= $urlprincipal ?>"
                            window.location.href = redirect
                        });
                }

                var masdeunrol = '<?=$masdeunrol?>';
                var numrolesencontrados = '<?=$numrolesencontrados?>';
                if('<?=$listarolesdecode?>' != ''){
                    listarolesdecode = JSON.parse('<?=$listarolesdecode?>');
                }
                var typeuser = '<?=$tipodeusuario?>';
                var omiteuserdbexterna = '<?=$omiteuserdbexterna?>';
                let curpvalida = 1;
                document.getElementById('curpvalida').value = curpvalida

                if(omiteuserdbexterna == 1){
                    menssage = "Actualmente el registro solo se encuentra abierto para publico en general.";
                    swal(menssage, {
                        buttons: "Aceptar",
                        timer: 4000,
                    }).then((value) => {
                        window.location.href = "index.php";
                    });
                }

                if(typeuser == 0){
                    let curpUser =  '<?= strtoupper($campos[0]);?>';
                    let validausuario = 0;
                    let validaconrenapo = '<?= $validaconrenapo?>';
                    let omitevalidacionrenapo = '<?= $omitevalidacionrenapo?>';
                    //Valida curp con la RENAPO
                    if(curpUser != '' && validausuario == 0 && validaconrenapo == 1) {
                        // Crear una nueva instancia de XMLHttpRequest
                        var xhttp = new XMLHttpRequest();
                        // Definir la función de devolución de llamada para manejar la respuesta del servidor
                        xhttp.onload = function () {
                            if (this.status == 200 && this.status < 300) {
                                //Válida si la conexión fue exitosa
                                let dataSendCurp = JSON.parse(this.responseText);
                                curpvalida = 0;
                                if(dataSendCurp["@attributes"].statusOper == 'EXITOSO'){
                                    curpvalida = 1;
                                }
                                document.getElementById('curpvalida').value = curpvalida
                                if(curpvalida == 1){
                                    updateDateRenapo(dataSendCurp.nombres,dataSendCurp.apellido1,dataSendCurp.apellido2 ,dataSendCurp.fechNac, dataSendCurp.sexo, dataSendCurp.numEntidadReg)
                                }else if(omitevalidacionrenapo == 1){
                                    //Para agregar elementos que se requieren para el tipo de usuarios
                                }else{
                                    document.getElementById("envia-info").remove();
                                    menssage = "Solo integrantes UnADM pueden inscribirse.";
                                    swal(menssage, {
                                        buttons: "Aceptar",
                                        timer: 4000,
                                    }).then((value) => {
                                        window.location.href = "index.php";
                                    });
                                }
                            }else if(omitevalidacionrenapo == 1){
                                console.log("Continua con registro sin datos 2");
                            }else{
                                document.getElementById("envia-info").remove();
                                menssage = "Solo integrantes UnADM pueden inscribirse.";
                                swal(menssage, {
                                    buttons: "Aceptar",
                                    timer: 4000,
                                }).then((value) => {
                                    window.location.href = "index.php";
                                });
                            }
                        };
                        xhttp.onerror = function() {
                            if(omitevalidacionrenapo == 1){
                                console.log("Continua con registro sin datos 3");
                            }else{
                                document.getElementById("envia-info").remove();
                                menssage = "Solo integrantes UnADM pueden inscribirse.";
                                swal(menssage, {
                                    buttons: "Aceptar",
                                    timer: 4000,
                                }).then((value) => {
                                    window.location.href = "index.php";
                                });
                            }
                        };

                        // Enviar la solicitud al servidor
                        xhttp.open("POST", "includes/serviceDataCurp.php", true);
                        var datos = "curp=" + encodeURIComponent(curpUser);
                        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhttp.send(datos);
                    }
                }

                $("#envia-info").validate();
                $.post("includes/getPais.php", {}, function (data) {
                    $("#id_country").html(data);
                });
                $.post("includes/getEstados.php", {}, function (data) {
                    $("#e_nacimiento").html(data);
                    if (iddelestado != 0) {
                        $.post("includes/getEstadosJson.php", {iddelestado:iddelestado}, function (data) {
                            iddelestado = data;
                        });
                    }
                });
                $("#codigo-postal").change(function () {
                    extraerSepomex();
                })
                $("#isafiliado").change(function () {
                    if($("#isafiliado").val() == "S"){
                        $("#isnotafiliado").css("display",'block')
                    }else {
                        $("#isnotafiliado").css("display",'none')
                    }
                })
                $("#howenteraste").change(function () {
                    if($("#howenteraste").val() == "otro"){
                        $("#enteradootro").css("display",'block')
                    }else {
                        $("#enteradootro").css("display",'none')
                    }
                })
                $("#motivoinscripcion").change(function () {
                    if($("#motivoinscripcion").val() == "otro"){
                        $("#motivootro").css("display",'block')
                    }else {
                        $("#motivootro").css("display",'none')
                    }
                })
                var idcategoria = "<?php echo $categoryid?>";
                var idcurso = "<?php echo $idcourse?>";
                var idgrupo = "<?php echo $typegrouping?>";

                if (idcategoria != 0) {
                    $("<div>", {
                        'class': 'form-group'
                    }).append(
                        $("<p>", {
                            'id': 'combo-categorias'
                        })
                    ).appendTo('#nuevos-elementos');

                    if(idcategoria == 0) {
                        document.getElementById("combo-categorias").textContent = "Comunidades de práctica: *";
                    }else{
                        if(document.getElementById("combo-categorias")!= null){
                            document.getElementById("combo-categorias").textContent = "Cursos: *";
                        }
                    }
                    $('<select>', {
                        'class': 'form-control',
                        'id': 'categorias',
                        'name': 'categorias',
                        'required': 'true',
                        'title': 'Este campo es requerido.'
                    }).appendTo("#combo-categorias");

                    var tipodeusuario = "<?php echo $tipodeusuario?>";
                    var idrol = "<?php echo $idrol?>";

                    coursesValidation(idrol);
                    function coursesValidation(idrol) {
                        $.post("includes/CategoriasMoodle.php", {
                            idcategoria: idcategoria,
                            tipodeusuario: tipodeusuario,
                            idrol: idrol
                        }, function (data) {
                            if (data == 'not_config_courses') {
                                menssage = "No existen cursos configurados, modifica el campo 'showuniquecourseslist' en la configuración del plugin.";
                                swal(menssage, {
                                    buttons: "Aceptar",
                                    timer: 8000,
                                }).then((value) => {
                                    window.location.href = "index.php";
                                });
                            }
                            if (data == 'error_not_active_config') {
                                menssage = "El parametro de configuración 'showuniquecourses' debe estar habilitado para utilizar la configuración de 'acceptcoursesdbexternal' del plugin.";
                                swal(menssage, {
                                    buttons: "Aceptar",
                                    timer: 8000,
                                }).then((value) => {
                                    window.location.href = "index.php";
                                });
                            }
                            $("#categorias").html(data);

                            // ✅ NUEVO: EJECUTAR SELECCIÓN DESPUÉS DE CARGAR LOS CURSOS
                            setTimeout(function() {
                                selectCourseFromUtm();
                            }, 100);
                        });
                    }

                    if (idcurso != 0) {
                        document.getElementById('idcourse').value = idcurso;
                        setTimeout("$('#categorias').val($('#idcourse').val());", 2000);
                    }
                    setTimeout("groupsHorarios()", 3000);

                    if (idcategoria != 0) {
                        $("<div>", {
                            'class': 'form-group'
                        }).append(
                            $("<p>", {
                                'id': 'combo-grupos',
                            })
                        ).appendTo('#nuevos-elementos');

                        if (idcategoria == 0) {
                            document.getElementById("combo-grupos").textContent = "Horarios disponibles";
                        } else {
                            if(document.getElementById("combo-grupos")!= null){
                                document.getElementById("combo-grupos").textContent = "Grupos disponibles";
                            }
                        }
                        if(idgrupo != 0){
                            $("#combo-grupos").css("display","none")
                        }else {
                            $('<select>', {
                                'class': 'form-control',
                                'id': 'grupos',
                                'name': 'grupos',
                                'required': 'true',
                                'title': 'Este campo es requerido.'
                            }).appendTo("#combo-grupos");
                        }
                        $('#categorias').change(function () {
                            idcurso = $('#categorias').val();
                            $.post("includes/getGroupsMoodle.php", {idcurso: idcurso}, function (data) {
                                $("#grupos").html(data);
                            });
                            setTimeout(" idgrupo = $('#grupos').val();", 2000);
                            document.getElementById('idcourse').value = idcurso;
                            document.getElementById('typegrouping').value = idgrupo;
                        })
                        $('#grupos').change(function () {
                            idgrupo = $('#grupos').val();
                            document.getElementById('typegrouping').value = idgrupo;
                        })
                        if (idgrupo != 0 ) {
                            document.getElementById('typegrouping').value = idgrupo;
                            setTimeout("$('#grupos').val($('#typegrouping').val());", 4000);
                        }
                    }
                }

                var read = 0;
                $("#texto-terminos-condiciones").on("scroll",function () {
                    let element = document.getElementById("texto-terminos-condiciones");
                    if (element.offsetHeight + element.scrollTop >= element.scrollHeight) {
                        document.getElementById("register-terms_of_service").disabled = false;
                        document.getElementById("register-terms_of_service").ckecked = true;
                        read = 1;
                        $('#leer-aviso').css('display','none');
                        $('#readall-terminos').css('display','none');
                    }
                });

                $('#aviso-privacidad').click(function (){
                    if(read != 1){
                        $('#leer-aviso').css('display','block');
                        $('#readall-terminos').css('display','block');
                    }else{
                        $('#leer-aviso').css('display','none');
                        $('#readall-terminos').css('display','block');
                    }
                });

                $('#pass').change(function () {
                    if ($('#pass').val() == "") {
                        document.getElementById("pass").classList.add('error');
                        return false;
                    }
                    valor = document.getElementById("pass").value;
                    if (!(/(?=(?:.*\d){1})(?=(?:.*[A-Z]){1})(?=(?:.*[a-z]){1})(?=(?:.*[$*#\-_]){1})\S{8,16}$/.test(valor))) {
                        $("#validate-pass").css("display","block");
                        document.getElementById("pass").style.border = '2px solid red';
                        return false;
                    } else {
                        $("#validate-pass").css("display","none");
                        document.getElementById("pass").style.border = '1px solid green';
                    }
                });

                $("#email").change(function () {
                    cadena = "email=" + $('#email').val();
                    $.ajax({
                        type: "POST",
                        url: "includes/compruebaemail.php",
                        data: cadena,
                        success: function (r) {
                            if (r == 1) {
                                swal("<?php echo $correoenuso ?>");
                                $("#email").val("");
                            }
                        }
                    });
                })

                setTimeout("datacurp()",2000);

                if(masdeunrol == 1 && numrolesencontrados >1 ){
                    document.getElementById('modalroles').classList.remove('not-view');
                    document.getElementById('message-modal-roles').textContent = 'Estimado(a) participante la CURP proporcionada se encuentra asociada a más de un rol, selecciona a continuación el rol con el que deseas realizar el registro y valida la información:';

                    // ✅ MODIFICADO: EVENT LISTENER MEJORADO PARA CERRAR EL MODAL
                    document.getElementById('info-modal-roles').addEventListener('click', function(event) {
                        if (event.target.tagName === 'BUTTON') {
                            const selectedRole = event.target.getAttribute('data-role');
                            listarolesdecode.forEach(function(elemento) {
                                var datos = elemento.split('|');
                                if(selectedRole == datos[2] ) {
                                    document.getElementById('username').value = datos[0];
                                    document.getElementById('matricula').value = datos[0];
                                    document.getElementById('session_alias').value = datos[1];
                                    document.getElementById('rol').value =  datos[2];
                                    document.getElementById('rolname').value =  datos[3];
                                    document.getElementById('email').value =  datos[4];

                                    // ✅ NUEVO: CERRAR EL MODAL DESPUÉS DE SELECCIONAR
                                    closeRolesModal();
                                    coursesValidation(selectedRole);
                                    return;
                                }
                            });
                        }
                    });

                }else{
                    document.getElementById('modalroles').remove();
                }
            });

            function datacurp(){
                $('#id_country').val('<?php echo $pais ?>');
                $('#ocupacion').val('<?php echo $ocupacion ?>');
                if(iddelestado != 0) {
                    $('#e_nacimiento').val(iddelestado);
                }else{
                    $('#e_nacimiento').val('<?php echo $estado ?>');
                }
                valgenero = '<?php echo $genero;?>';
                if(valgenero != ''){
                    $('#genero').val('<?php echo $genero ?>');
                }
                if (document.getElementById('existeuserdb') != null && document.getElementById('existeuserdb').textContent == 1) {
                    codigo_postal = '<?=$cp; ?>';
                    if(codigo_postal != ''){
                        $('#codigo-postal').val('<?php echo $cp ?>');
                        extraerSepomex();
                    }
                    $('#edad').val('<?php echo $edad ?>');
                    $('#date_nacimientos').val('<?php echo $fecha_nacimiento ?>');
                    $('#matricula').val('<?php echo $matricula ?>');
                    $('#rol').val('<?php echo $idrol ?>');
                }
                else {
                    hayFechaCurp();
                    curp = '<?php echo $campos[0]?>'
                    curp = curp.substring(0, 10);
                    $('#matricula').val(curp);
                    $('#rol').val(<?php echo $idrol?>);
                    var sel = document.getElementById("rol");
                    if (sel != null) {
                        var text = sel.options[sel.selectedIndex].text;
                        $('#rolname').val(text);
                        document.getElementById("user-not-view-info").style.display = 'none'
                        setTimeout(function () {
                            var inactivoToGeneral = <?= (int)$inativotogeneral ?>;
                            dato = document.getElementById("envia-info").querySelectorAll(".form-control");
                            tam = dato.length;
                            if (inactivoToGeneral == 1) {
                            } else {
                                if(typeuser.value == 1) {
                                    for (i = 0; i <= tam - 1; i++) {
                                        if (dato[i].value != '') {
                                            dato[i].setAttribute("readonly", "");
                                            dato[i].classList.add('control-data-form');
                                        }
                                    }
                                }
                            }
                            document.getElementById("ocupacion").removeAttribute("readonly");
                            document.getElementById("ocupacion").classList.remove('control-data-form');
                            if (document.getElementById("categorias") != null) {
                                document.getElementById("categorias").removeAttribute("readonly");
                                document.getElementById("categorias").classList.remove('control-data-form');
                                if (document.getElementById("grupos") != null) {
                                    document.getElementById("grupos").removeAttribute("readonly");
                                    document.getElementById("grupos").classList.remove('control-data-form');
                                }
                            }
                        }, 2000);
                    }
                }
            }

            function extraerSepomex() {
                $('#e_residencias').find('option').remove().end().append('<option value=""></option>').val('');
                id_postal = $("#codigo-postal").val();
                $.post("includes/getEstado.php", {id_postal: id_postal}, function (data) {
                    $("#e_residencias").html(data);
                });
                $.post("includes/getMunicipio.php", {id_postal: id_postal}, function (data) {
                    $("#municipios").html(data);
                });
            }

            function hayFechaCurp(fechasend = '') {
                if ($('#date_nacimientos').val() == '') {
                    if(fechasend != ''){
                        fechacurp = fechasend
                    }else {
                        fechacurp = "<?php echo $fecha_nacimiento?>";
                    }
                    edad = calcularEdad(fechacurp);
                    fechacurp = fechacurp.split('/').reverse().join('-');
                    $('#date_nacimientos').val(fechacurp);
                    document.getElementById("edad").value = edad;
                }
            }

            function calcularEdad(birthday) {
                birthday = new Date(birthday.split('/').reverse().join('-'));
                var ageDifMs = Date.now() - birthday.getTime();
                var ageDate = new Date(ageDifMs);
                return Math.abs(ageDate.getUTCFullYear() - 1970);
            }

            function groupsHorarios() {
                idcursos = $('#categorias').val();
                $.post('includes/getGroupsMoodle.php', {idcurso: idcursos}, function (data) {
                    $('#grupos').html(data);
                });
            }
        </script>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    </head>
    <!--validación de roles de usuarios-->
    <div id="modalroles" class="modal-roles not-view">
        <div class="modal-content">
            <p id="message-modal-roles"></p>
            <div class="col-md-12" id="info-modal-roles">
                <?php
                echo $listahtmlroles;
                ?>
            </div>
        </div>
    </div>
    <div class="colors">
        <div class="container">
            <br><br><br>
            <div class="row ">
                <div class="col-md-8 offset-md-2 card" id="medio">
                    <div class="panel-heading">
                        <h1 id="texto-mostrado">Para continuar con el registro, por favor, confirma tus datos personales:</h1>
                    </div>
                    <br>
                    <!--                    <div id="ya-tienes-cuenta">-->
                    <!--                        <span class="text">¿Ya tienes una cuenta de usuario?-->
                    <!--                            <a id="index-sesion-moodle" href="--><?//= $urlsession?><!--" data-type="login">Iniciar sesión.</a>-->
                    <!--                        </span>-->
                    <!--                        <p><br><br><strong>Crear una cuenta</strong></p>-->
                    <!--                    </div>-->
                    <form id="envia-info" action="<?= $registramoodle?>" method="post" enctype="multipart/form-data">
                        <!-- ✅ NUEVO: Campos ocultos para el origen -->
                        <input type="hidden" name="origin" value="<?php echo $origin; ?>">
                        <input type="hidden" name="is_saberes_mx" value="<?php echo $is_saberes_mx ? '1' : '0'; ?>">

                        <div class="form-group">
                            <p>CURP: <input style="text-transform:uppercase" class="form-control" id = "curp" name="curp" readonly type="text" value="<?php echo $campos[0];?>"></p>
                        </div>
                        <div class="form-group">
                            <p>Nombre de usuario:<span class="red-text"> *</span><input readonly class="form-control" id= "username" name="username" type="text" value="<?php echo $username;?>" required pattern="[a-z]{2,254}" title="Nombre de usuario: solo puede contener letras minúsculas"></p>
                        </div>
                        <div class="form-group">
                            <p id="contra">Contraseña:
                                <span class="red-text"> *</span>
                                <br>
                                <input title="Este campo es requerido." readonly class="form-control password" id="pass" name="pass" type="password" >
                                <span class="fa fa-fw fa-eye password-icon show-password" onclick="viewPassword();"></span>
                            </p>
                            <p id="validate-pass" style="display: none; background-color: yellow"><b>La contraseña debe tener al menos 8-12 caracteres, al menos 1 dígito(s), al menos 1 letra(s) minúscula(s), al menos 1 letra(s) mayúscula(s), al menos 1(s) carácter(es) especial(es) como *, -, o #</b></p>
                        </div>
                        <div class="form-group">
                            <p>Nombre(s):<span class="red-text"> *</span><input style="text-transform:uppercase" class="form-control" id="nombre" name="nombre" type="text" value="<?php echo $nombre;?>" title="Ingresa un nombre válido."  required ></p>
                        </div>
                        <div class="form-group">
                            <p>Primer apellido:<span class="red-text"> *</span> <input  style="text-transform:uppercase" class="form-control" id="p_apellido" name="p_apellido" type="text" value="<?php echo $apellido_p;?>" title="Ingresa un apellido válido."  required></p>
                        </div>
                        <div class="form-group">
                            <p>Segundo apellido:<span class="red-text"> *</span> <input style="text-transform:uppercase" class="form-control" id="s_apellido" name="s_apellido" type="text" value="<?php echo $apellido_m;?>" title="Ingresa un apellido válido."  required></p>
                        </div>
                        <div class="form-group">
                            <p>
                                <label for="email">Correo electrónico:<span class="red-text"> *</span></label>
                                <input  class="form-control" id="email" name="email" type="email" value="<?php echo $correo;?>" title="Ingresa un correo electrónico válido." required></p>
                        </div>
                        <div class="form-group">
                            <p>País:<span class="red-text"> *</span> <br> <select class="form-control" name="id_country" id="id_country" required title="Este campo es requerido."></select>
                        </div>
                        <div class="form-group">
                            <p>Código postal:<span class="red-text"> *</span><br><input class="form-control" type="text" name="codigo-postal" id="codigo-postal" title="Este campo es requerido." required></p>
                        </div>
                        <div class="form-group">
                            <p>Estado de residencia:<span class="red-text"> *</span><br><select class="form-control" name="e_residencias" id="e_residencias" title="Este campo es requerido." required ></select></p>
                        </div>
                        <div class="form-group">
                            <p>Municipio:<span class="red-text"> *</span> <br><select class="form-control" name="municipios" id="municipios" title="Este campo es requerido." required></select></p>
                        </div>
                        <div class="form-group">
                            <p>Estado de nacimiento:<span class="red-text"> *</span><br><select class="form-control" id="e_nacimiento" name="e_nacimiento" title="Este campo es requerido." required>
                                </select> </p>
                        </div>
                        <div class="form-group">
                            <p>Fecha de nacimiento:<span class="red-text"> *</span> <input class="form-control"  id="date_nacimientos" name="date_nacimientos" type="date" title="Este campo es requerido." required value=""></p>
                        </div>
                        <div class="form-group">
                            <p>Edad:<span class="red-text"> *</span><br><input class="form-control" style="text-transform:uppercase" id="edad" name="edad" type="number" value="<?php echo $edad;?> " title="Este campo es requerido." required >
                                <span class="anios-position"> años</span></p>
                        </div>
                        <div >
                            <p>Sexo:<span class="red-text"> *</span><br>
                                <select class="form-control" id="genero" name="genero" title="Este campo es requerido."  required>
                                    <option value="" >Seleccionar Sexo</option>
                                    <option value="M">MUJER</option>
                                    <option value="H">HOMBRE</option>
                                </select>
                        </div>
                        <div>
                            <p>Ocupación/Sector:<span class="red-text"> *</span><br>
                                <select class="form-control" id="ocupacion" name="ocupacion" title="Este campo es requerido." required>
                                    <option value="">Seleccionar Ocupación</option>
                                    <option value="EDUCATIVO">EDUCATIVO</option>
                                    <option value="CULTURAL">CULTURAL</option>
                                    <option value="COMERCIAL">COMERCIAL</option>
                                    <option value="GOBIERNO">GOBIERNO</option>
                                    <option value="SALUD">SALUD</option>
                                    <option value="OTRO">OTRO</option>
                                </select>
                            </p>
                        </div>
                        <div id="user-not-view-info">
                            <div class="form-group">
                                <p>Matricula: <span class="red-text"> *</span><input style="text-transform:uppercase" class="form-control" id="matricula" name="matricula" type="text" value="<?php echo $matricula;?> " title="Ingresa un nombre válido."  required ></p>
                            </div>
                            <div>
                                <p>Rol:<span class="red-text"> *</span>
                                    <br>
                                    <select class="form-control" id="rol" name="rol" required>
                                        <option value="0">Seleccionar Rol</option>
                                        <option value="1">Interesado a estudiante</option>
                                        <option value="2">Aspirante a estudiante</option>
                                        <option value="3">Estudiante Lic/TSU</option>
                                        <option value="4">Egresado</option>
                                        <option value="5">Titulado</option>
                                        <option value="6">Pasante</option>
                                        <option value="7">Aspirante a docente en línea</option>
                                        <option value="8">Docente en línea</option>
                                        <option value="9">Asesor Académico</option>
                                        <option value="10">Tutor</option>
                                        <option value="11">Posdoctorante</option>
                                        <option value="12">Personal Administrativo</option>
                                        <option value="13">Prerregistro</option>
                                        <option value="14">Aspirante Posgrado</option>
                                        <option value="15">Estudiante Posgrado</option>
                                        <option value="16">Jefe de Carrera Lic/TSU</option>
                                        <option value="17">Jefe de División Lic/TSU</option>
                                        <option value="18">Jefe de Carrera Posgrado</option>
                                        <option value="19">Coordinador Lic/TSU</option>
                                        <option value="20">Jefe de División Posgrado</option>
                                        <option value="21">Jefe de División Posgrado</option>
                                        <option value="22">Aspirante admitido Lic/TSU</option>
                                        <option value="23">Aspirante admitido Posgrado</option>
                                        <option value="24">Escolares</option>
                                        <option value="25">Maestro</option>
                                        <option value="26">Estudiante Innovatic</option>
                                        <option value="27">Aspirante posgrado selección</option>
                                        <option value="28">Apoyo docente</option>
                                        <option value="29">Educandos</option>
                                        <option value="30">Promotor</option>
                                        <option value="31">Monitor Académico</option>
                                        <option value="32">Apoyo al Monitor Académico</option>
                                        <option value="33">Aspirante Lic/TSU No admitido</option>
                                        <option value="34">Aspirante Posgrado No admitido</option>
                                        <option value="35">Gestor de perfiles por grupo Lic/TSU</option>
                                        <option value="36">Gestor de de base de datos</option>
                                        <option value="37">Aulas</option>
                                        <option value="38">Candidato a docente en línea</option>
                                        <option value="39">Conapace</option>
                                        <option value="40">Recursos Humanos</option>
                                        <option value="41">Candidato a docente en línea admitido</option>
                                        <option value="42">Candidato a docente en línea no admitido</option>
                                        <option value="43">Responsable de evaluación</option>
                                        <option value="44">Aspirante a investigador</option>
                                        <option value="45">Investigador</option>
                                        <option value="46">Administrador SIA</option>
                                        <option value="47">Mesa de ayuda</option>
                                        <option value="48">Investigador PIMITFAM</option>
                                        <option value="50">Administrador PIMITFAM</option>
                                        <option value="52">Apoyo a responsable de p. e.</option>
                                        <option value="53">Asesor metodológico</option>
                                        <option value="54">Director de Division PIIn</option>
                                        <option value="55">Responsable de Programa Educativo PIIn</option>
                                        <option value="56">Evaluador CIEES</option>
                                        <option value="57">Apoyo de asuntos escolares</option>
                                        <option value="58">Dictaminador</option>
                                        <option value="59">Tutor en línea</option>
                                        <option value="60">Egresado de Posgrado</option>
                                        <option value="61">Consulta Externo</option>
                                        <option value="62">Mesa CTIE</option>
                                        <option value="63">Público en general</option>

                                    </select>
                                </p>
                            </div>
                        </div>
                        <div id="nuevos-elementos">
                        </div>
                        <div  id ="eresdocente" style="display: none">
                            <input type="checkbox" id="completa-registro" onchange="validaSeleccion();" "="">
                            <label><b>Completa el registro</b> y ayuda a la investigación académica brindando más información.</label>
                            <input onchange="validaSelecciondocen();" id="register-eres_docente" type="checkbox" name="eres_docente" class="input-block checkbox" data-errormsg-required="This field is required." value="">
                            <label for="register-eres_docente"><b>¿Eres docente?</b> Completa los siguientes datos.</label>
                        </div>
                        <div id="opcionalesdocen" style="display: none">
                            <label for="register-cct" class="focus-out">
                                <span class="label-text">Clave de tu Centro de Trabajo C.C.T.</span>
                                <span class="label-optional" id="register-cct-optional-label">(optional)</span>
                            </label><br>
                            <input id="register-cct" type="text" name="cct" class="input-block " maxlength="10" data-errormsg-required="This field is required." value="">
                            <br><label for="register-funcion" class="focus-out">Funciones</label><br>
                            <select data-hj-suppress="" id="register-funcion" name="funcion" class="input-inline" data-errormsg-required="This field is required." data-errormsg-invalid_choice="Select a valid choice. %(value)s is not one of the available choices.">
                                <option value=""></option>
                                <option value="0">DOCENTE FRENTE A GRUPO</option>
                                <option value="1">ADMINSTRATIVAS</option>
                                <option value="2">DIRECTIVAS</option>
                                <option value="3">TÉCNICAS</option>
                                <option value="4">OTRAS</option>
                            </select>
                            <br><label for="register-nivel_Educativo" class="focus-out">Nivel educativo que imparte</label><br>
                            <select data-hj-suppress="" id="register-nivel_Educativo" name="nivel_Educativo" class="input-inline" data-errormsg-required="This field is required." data-errormsg-invalid_choice="Select a valid choice. %(value)s is not one of the available choices.">
                                <option value=""></option>
                                <option value="0">EDUCACIÓN PREESCOLAR</option>
                                <option value="1">EDUCACIÓN PRIMARIA</option>
                                <option value="2">EDUCACIÓN SECUNDARIA</option>
                                <option value="3">EDUCACIÓN MEDIA SUPERIOR</option>
                                <option value="4">EDUCACIÓN SUPERIOR</option>
                                <option value="5">FORMACIÓN DOCENTE (ESCUELA NORMAL)</option>
                                <option value="6">EDUCACIÓN ESPECIAL</option>
                                <option value="7">EDUCACIÓN INDÍGENA</option>
                                <option value="8">EDUCACIÓN PARA ADULTOS</option>
                                <option value="9">CAPACITACIÓN PARA TRABAJO</option>
                            </select>
                            <br><label for="register-asignatura" class="focus-out">
                                <span class="label-text">Asignatura que imparte</span>
                                <span class="label-optional" id="register-asignatura-optional-label">(optional)</span>
                            </label><br>
                            <input id="register-asignatura" type="text" name="asignatura" class="input-block " maxlength="120" data-errormsg-required="This field is required." value="">
                            <label for="register-goals" class="focus-out">
                                <span class="label-text">Cuéntanos por qué te interesa <?=$NAMEPLATAFORMQRCURP?></span>
                                <span class="label-optional" id="register-goals-optional-label">(optional)</span>
                            </label>
                            <textarea id="register-goals" type="textarea" name="goals" class="input-block" data-errormsg-required="Tell us your goals."></textarea>
                        </div>
                        <?php if (!empty($formextrafields)) { ?>
                            <?php foreach ($formextrafields as $extrafield) { ?>
                                <div class="form-group dynamic-extra-field">
                                    <p><?php echo s($extrafield['label']); ?>:
                                        <?php if ($extrafield['required']) { ?><span class="red-text"> *</span><?php } ?>
                                        <input class="form-control"
                                               id="extra_<?php echo s($extrafield['shortname']); ?>"
                                               name="extra_fields[<?php echo s($extrafield['shortname']); ?>]"
                                               type="<?php echo s($extrafield['type']); ?>"
                                            <?php if ($extrafield['required']) { ?>required<?php } ?>>
                                    </p>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <br>
                        <?php
                        ($categoryid=='')?$categoryid=0:$categoryid;
                        echo avisoDePrivacidad($categoryid);
                        ?>
                        <br>
                        <label for="register-terms_of_service">
                            <input id="register-terms_of_service" type="checkbox" disabled name="terms_of_service" class="input-block checkbox check-size" required title="Debe aceptar los Términos de Servicio de <?=$NAMEPLATAFORMQRCURP?>" >
                            <u style="cursor: pointer;  text-decoration: underline; color: darkblue;">
                                <span class="red-text" id="leer-aviso" style="display: none">Debes leer el aviso de privacidad.</span>
                                <a id="aviso-privacidad">He leído y acepto el Aviso de privacidad</a>
                                de <?=$NAMEPLATAFORMQRCURP?>
                            </u>
                        </label>
                        <p style="display: none" id="urlmoodle" name="urlmoodle"><?php echo $url?></p>
                        <p style="display: none" id="message" name="message"><?php echo $muestramensaje?></p>
                        <div style="display: none;">
                            <span>Estado del usuario?</span><p  id="compruebaActivo"><?= $status ?></p>
                            <span>Se insertara en la bd externa</span><p id="external"><?= $remoteinsertdb ?> </p>
                            <span>Encuentra el dato en la bd externa</span><p id="existeuserdb"><?= $encuentracurp ?></p>
                            <span>Acepta registros publico general</span><p id="publicogeneral"><?= $registropublicogeneral ?></p>
                            <span>Despachador</span> <p id="despachador"><?= $despachador ?> </p>
                            <input type="hidden" id="rolname" name="rolname" value="<?php echo $roluser ?>">
                            <span>id Curso</span><input type="hidden"  id="idcourse" name="idcourse" value="<?php echo $idcourse ?>">
                            <!--                            <span>id grupo</span><input type="hidden" id="typegrouping" name="typegrouping" value="--><?php //echo $typegrouping ?><!--">-->
                            <!--                            <span>id grupo</span><input type="hidden" id="typegrouping" name="typegrouping" value="10001">-->
                            <span>id grupo</span><input type="hidden" id="typegrouping" name="typegrouping" value="<?php echo $typegrouping ?>">
                            <span>NombreCategoria</span><input type="hidden" id="namecategory" name="namecategory" value="<?php echo $nameCategoria ?>">
                            <span>TipoRegistro</span><input type="hidden" id="typeuser" name="typeuser" value="<?php echo $tipodeusuario ?>">
                            <span>InactivoToGeneral</span><input type="hidden" id="inactivotogeneral" name="inactivotogeneral" value="<?php echo $inativotogeneral ?>">
                            <span>Validateuser</span><input type="hidden" id="curpvalida" name="curpvalida" value="">
                        </div>
                        <input readonly id="session_alias" name="session_alias" type="hidden" value="<?php echo $alias;?>">
                        <input id="verifica" disabled type="submit" class="btn btn-md btn-block btn-success" value="Continuar">
                        <!--<input id="nuevamente" type="button" class="btn btn-md btn-block btn-dark" onclick="javascript:history.back();" value="Intentarlo nuevamente">-->
                    </form>
                </div>
            </div>
            <script>
                (function() {
                    const fieldConfig = <?php echo json_encode($formfieldconfig); ?> || {};
                    Object.keys(fieldConfig).forEach(function(fieldName) {
                        const config = fieldConfig[fieldName] || {};
                        let element = document.querySelector('[name="' + fieldName + '"]');
                        if (!element) {
                            element = document.getElementById(fieldName);
                        }
                        if (!element) {
                            return;
                        }
                        const group = element.closest('.form-group') || element.closest('div') || element.parentElement;
                        if (Number(config.visible) === 0) {
                            if (group) {
                                group.style.display = 'none';
                            } else {
                                element.style.display = 'none';
                            }
                            element.removeAttribute('required');
                            element.disabled = true;
                            return;
                        }
                        if (Number(config.required) === 0) {
                            element.removeAttribute('required');
                        } else {
                            element.setAttribute('required', 'required');
                        }
                        if (group && config.label) {
                            const labelContainer = group.querySelector('p');
                            if (labelContainer) {
                                const current = labelContainer.innerHTML;
                                const separator = current.indexOf(':');
                                if (separator !== -1) {
                                    labelContainer.innerHTML = config.label + current.substring(separator);
                                }
                            }
                        }
                    });
                })();
            </script>
            <script src="js/sweetalert.min.js"></script>
            <script src="js/alertsregistro.js?version=1"></script>
            <!--            <script src="https://framework-gb.cdn.gob.mx/gobmx.js"></script>-->
        </div>
    </div>
<?php

echo $OUTPUT->footer();
