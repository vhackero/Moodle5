<?php
require_once(__DIR__.'/../../config.php');

use local_qrcurp\local\config;
use local_qrcurp\local\external_db;

// DATOS TOMADOS DE LA CONFIGURACIÓN DEL PLUGIN.
$remotedbhost = config::get_string('dbhost');
$remotedbname = config::get_string('dbname');
$remotedbuser = config::get_string('dbuser');
$remotedbtable = config::get_string('dbtable');
$remoteinsertdb = config::get_bool('dbinsert');
$validateconnection = config::get_bool('validateexternalconnection', true);
$connectiontestquery = trim(config::get_string('externalconnectiontestquery', 'SELECT 1'));

// CREANDO CONEXION y LA VARIABLE GLOBAL PARA NUESTRA CONEXIÓN EXTERNA.
global $DBEXTERNAL;

$DBEXTERNAL = new stdClass();
$DBEXTERNAL->dbname = $remotedbname;
$DBEXTERNAL->dbtable = $remotedbtable;
$DBEXTERNAL->dbinsert = $remoteinsertdb;
$DBEXTERNAL->errordbportname = 0;

$requiredconfig = [
    'dbhost' => $remotedbhost,
    'dbuser' => $remotedbuser,
    'dbname' => $remotedbname,
    'dbtable' => $remotedbtable,
];
$missingconfig = array_filter($requiredconfig, static function(string $value): bool {
    return trim($value) === '';
});
if (!empty($missingconfig)) {
    $DBEXTERNAL->errordbportname = 1;
    return;
}

try {
    $mysqli = external_db::create_primary_connection();
    if ($validateconnection) {
        if ($connectiontestquery === '') {
            $connectiontestquery = 'SELECT 1';
        }
        $validationresult = $mysqli->query($connectiontestquery);
        if ($validationresult === false) {
            throw new \mysqli_sql_exception('External DB validation query failed.');
        }
        if ($validationresult instanceof \mysqli_result) {
            $validationresult->free();
        }
    }
    $mysqli->dbname = $remotedbname;
    $mysqli->dbtable = $remotedbtable;
    $mysqli->dbinsert = $remoteinsertdb;
    $mysqli->errordbportname = 0;
    $DBEXTERNAL = $mysqli;
} catch (\mysqli_sql_exception $exception) {
    $DBEXTERNAL->errordbportname = 1;
}
