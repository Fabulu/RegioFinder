<?php

require __DIR__ . "/../vendor/autoload.php";

(new \RegioMap\Config\ConfigLoad());

//$stmt = ConfigLoad::$connection->prepare("DROP TABLE IF EXISTS hofladen;");
//$stmt->execute();


try {

    $sql_file = __DIR__ . '/database.sql';

    if (!file_exists($sql_file)) {
        die("Fehler: SQL-Datei wurde nicht gefunden: $sql_file");
    }

    $sql = file_get_contents($sql_file);

    \RegioMap\Config\ConfigLoad::$connection->exec($sql);

} catch (\PDOException $e) {
    echo "Fehler: " . $e->getMessage();
}