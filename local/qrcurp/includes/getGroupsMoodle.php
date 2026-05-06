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
    foreach ($consultamoodle as $data) {


        $query = "SELEct distinct(u.id) FROM {$CFG->prefix}role_assignments AS ra
         JOIN {$CFG->prefix}context AS ctx ON ra.contextid = ctx.id
         JOIN {$CFG->prefix}user AS u ON u.id = ra.userid
         JOIN {$CFG->prefix}groups_members AS gm ON gm.userid = u.id
         JOIN {$CFG->prefix}user_enrolments AS enr ON gm.userid = enr.userid AND enr.status = 0
         WHERE ra.roleid = $roleidprofesor
         AND ctx.instanceid = $idcurso
         AND gm.groupid = $data->id";
        $consultas = $DB->get_records_sql($query);
        $consultacupo = 0 ;
        foreach ($consultas as $dato) {
//            echo "id del profesor activo".$dato->id."<br>";
            $query2 ="SELEct u.id FROM {$CFG->prefix}role_assignments AS ra
         JOIN {$CFG->prefix}context AS ctx ON ra.contextid = ctx.id
         JOIN {$CFG->prefix}user AS u ON u.id = ra.userid
         JOIN {$CFG->prefix}groups_members AS gm ON gm.userid = u.id
         JOIN {$CFG->prefix}user_enrolments AS enr ON gm.userid = enr.userid AND enr.status = 0
         WHERE ra.roleid = $rolstudent
         AND ctx.instanceid = $idcurso
         AND gm.groupid = $data->id";

            $total = count(groups_get_members($data->id, 'u.id'));
//        echo "Id del curso: ".$data->id."<br>";
            $consultaalumnos = $DB->get_records_sql($query2);
            if(sizeof($consultaalumnos) == 0){
                $consultacupo =1;
                if($consultacupo > 0) {
                    $band = 0;
                    $consultanombre = $DB->get_record("user", array('id' => $dato->id), 'firstname,lastname');
//                    $nombrecompleto = "Monitor (a): " . $consultanombre->firstname . " " . $consultanombre->lastname;
                    $html .=  "<option value='" . $data->{"id"} . "'>" . $data->{"name"} . "</option>";
                    break;
                }
            }
            foreach ($consultaalumnos as $numalumnos){
                $consultacupo++;
            }
//             echo "Nombre del grupo".$data->{"name"}."<br>";
//             echo "Numero de alumnos en el grupo".$consultacupo."<br>";
//            echo"Cupo".$consultacupo."<br>";
//            echo"limite".$limitedegrupo."<br>";
//            echo "id del grupo: ".$data->id."nombre grupo: ".$data->name."id del maestro".$dato->id."numero deestudiantes en ese curso".$consultacupo.'<br>';
            if($consultacupo < $limitedegrupo){
                if($band == 1 ){
                    $consultacupo = 1;
                }
                if($consultacupo > 0) {
                    $band = 0;
                    $consultanombre = $DB->get_record("user", array('id' => $dato->id), 'firstname,lastname');
//                    $nombrecompleto = "Monitor (a): " . $consultanombre->firstname . " " . $consultanombre->lastname;
                    $html .=  "<option value='" . $data->{"id"} . "'>" . $data->{"name"} . "</option>";
                    break;
                }
            }if($consultacupo >= $limitedegrupo){
                //echo "El grupo".$data->{"name"}." está lleno<br>";
                $band =1 ;
                //Agregar al usuario al segundo grupo vacio que encuentre.
                $nohaycupo = true;
            }
//        echo "Nombre del grupo: ".$data->name."Numero de alumnos:".$consultacupo."<br>";
        }
    }

    if($permitegrupodeespera == 1) {
        if ($nohaycupo) {
            //echo "NO hay espacio";
            //la data-> = $existegruponame
            $idespera = $DB->get_record("groups", array("name" => $nombregroup,'courseid'=>$idcurso), 'id,name');
            //  echo "agregando el grupo de espera";
            $html .= "  <optgroup label='" . "Sin Horarios" . "'>" . "<option value='" . $idespera->id . "'>" . $idespera->name . "</option>" . "</optgroup>";

        }
        echo $html;
    }else{
        echo $html;
    }
}
if($groupsalredycreated == 1){

    $consultamoodle = $DB->get_records('groups', array('courseid' => $idcurso), 'name ASC');
    $html= "<option value=''>Seleccionar</option>";
    $nohaycupo = false;

    foreach ($consultamoodle as $data) {
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
