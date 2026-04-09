<?php

	require ('../externals/conexion.php');

	$id_codigo = $_POST['id_postal'];
//	$id_codigo = 90401;
	//$id_estado = 1;   	//para comprobar que esté ejecutando la consulta

	$queryS = "SELECT description
					FROM states 
					WHERE id > 0
					GROUP BY description";
	$resultadoS = $mysqli->query($queryS);

	$html= "<option value=''>Seleccionar Estado</option>";

	while($rowS = $resultadoS->fetch_assoc())
	{
		if($rowS['description']=="Distrito Federal"){
			$rowS['description'] = "Ciudad de México";
		}
		$html.= "<option value='".strtoupper($rowS['description'])."'>".mb_strtoupper($rowS['description'],"utf-8")."</option>";
	}

	echo $html;
?>		