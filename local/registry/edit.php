<?php

//require ('../externals/conexion.php');
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->libdir.'/moodlelib.php');

global $CFG;

$idcurso = $_POST['idcurso'];
//$idcurso = 35;

$roleidprofesor = get_config('local_registry','rolteacher');    //id del rol de Profesor a impartir los cursos
$limitedegrupo = get_config('local_registry','limitegroup');    //límite de alumnos en los grupos
$rolstudent = get_config('local_registry','rolstudent');        //rol de estudiantes en los cursos
$nombregroup = get_config('local_registry','namegroupespera');    //nombre del grupo al superar el límite de los grupos
$permitegrupodeespera = get_config('local_registry','haygroupespera');    //nombre del grupo al superar el límite de los grupos

$idcurso = 19;
$limitedegrupo =21;
$rolstudent = 9;
$useridincial = 74;
$roleidprofesor = 10;
echo $roleidprofesor."<br>";
echo $limitedegrupo."<br>";
echo $rolstudent."<br>";
//echo $nombregroup."<br>";
echo $idcurso."<br>";
$consultamoodle = $DB->get_records('groups',array('courseid'=>$idcurso)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$band =0;
//$consultamoodle = $DB->get_records('course',array('category'=>$categoryid),'','id,fullname'); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$html= "<option value=''>Seleccionar</option>";
//groups_get_members_by_role();

foreach ($consultamoodle as $data) {
    echo "Id del grupo ".$data->id."<br>";

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
        //echo "id del profesor activo".$dato->id."<br>";
        $query2 ="SELEct u.id FROM {$CFG->prefix}role_assignments AS ra
         JOIN {$CFG->prefix}context AS ctx ON ra.contextid = ctx.id
         JOIN {$CFG->prefix}user AS u ON u.id = ra.userid
         JOIN {$CFG->prefix}groups_members AS gm ON gm.userid = u.id
         JOIN {$CFG->prefix}user_enrolments AS enr ON gm.userid = enr.userid AND enr.status = 0
         WHERE ra.roleid = $rolstudent
         AND ctx.instanceid = $idcurso
         AND gm.groupid = $data->id";

        $total = count(groups_get_members($data->id, 'u.id'));
        $consultaalumnos = $DB->get_records_sql($query2);

        foreach ($consultaalumnos as $numalumnos){
            $consultacupo++;
        }
         echo "Nombre del grupo".$data->{"name"}."<br>";
         echo "Numero de alumnos en el grupo".$consultacupo."<br>";
        echo"Cupo".$consultacupo."<br>";
        echo"limite".$limitedegrupo."<br>";
        echo "id del grupo: ".$data->id."nombre grupo: ".$data->name."id del maestro".$dato->id."numero deestudiantes en ese curso".$consultacupo.'<br>';
        if($consultacupo < $limitedegrupo){
            if($band == 1){
                echo "este es el grupo ".$data->{"name"}."que se abrira<br>";
                groups_add_member($data->{"id"},$useridincial);
//                $consultacupo = 1;
            }
            if($consultacupo > 0) {
                echo "Se abrio el nuevo grupo ".$data->{"name"};
                $band = 0;
                $consultanombre = $DB->get_record("user", array('id' => $dato->id), 'firstname,lastname');
                $nombrecompleto = "Monitor (a): " . $consultanombre->firstname . " " . $consultanombre->lastname;
                //echo "agregangendo elemento..";
                //$html .= "  <optgroup label='" . $nombrecompleto . "'>" . "<option value='" . $data->{"id"} . "'>" . $data->{"name"} . "</option>" . "</optgroup>";
                $html .=  "<option value='" . $data->{"id"} . "'>" . $data->{"name"} . "</option>";
            }
        }if($consultacupo >= $limitedegrupo){
            echo "El grupo".$data->{"name"}." está lleno<br>";
            $band =1 ;
            //Agregar al usuario al segundo grupo vacio que encuentre.
            $nohaycupo = true;
        }
        echo "Nombre del grupo: ".$data->name."Numero de alumnos:".$consultacupo."<br>";
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

?>
