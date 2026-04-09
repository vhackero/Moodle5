<?php
//if ($_SERVER['REQUEST_METHOD'] != "POST") {
//    header("location: /index.php");
//    die();
//}
//require ('../externals/conexion.php');
require_once(__DIR__.'/../../../config.php');
global $DB;
$curp = $_POST['curp'];
//$curp = "aosl970306htllss02";
$idcategoria = 2; //Categoría donde se registran los usuarios que estuvieron en la pasada; ejemplo: si estuvo en la 1 la nueva sería 2;
$limitedecursos = 2; //Limite de cursos con la misma lengua
$existeEnPeridoActual = 0;
$nameListaEspera = get_config("local_registry","namegroupespera");

//Verifica en que cursos esta ese usuario
$consultamoodleuser = $DB->get_record('user_info_data',array('data'=>$curp)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$iduser = $consultamoodleuser->userid;
$consultamoodle = $DB->get_record('user',array('id'=>$iduser)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
$email = $consultamoodle->email;
$data = enrol_get_all_users_courses($iduser);

//print_object($data);
$consultagroup = $DB->get_records('groups_members',array('userid'=>$iduser)); //CONSULTA DE LA CURP EN LA BD DE MOODLE

$pertencelista  =0;
$contador = 0;

$contadorEspaniol = 0;
$contadorIngles = 0;
$contadorMaya = 0;
$contadorNahualt = 0;
$contadorOtomi = 0;
$contadorMixteco = 0;
$contadorZapoteco = 0;

foreach ($data as $curso){
    echo $curso->id.$curso->fullname."<br>";
    $data2 =  groups_get_user_groups($curso->id,$iduser);
//    print_object($data2);;
    //valida si ya uso la herramienta para impedir que se matricule a otro curso
    $queryValidate = $DB->get_record('course',array('id'=>$curso->id,'category'=>$idcategoria));
    if($queryValidate){
        $existeEnPeridoActual = 1;
    }
    foreach ($data2 as $grupo){
        //Valida si el usuario esta en los cursos que se especifican y hace un conteo si ya estuvo en mas de uno con este nombre
        $namegroup = $DB->get_record('groups',array('id'=>$grupo[0])); //CONSULTA DE LA CURP EN LA BD DE MOODLE
        if(strstr($namegroup->{"name"},$nameListaEspera)){
            echo "Esta en espera";
            $pertencelista = 1;
            $curso = $namegroup->{"courseid"};
        }else{
            if($curso->fullname == "Español") {
                $contadorEspaniol++;
                $contador++;
            }
            if($curso->fullname == "Inglés") {
                $contadorIngles++;
                $contador++;
            }
            if($curso->fullname == "Maya") {
                $contadorMaya++;
                $contador++;
            }
            if($curso->fullname == "Náhualt") {
                $contadorNahualt++;
                $contador++;
            }
            if($curso->fullname == "Otomí") {
                $contadorOtomi++;
                $contador++;
            }
            if($curso->fullname == "Mixteco") {
                $contadorMixteco++;
                $contador++;
            }
            if($curso->fullname == "Zaporteco") {
                $contadorZapoteco++;
                $contador++;
            }
            if($curso->fullname == "Curso A") {
                $contadorZapoteco++;
                $contador++;
            }
        }
    }

}
//SI el contador es mayor a 3
$consultamoodleuser = $DB->get_records('course',array('category'=>$idcategoria)); //CONSULTA DE LA CURP EN LA BD DE MOODLE
if($contador>0){
    $html = "<option value='"."'>" ."Selecciona una opción" . "</option>";
}
foreach ($consultamoodleuser as $data)
{

    if($contador > 0) {
    //Una vez que el contador es por lo menos mayor unos que quiere decir que esta registrado en al menos una lengua, y si el contador supera o es igual al limite para ese curso
        // no debe aparecer en el menu.
        if ($contador >= $limitedecursos) {
            if ($data->{"fullname"} == "Español") {
                if ($contadorEspaniol >= $limitedecursos) {
                    //echo "Existe un curso con ese nombre el cual no debe aparecer";
                } else {
                    $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                }
            }
            if ($data->{"fullname"} == "Inglés") {
                if ($contadorIngles >= $limitedecursos) {
                    //echo "Existe un curso con ese nombre el cual no debe aparecer";
                } else {
                    $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                }
            }
            if ($data->{"fullname"} == "Maya") {
                if ($contadorMaya >= $limitedecursos) {
                    //echo "Existe un curso con ese nombre el cual no debe aparecer";
                } else {
                    $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                }
            }
            if ($data->{"fullname"} == "Náhualt") {
                if ($contadorNahualt >= $limitedecursos) {
                    //echo "Existe un curso con ese nombre el cual no debe aparecer";
                } else {
                    $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                }
            }
            if ($data->{"fullname"} == "Otomí") {
                if ($contadorOtomi >= $limitedecursos) {
                    //echo "Existe un curso con ese nombre el cual no debe aparecer";
                } else {
                    $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                }
            }
            if ($data->{"fullname"} == "Mixteco") {
                if ($contadorMixteco >= $limitedecursos) {
                    //echo "Existe un curso con ese nombre el cual no debe aparecer";
                } else {
                    $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                }
            }
            if ($data->{"fullname"} == "Zapoteco") {
                if ($contadorZapoteco >= $limitedecursos) {
                    //echo "Existe un curso con ese nombre el cual no debe aparecer";
                } else {
                    $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
                }
            }
        } else {
            $html .= "<option value='" . $data->{"id"} . "'>" . $data->{"fullname"} . "</option>";
        }
    }
}
echo $html;
echo "|".$iduser."|".$email."|".$pertencelista."|".$existeEnPeridoActual;







