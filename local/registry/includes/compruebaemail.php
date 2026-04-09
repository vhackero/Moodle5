<?php

	//require ('../externals/conexion.php');
	require_once(__DIR__.'/../../../config.php');

	$correo = $_POST['email'];
	//$correo = "felipealcocersosa@gmail.com";

		$consultamoodle = array_keys($DB->get_records('user',array('email'=>$correo),'','email')); //CONSULTA DE LA CURP EN LA BD DE MOODLE
		$estaregis = $consultamoodle[0]; //SE EXTRAE EL USUARIO CON ESA CURP
		if($estaregis != ''){
			echo 1;
		}else{
			echo 0;
		}



?>		