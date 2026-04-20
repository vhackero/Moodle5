<?php
    require ('../externals/conexion.php');
	
	$id_codigo = $_POST['id_postal'];
	// $id_municipio = 1;   	//para comprobar que este ejecutando la consulta
	
	$query = "SELECT 	id, colonia
					FROM postal
					WHERE codigo = $id_codigo";
	
	$resultado=$mysqli->query($query);
	
	while($row = $resultado->fetch_assoc())
	{
		$html.= "<option value='".$row['colonia']."'>".$row['colonia']."</option>";
	}
	echo $html;
?>