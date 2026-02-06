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

        $filename = __DIR__ . "/../../config.ini";

        $ini_array = parse_ini_file($filename);
        //print_r($ini_array);

        //$ini_array["dbname"] = $dbname;

        $host=$ini_array["host"];
        $dbname=$ini_array["database"];
        $user=$ini_array["user"];
        $pass=$ini_array["password"];



        try {
            ConfigLoad::$connection = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            ConfigLoad::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            //echo "Verbindung erfolgreich!";
        } catch (\PDOException $e) {
            echo "Fehler: " . $e->getMessage();
        }


    }


}