<?php

//require ('../externals/conexion.php');
require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->libdir.'/moodlelib.php');

global $CFG;

$idcurso = $_POST['idcurso'];
$roleidprofesor = get_config('local_qrcurp','rolteacher');    //id del rol de Profesor a impartir los cursos

//$idcurso = 10;
$limitedegrupo = 30; //límite que tendran los grupos de comunidade de la práctica de la lengua
$limitedegrupoculturas = 40; //límite que tendran los grupos de comunidades de práctica de la cultura
//LIMITES PARA CADA UNO DE LOS GRUPOS.
$rolstudent = 5; //limite que tendran los grupos agrupados
$nombregroup = 'Lista de espera '; //Nombre del grupo al que se agregaran

$consultamoodle = $DB->get_records('groups',array('courseid'=>$idcurso)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$consultanamecourse = $DB->get_record('course',array('id'=>$idcurso)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$namecourse = $consultanamecourse->fullname;
//$consultamoodle = $DB->get_records('course',array('category'=>$categoryid),'','id,fullname'); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$html= "<option value=''>Seleccionar</option>";
//groups_get_members_by_role();
foreach ($consultamoodle as $data) {
    $limitedegrupo = 20;

    $query = "SELEct distinct(u.id) FROM {$CFG->prefix}role_assignments AS ra
         JOIN {$CFG->prefix}context AS ctx ON ra.contextid = ctx.id
         JOIN {$CFG->prefix}user AS u ON u.id = ra.userid
         JOIN {$CFG->prefix}groups_members AS gm ON gm.userid = u.id
         JOIN {$CFG->prefix}user_enrolments AS enr ON gm.userid = enr.userid AND enr.status = 0
         WHERE ra.roleid = $roleidprofesor
         AND ctx.instanceid = $idcurso
         AND gm.groupid = $data->id";
    $consultas = $DB->get_records_sql($query);

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
        $consultacupo = 0;

        foreach ($consultaalumnos as $numalumnos){
            $consultacupo++;
        }
        echo"Cupo".$consultacupo."<br>";
//        echo"limite".$limitedegrupo."<br>";
//        echo "id del grupo: ".$data->id."nombre grupo: ".$data->name."id del maestro".$dato->id."numero deestudiantes en ese curso".$consultacupo.'<br>';
        echo "nombre del grupo".$data->{"name"}."<br>";
        if(strstr($data->{"name"},"Comunidad de práctica de la lengua 18:30 a 20:00 lunes")||strstr($data->{"name"},"Comunidad de práctica de la lengua 8:30 a 10:00 viernes") ){
            $limitedegrupo = 39;
        }
        if(strstr($data->{"name"},"Comunidad de práctica de la lengua 8:30 a 10:00 martes")||strstr($data->{"name"},"Comunidad de práctica de la lengua 12:00 a 13:30 sábado") ){
            $limitedegrupo = 40;
        }
        if(strstr($data->{"name"},"Comunidad de práctica de la lengua 18:30 a 20:00 martes") || strstr($data->{"name"},"Comunidad de práctica de la lengua 17:00 a 18:30 miércoles") || strstr($data->{"name"},"Comunidad de práctica de la lengua 14:30 a 16:00 jueves") || strstr($data->{"name"},"Comunidad de práctica de la lengua 10:00 a 11:30 sábado")|| strstr($data->{"name"},"Comunidad de práctica de la lengua 11:30 a 13:00 sábado") ){
            $limitedegrupo = 43;
        }
        if(strstr($data->{"name"},"Comunidad de práctica de la lengua 17:00 a 18:30 jueves")){
            $limitedegrupo = 39;
        }
        if(strstr($data->{"name"},"Comunidad de práctica de la lengua 13:30 a 15:00 sábado") || strstr($data->{"name"},"Comunidad de práctica de la lengua 14:30 a 16:00 lunes")){
            $limitedegrupo = 41;
        }
        if(strstr($data->{"name"},"Comunidad de práctica de la lengua 14:30 a 16:00 miércoles")){
            $limitedegrupo = 42;
        }
        if($data->{"id"} == 57 || strstr($data->{"name"},"Comunidad de práctica de la lengua 17:30 a 19:00 jueves")){
            $limitedegrupo = 45;
        }
        if(strstr($data->{"name"},"Comunidad de práctica de la lengua 18:30 a 20:00 viernes")){
            $limitedegrupo = 46;
        }

        if(strstr($data->{"name"},"lengua")){
//            echo "Encontre la palabra<br>";
            if($consultacupo < $limitedegrupo){
                $consultanombre =$DB->get_record("user",array('id'=>$dato->id),'firstname,lastname');
                $nombrecompleto = "Mediador (a): ".$consultanombre->firstname." ".$consultanombre->lastname;
                //echo "agregangendo elemento..";
                $html.= "  <optgroup label='" . $nombrecompleto . "'>" . "<option value='" . $data->{"id"} . "'>" . $data->{"name"} . "</option>" . "</optgroup>";
            }if($consultacupo == $limitedegrupo){
                $nohaycupo = true;
            }
        }
        if(strstr($data->{"name"},"cultura")) {
//            echo "El grupo no contiene la palabra<br>";
            if($consultacupo < $limitedegrupoculturas){
                $consultanombre =$DB->get_record("user",array('id'=>$dato->id),'firstname,lastname');
                $nombrecompleto = "Mediador (a): ".$consultanombre->firstname." ".$consultanombre->lastname;
                //echo "agregangendo elemento..";
                $html.= "  <optgroup label='" . $nombrecompleto . "'>" . "<option value='" . $data->{"id"} . "'>" . $data->{"name"} . "</option>" . "</optgroup>";
            }if($consultacupo == $limitedegrupoculturas){
                $nohaycupo = false;
            }
        }
//        else{
//            if ($consultacupo < $limitedegrupo) {
//                $consultanombre = $DB->get_record("user", array('id' => $dato->id), 'firstname,lastname');
//                $nombrecompleto = "Mediador (a): " . $consultanombre->firstname . " " . $consultanombre->lastname;
//                //echo "agregangendo elemento..";
//                $html .= "  <optgroup label='" . $nombrecompleto . "'>" . "<option value='" . $data->{"id"} . "'>" . $data->{"name"} . "</option>" . "</optgroup>";
//            }
//            if ($consultacupo == $limitedegrupo) {
//                $nohaycupo = false;
//            }
//        }


    }

}
//if($nohaycupo){
//    //echo "NO hay espacio";
//    //la data-> = $existegruponame
//    $idespera = $DB->get_record("groups",array("name"=>$nombregroup.$namecourse),'id,name');
//
//    //  echo "agregando el grupo de espera";
//    $html .= "  <optgroup label='" . "Sin Horarios" . "'>"."<option value='" . $idespera->id . "'>" . $idespera->name . "</option>". "</optgroup>";
//
//}
echo $html;


?>
