<?php

@$accion = $_POST['accion'];
@$palabra = $_POST['palabra'];

if ($accion){
		switch ($accion){
			case 1:
				buscar($palabra);
			break;
		}
	}

function buscar($palabra){
	// First, include Requests
	include('Requests-1.7.0/library/Requests.php');

	// Next, make sure Requests can load internal classes
	Requests::register_autoloader();

	// Now let's make a request!
	 $request = Requests::get('https://demapi.colmex.mx/api/Palabras/GetPalabraFormada/'.$palabra, array('cliente'=>'uniabidismex', 'token'=>'9dYxNXFoemw7ZNWg9Ra92gpLA7BisaHU','Accept' => 'application/json'));
	 $respuesta= json_decode($request->body);

	foreach($respuesta as $entradas){
		//$entradas->entrada."</span></strong><sup>".$entradas->superIndice."</sup>"."<br>";
		//echo "<strong>".'<span style="font-size:larger;">'.$entradas->entrada."</span></strong>";
		foreach($entradas->acepciones as $acepcion){
			echo $acepcion."<br>";
		}
		echo "<br>";

	}
}
