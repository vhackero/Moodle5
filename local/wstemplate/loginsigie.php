<?php
require_once ('../../config.php');
global $CFG;
// La URL del servidor al que deseas hacer la solicitud POST
$url = "http://3.17.67.194:8080/plataforma/j_spring_security_check";

// Los datos que deseas enviar (en formato application/x-www-form-urlencoded)
$post_data = array(
    'j_username' => 'es241100227',
    'j_password' => 'Paco1975*'
);

// Codificar los datos para formulario (application/x-www-form-urlencoded)
$post_fields = http_build_query($post_data);

// Iniciar una nueva sesión cURL
$ch = curl_init($url);

// Establecer las opciones de cURL
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); // Usamos los datos codificados
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded',  // Tipo de contenido para formulario
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:135.0) Gecko/20100101 Firefox/135.0',  // Emular un navegador web
//    'Referer: http://3.17.67.194:8080/plataforma/login',  // Enviar el referer para la solicitud de inicio de sesión
));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Tiempo máximo de ejecución en segundos
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // Tiempo máximo de espera para la conexión en segundos
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecciones
curl_setopt($ch, CURLOPT_MAXREDIRS, 50); // Permitir hasta 50 redirecciones
curl_setopt($ch, CURLOPT_VERBOSE, true); // Habilitar la depuración detallada para ver las redirecciones

// Manejo de cookies
curl_setopt($ch, CURLOPT_COOKIEFILE, $CFG->dataroot.'cookie.txt');  // Almacenar cookies recibidas
curl_setopt($ch, CURLOPT_COOKIEJAR, $CFG->dataroot.'cookie.txt');   // Almacenar cookies en archivo

// Realizar la solicitud POST y obtener la respuesta
$response = curl_exec($ch);

// Verificar si ocurrió algún error
if ($response === false) {
    echo "Error en la solicitud cURL: " . curl_error($ch);
} else {
    // Leer el archivo cookie.txt
    $cookie_file = $CFG->dataroot.'cookie.txt';
    $cookie_lines = file($cookie_file, FILE_IGNORE_NEW_LINES);

    foreach ($cookie_lines as $line) {
        // Ignorar comentarios
        if (strpos($line, '#') === 0) {
            continue;
        }

        // Dividir los campos de cada cookie
        $fields = explode("\t", $line);

        // Asegurarse de que la línea tiene el formato correcto
        if (count($fields) == 7) {
            // Extraer la información de la cookie
            $domain = $fields[0];
            $secure = $fields[1] == "TRUE" ? true : false;
            $path = $fields[2];
            $httponly = $fields[3] == "TRUE" ? true : false;
            $expire = $fields[4] == "0" ? 0 : time() + $fields[4];  // Expiración: 0 significa sesión
            $cookie_name = $fields[5];
            $cookie_value = $fields[6];

            // Establecer la cookie en el navegador
            setcookie($cookie_name, $cookie_value, $expire, $path, $domain, $secure, $httponly);
        }
    }

// Verificar que la cookie se haya establecido
    if (isset($_COOKIE["JSESSIONID"])) {
        echo "Cookie JSESSIONID: " . $_COOKIE["JSESSIONID"];
    } else {
        echo "No se encontró la cookie.";
    }

    curl_close($ch);
    echo 'Redireccionando';
//    header("Location:",'http://3.17.67.194:8080/plataforma/tablero');
    exit();
}

// Cerrar la sesión cURL
?>
