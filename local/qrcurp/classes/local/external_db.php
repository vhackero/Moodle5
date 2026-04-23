<?php
// This file is part of Moodle - http://moodle.org/

namespace local_qrcurp\local;

defined('MOODLE_INTERNAL') || die();

use mysqli;

/**
 * Factory utilities for plugin external database connections.
 */
class external_db {
    /**
     * Build configured primary external DB connection.
     */
    public static function create_primary_connection(): mysqli {
        $host = config::get_string('dbhost');
        $dbname = config::get_string('dbname');
        $dbuser = config::get_string('dbuser');
        $dbpass = config::get_string('dbpass');
        $port = config::get_int('dbport', 3306);

        return self::connect($host, $dbuser, $dbpass, $dbname, $port);
    }

    /**
     * Build configured catalog DB connection.
     */
    public static function create_catalog_connection(): mysqli {
        $host = config::get_string('dbcatalogoshost');
        $dbname = config::get_string('dbcatalogos');
        $dbuser = config::get_string('dbcatalogosuser');
        $dbpass = config::get_string('dbcatalogospass');

        return self::connect($host, $dbuser, $dbpass, $dbname);
    }

    /**
     * Connect to MySQL with utf8 and strict exception handling.
     */
    private static function connect(string $host, string $user, string $pass, string $dbname, int $port = 3306): mysqli {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $mysqli = new mysqli($host, $user, $pass, $dbname, $port);
        $mysqli->set_charset('utf8');

        return $mysqli;
    }
}
