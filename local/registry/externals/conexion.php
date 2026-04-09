<?php
require_once(__DIR__.'/../../../config.php');

//CONEXIÓN A BD PARA EXTRAER LOS DICCIONARIOS DE PAISES, ESTADOS, MUNICIPIOS, ETC
        $host =get_config('local_registry','dbcatalogoshost');
        $dbname =get_config('local_registry','dbcatalogos');
        $user =get_config('local_registry','dbcatalogosuser');
        $pass =get_config('local_registry','dbcatalogospass');
//        $mysqli = new mysqli("172.18.25.11","temporal","Pwd-pru3b4s","sepomex");  //servidor, usuario de base de datos, contraseña del usuario, nombre de base de datos
        $mysqli = new mysqli($host,$user,$pass,$dbname);  //servidor, usuario de base de datos, contraseña del usuario, nombre de base de datos
        mysqli_set_charset($mysqli, "utf8"); //PARA ACEPTAR ,ñ etc.
        if(mysqli_connect_errno()){
                    echo 'Conexion Fallida : ', mysqli_connect_error();
                    exit();
            }

