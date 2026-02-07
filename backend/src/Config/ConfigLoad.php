<?php

namespace RegioMap\Config;

class ConfigLoad
{

    /**
     * @var \PDO
     */
    public static $connection;

    public function __construct()
    {

        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        $filename = __DIR__ . "/../../config.ini";

        $ini_array = parse_ini_file($filename);

        $host = $ini_array["host"];
        $dbname = $ini_array["database"];
        $user = $ini_array["user"];
        $password = $ini_array["password"];

        try {
            ConfigLoad::$connection = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
            ConfigLoad::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo "Fehler: " . $e->getMessage();
        }

    }

}