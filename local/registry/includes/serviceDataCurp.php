<?php


header('Content-Type: application/json');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido. Solo POST']);
    exit;
}

// Obtener el valor de xCurp desde el body
$curp = $_POST['curp'] ?? '';

if (empty($curp)) {
    http_response_code(400);
    echo json_encode(['error' => 'El parámetro curp es requerido']);
    exit;
}

// Definir la URL del servicio interno
$url = 'http://172.18.30.111:81/index.php/consulta/porCurp';
//$url = 'http://148.207.151.43:81/index.php/consulta/porCurp';

// Inicializar cURL
$ch = curl_init($url);

// Configurar cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['xCurp' => $curp]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

// Ejecutar la solicitud
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

// Cerrar cURL
curl_close($ch);

// Manejar posibles errores
if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la solicitud cURL: ' . $error]);
    exit;
}

// Pasar la respuesta original del backend
http_response_code($httpCode);
echo $response;
