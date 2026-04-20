<?php
require_once(__DIR__.'/../../config.php');

use local_qrcurp\local\config;
use local_qrcurp\local\external_db;

// DATOS TOMADOS DE LA CONFIGURACIÓN DEL PLUGIN.
$remotedbname = config::get_string('dbname');
$remotedbtable = config::get_string('dbtable');
$remoteinsertdb = config::get_bool('dbinsert');

// CREANDO CONEXION y LA VARIABLE GLOBAL PARA NUESTRA CONEXIÓN EXTERNA.
global $DBEXTERNAL;

try {
    $DBEXTERNAL = external_db::create_primary_connection();
    $DBEXTERNAL->dbname = $remotedbname;
    $DBEXTERNAL->dbtable = $remotedbtable;
    $DBEXTERNAL->dbinsert = $remoteinsertdb;
    $DBEXTERNAL->errordbportname = 0;
} catch (\mysqli_sql_exception $exception) {
    error_reporting(E_ERROR);
    $message = 'Conexión fallida, revisar la configuración de conexión a la base de datos externa.';
    redirect('index.php', $message, null, \core\output\notification::NOTIFY_ERROR);
}
