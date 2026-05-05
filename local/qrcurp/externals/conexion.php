<?php
require_once(__DIR__.'/../../../config.php');

use local_qrcurp\local\external_db;

// CONEXIÓN A BD PARA EXTRAER LOS DICCIONARIOS DE PAISES, ESTADOS, MUNICIPIOS, ETC.
try {
    $mysqli = external_db::create_catalog_connection();
} catch (\mysqli_sql_exception $exception) {
    echo 'Conexion Fallida : ' . s($exception->getMessage());
    exit();
}
