<?php
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("location: index.php");
    die();
}
require_once(__DIR__.'/../../config.php');

$remotedbhost = get_config('local_registry','dbhost');
$remotedbname = get_config('local_registry','dbname');
$remotedbuser = get_config('local_registry','dbuser');
$remotedbpass = get_config('local_registry', 'dbpass');
$remoteport = get_config('local_registry', 'dbport');
$remotedbtable = get_config('local_registry','dbtable');  //NOMBRE DE LA TABLA

$urlsesion = $CFG->wwwroot.'/login/index.php';
// Create connection
$conn = new mysqli($remotedbhost, $remotedbuser, $remotedbpass,$remotedbname,$remoteport);

// Check connection
if ($conn->connect_error) {
    error_reporting(E_ERROR);
    $message = "Connection failed: " . $conn->connect_error;
    redirect('index.php', $message .\core\notification::error("Informar al administrador del sitio") , null, \core\output\notification::NOTIFY_ERROR);


}
//$inserccion  = $_POST['consulta'];
//Verificar que el usurio está registrado
$curp = $_POST['curp'];
$correo = $_POST['emailins'];
$username = $_POST['usernameins'];
$alias = $_POST['aliasins'];
$nombres = $_POST['nombreins'];
$apellidop = $_POST['p_apellidoins'];
$apellidos = $_POST['s_apellidoins'];
$genero = $_POST['generoins'];
$fechanaci = $_POST['date_nacimientoins'];
$estado = $_POST['e_nacimientoins'];
$municipio = $_POST['municipioins'];
$ocupacion = $_POST['ocupacionins'];
$pais = $_POST['id_countryins'];
$estado_residen = $_POST['e_residenciains'];


$inserccion = "INSERT INTO $remotedbtable (id, nombre, apellido_p, apellido_m, curp,rol, username, password,correo,genero,date_nacimiento,estado,municipio,ocupacion,pais,estado_residencia) VALUES (NULL ,'$nombres','$apellidop','$apellidos','$curp',4,'$username','$alias','$correo','$genero','$fechanaci','$estado','$municipio','$ocupacion','$pais','$estado_residen')";



//echo $alias;
//die();
$verificaexiste="SELECT curp FROM $remotedbtable where curp = '$curp'";

$datos = mysqli_query($conn,$verificaexiste)or die(
redirect('index.php', $message .\core\notification::error("Informar al administrador del sitio") , null, \core\output\notification::NOTIFY_ERROR)//SI NO SE EJECUTA LA CONSULTA SE RETORNARA A LA PANTALLA INICAL
);
//EXTRAE LA CURP ENCONTRADA EN LA BASE DE DATOS EXTERNA
$registro = ''; //INICIA VACIO
$existeregistro = 0;

while($row = mysqli_fetch_array($datos)) {
    $registro = $row[0];
    //VERIFICA QUE RETORNE UN VALOR
    if($registro != ''){
        //EXISTE UN USUARIO CON LA CURP
        $existeregistro  = 1; //encontro al menos un dato en la bd externa
    }else{
        $existeregistro =0;
    }
}
echo $existeregistro;
echo $inserccion;
if($existeregistro == 0) {
    if ($inserccion != '') {
        if (mysqli_query($conn, $inserccion)) {
            $message = "Usuario agregado con éxito!";
            redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_SUCCESS);

        } else {
            //$message = "Error: " . $inserccion . "<br>" . mysqli_error($conn);
            $message = "Error al agregar el usuario " . "<br>" . mysqli_error($conn) . "<br>" . "Reportar al administrador";
            redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}
else {
    $message = "El usuario ya existe en el registro!";
    //retornar los datos para iniciar sesión
    //Crer cons que retorne los valores
    redirect($urlsesion, $message, null, \core\output\notification::NOTIFY_SUCCESS);


}
?>


