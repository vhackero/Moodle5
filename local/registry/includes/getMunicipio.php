<?php

	require ('../externals/conexion.php');

	$id_codigo = $_POST['id_postal'];
//		$id_codigo = 90401;
	//	 $id_estado = 1;   	//para comprobar que esté ejecutando la consulta
	$queryM = "SELECT DISTINCT mun.description FROM municipalities as mun
			INNER JOIN suburbs as sub ON sub.id_municipality = mun.id
			WHERE sub.cp  = $id_codigo;
			";

	$resultadoM = $mysqli->query($queryM);

	//$html= "<option value='0'>Seleccionar Municipio</option>";

	while($rowM = $resultadoM->fetch_assoc())
	{
		$html.= "<option value='".mb_strtoupper($rowM['description'],"utf-8")."'>".mb_strtoupper($rowM['description'],"utf-8")."</option>";
	}

	echo $html;
?>		