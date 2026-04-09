<?php
/**
 * @package local_registry
 * @author  Luis_Felipe (Modificado para guardar ID de Rol y Nombre de Rol por separado)
 */

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("location: index.php");
    die();
}

require_once(__DIR__.'/../../config.php');
global $CFG, $DB, $PAGE, $SESSION;
require_once($CFG->dirroot.'/group/lib.php');
require_once(__DIR__.'/lib.php');

$PAGE->set_context(\context_system::instance());

$username = strtolower($_POST['username']);
$correo = $_POST['email'];
$alias = !empty($_POST['session_alias']) ? $_POST['session_alias'] : $_POST['pass'];
$idcourse = $_POST['idcourse'];
$idcreategroup = $_POST['typegrouping'];
$typeuser = $_POST['typeuser'];

/**
 * Lista de campos adicionales.
 * Formato: 'nombre_corto_en_moodle' => 'clave_en_formulario_post'
 */
$campos_post = [
    'cp'                => 'cp',
    'estado_residencia' => 'estado_residencia',
    'estado_nacimiento' => 'estado_nacimiento',
    'fecha_nacimiento'  => 'fecha_nacimiento',
    'ocupacion'         => 'ocupacion',
    'curp'              => 'curp',
    'genero'            => 'genero',
    'edad'              => 'edad',
    'matricula'         => 'matricula',
    'rol'               => 'rol',      // Guarda el ID del rol (Nombre corto en Moodle: rol)
    'rol_name'          => 'rol_name', // Guarda el Nombre del rol (Nombre corto en Moodle: rol_name)
    'id_del_curso'      => 'courseid',
    'id_grupo'          => 'grouping',
    'curp_valida'       => 'curpvalida'
];

if($typeuser == 1){
    $confirmemail = get_config('local_registry','confirmemailexternos');
} else {
    $confirmemail = get_config('local_registry','confirmemailgeneral');
}

$user_exists = $DB->get_record('user', array('username' => $username));

// --- LÓGICA PARA GUARDAR DATOS DE PERFIL (Función reutilizable) ---
function guardar_datos_perfil_seguro($userid, $campos) {
    global $DB;
    foreach ($campos as $shortname => $post_key) {
        if (isset($_POST[$post_key])) {
            $value = $_POST[$post_key];
            // Buscamos si el campo existe en la definición de campos de perfil de Moodle
            $field = $DB->get_record('user_info_field', array('shortname' => $shortname));
            if ($field) {
                $data_record = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => $field->id));
                if ($data_record) {
                    $DB->update_record('user_info_data', (object)['id' => $data_record->id, 'data' => $value]);
                } else {
                    $DB->insert_record('user_info_data', (object)['userid' => $userid, 'fieldid' => $field->id, 'data' => $value]);
                }
            }
        }
    }
}

if ($user_exists) {
    // Guardar o actualizar datos de perfil para usuario existente
    guardar_datos_perfil_seguro($user_exists->id, $campos_post);

    local_registry_set_course_group($user_exists->id, $idcourse, $idcreategroup, $alias);
    redirect($CFG->wwwroot . "/course/view.php?id=$idcourse", "Te has matriculado al Taller Manejo del Aula UnADM. Revisa tu correo electrónico para ver tus datos de acceso.");
    die();
}

// --- CREACIÓN DE USUARIO NUEVO ---
$record = new stdClass();
$record->username = $username;
$record->password = hash_internal_user_password($alias);
$record->idnumber = $alias;
$record->firstname = strtoupper($_POST['nombre']);
$record->lastname = strtoupper($_POST['p_apellido'] . ' ' . $_POST['s_apellido']);
$record->email = $correo;
$record->mnethostid = 1;
$record->lang = "es";
$record->timecreated = time();

if($confirmemail == 1) {
    $record->confirmed = 0;
    $record->secret = random_string(15);
    $record->institution = $idcourse;
    $record->department = $idcreategroup;
    $record->address = $_POST['namecategory'];
} else {
    $record->confirmed = 1;
}

$iduserinsert = $DB->insert_record('user', $record);

if ($iduserinsert) {
    // Guardar datos de perfil para el nuevo usuario
    guardar_datos_perfil_seguro($iduserinsert, $campos_post);

    if($confirmemail == 1) {
        local_registry_envia_correo($iduserinsert, 4, $alias, $idcourse, $idcreategroup, $_POST['namecategory']);
        redirect($CFG->wwwroot.'/login/index.php', "Usuario registrado. Revisa tu correo electrónico para confirmar tu cuenta.");
    } else {
        local_registry_set_course_group($iduserinsert, $idcourse, $idcreategroup, $alias);
        redirect("$CFG->wwwroot/course/view.php?id=$idcourse", "Registro realizado con éxito.");
    }
}