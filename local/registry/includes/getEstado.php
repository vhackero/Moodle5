<?php

require ('../externals/conexion.php');

$id_codigo = $_POST['id_postal'];
//	 $id_codigo = 90401;   	//para comprobar que esté ejecutando la consulta

$queryE = "SELECT DISTINCT es.description FROM states as es
INNER JOIN municipalities as mun ON mun.id_state = es.id
INNER JOIN suburbs as sub ON sub.id_municipality = mun.id
WHERE sub.cp  = $id_codigo;";

$resultadoE = $mysqli->query($queryE);

//$html= "<option value='0'>Seleccionar Estado</option>";

while($rowE = $resultadoE->fetch_assoc())
{
	$html.= "<option value='".strtoupper($rowE['description'])."'>".strtoupper($rowE['description'])."</option>";
}

echo $html;
?>		