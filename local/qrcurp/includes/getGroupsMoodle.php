<?php

//require ('../externals/conexion.php');
require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->libdir.'/moodlelib.php');

global $CFG;

$idcurso = $_POST['idcurso'];
$roleidprofesor = get_config('local_qrcurp','rolteacher');    //id del rol de Profesor a impartir los cursos
$limitedegrupo = get_config('local_qrcurp','limitegroup');    //límite de alumnos en los grupos
$rolstudent = get_config('local_qrcurp','rolstudent');        //rol de estudiantes en los cursos
$nombregroup = get_config('local_qrcurp','namegroupespera');    //nombre del grupo al superar el límite de los grupos
$permitegrupodeespera = get_config('local_qrcurp','haygroupespera');    //nombre del grupo al superar el límite de los grupos
$onegroupattime = get_config('local_qrcurp','onegroupattime');
$groupsalredycreated = get_config('local_qrcurp','groupsalredycreated');

$consultamoodle = $DB->get_records('groups',array('courseid'=>$idcurso)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$band =0;
//$consultamoodle = $DB->get_records('course',array('category'=>$categoryid),'','id,fullname'); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$html= "<option value=''>Seleccionar</option>";
//groups_get_members_by_role();

if($onegroupattime == 1){
    $consultamoodle = $DB->get_records('groups', array('courseid' => $idcurso), 'name ASC');
    $html= "<option value=''>Seleccionar</option>";
    $nohaycupo = false;
    $selectedgroup = null;

    foreach ($consultamoodle as $data) {
        if ($permitegrupodeespera == 1 && trim((string)$nombregroup) !== '' && (string)$data->name === (string)$nombregroup) {
            continue;
        }

        $groupid = (int)$data->id;
        $groupname = (string)$data->name;

        // Si no se configura límite, se toma el primer grupo disponible.
        if (empty($limitedegrupo) || (int)$limitedegrupo <= 0) {
            $selectedgroup = ['id' => $groupid, 'name' => $groupname];
            break;
        }

        $sqlstudents = "SELECT COUNT(DISTINCT u.id)
                          FROM {$CFG->prefix}role_assignments ra
                          JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                          JOIN {$CFG->prefix}user u ON u.id = ra.userid
                          JOIN {$CFG->prefix}groups_members gm ON gm.userid = u.id
                         WHERE ra.roleid = :rolstudent
                           AND ctx.instanceid = :courseid
                           AND gm.groupid = :groupid";
        $params = [
            'rolstudent' => (int)$rolstudent,
            'courseid' => (int)$idcurso,
            'groupid' => $groupid,
        ];
        $studentcount = (int)$DB->count_records_sql($sqlstudents, $params);

        // Mostrar un grupo a la vez: el primer grupo con cupo.
        if ($studentcount < (int)$limitedegrupo) {
            $selectedgroup = ['id' => $groupid, 'name' => $groupname];
            break;
        }

        $nohaycupo = true;
    }

    if (!empty($selectedgroup)) {
        $html .= "<option value='" . (int)$selectedgroup['id'] . "'>" . format_string($selectedgroup['name']) . "</option>";
    }

    if($permitegrupodeespera == 1) {
        $idespera = $DB->get_record("groups", array("name" => $nombregroup,'courseid'=>$idcurso), 'id,name');
        if (!empty($idespera->id)) {
            $html .= "<optgroup label='Sin Horarios'><option value='" . (int)$idespera->id . "'>" . format_string($idespera->name) . "</option></optgroup>";
        }
    }

    echo $html;
}
if($groupsalredycreated == 1){

    $consultamoodle = $DB->get_records('groups', array('courseid' => $idcurso), 'name ASC');
    $html= "<option value=''>Seleccionar</option>";
    $nohaycupo = false;

    foreach ($consultamoodle as $data) {
        if ($permitegrupodeespera == 1 && trim((string)$nombregroup) !== '' && (string)$data->name === (string)$nombregroup) {
            continue;
        }

        $groupid = (int)$data->id;
        $groupname = (string)$data->name;

        // Si no se configura límite, mostrar todos los grupos existentes del curso.
        if (empty($limitedegrupo) || (int)$limitedegrupo <= 0) {
            $html .= "<option value='" . $groupid . "'>" . format_string($groupname) . "</option>";
            continue;
        }

        // Contar usuarios con rol de estudiante dentro del grupo y del curso seleccionado.
        $sqlstudents = "SELECT COUNT(DISTINCT u.id)
                          FROM {$CFG->prefix}role_assignments ra
                          JOIN {$CFG->prefix}context ctx ON ra.contextid = ctx.id
                          JOIN {$CFG->prefix}user u ON u.id = ra.userid
                          JOIN {$CFG->prefix}groups_members gm ON gm.userid = u.id
                         WHERE ra.roleid = :rolstudent
                           AND ctx.instanceid = :courseid
                           AND gm.groupid = :groupid";
        $params = [
            'rolstudent' => (int)$rolstudent,
            'courseid' => (int)$idcurso,
            'groupid' => $groupid,
        ];

        $studentcount = (int)$DB->count_records_sql($sqlstudents, $params);
        if ($studentcount < (int)$limitedegrupo) {
            $html .= "<option value='" . $groupid . "'>" . format_string($groupname) . "</option>";
        } else {
            $nohaycupo = true;
        }
    }

    if($permitegrupodeespera == 1 && $nohaycupo) {
        $idespera = $DB->get_record("groups", array("name" => $nombregroup, 'courseid' => $idcurso), 'id,name');
        if (!empty($idespera->id)) {
            $html .= "<optgroup label='Sin Horarios'><option value='" . (int)$idespera->id . "'>" . format_string($idespera->name) . "</option></optgroup>";
        }
    }

    echo $html;
}



?>
