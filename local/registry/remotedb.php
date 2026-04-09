<?php
require_once(__DIR__.'/../../config.php');
//DATOS TOMADOS DE LA CONFIGURACIÓN DEL PLUGGIN
$remotedbhost = get_config('local_registry','dbhost');    //HOST
$remotedbname = get_config('local_registry','dbname');    //NOMBRE DE LA BD EXTERNA
$remotedbtable = get_config('local_registry','dbtable');  //NOMBRE DE LA TABLA
$remotedbuser = get_config('local_registry','dbuser');    //USUARIO DE LA DB EXTERNA
$remotedbpass = get_config('local_registry', 'dbpass');   //CONTRASEÑA DE LA DB EXTERNA
$remoteport = get_config('local_registry', 'dbport');     //PUERTO DE LA DB EXTERNA
$remoteinsertdb =get_config('local_registry','dbinsert'); //PARA SABER SI SE AGREGARAN LOS DATOS A LA BD EXTERNA

// CREANDO CONEXION y LA VARIABLE GLOBAL PARA NUESTRA CONEXIÓN EXTERNA
global $DBEXTERNAL ;

$DBEXTERNAL = new mysqli($remotedbhost, $remotedbuser, $remotedbpass,$remotedbname,$remoteport); //CONEXION
mysqli_set_charset($DBEXTERNAL, "utf8"); //PARA ACEPTAR ,ñ etc.
$DBEXTERNAL->dbname = $remotedbname;    //NOMBRE DE LA BD
$DBEXTERNAL->dbtable = $remotedbtable;  //NOMBRE DE LA TABLA
$DBEXTERNAL->dbinsert = $remoteinsertdb;  //OPCIÓN PARA SABER SI SE REGISTRARÁ EN LA BD EXTERNA

//CREANDO LA CONEXIÓN
$conn = new mysqli($remotedbhost, $remotedbuser, $remotedbpass,$remotedbname,$remoteport);

//VERIFICANDO LA CONEXIÓN A LA BD
if ($DBEXTERNAL->connect_error) {
    error_reporting(E_ERROR);
    $message = "Conexión fallida, revisar la configuración de conexion a la base de datos que se intenta comunicar. " . $conn->connect_error;
    redirect('index.php', $message .\core\notification::error("Informar al administrador del sitio") , null, \core\output\notification::NOTIFY_ERROR);
}