<?php

use RegioMap\Config\ConfigLoad;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . "/../vendor/autoload.php";

(new \RegioMap\Config\ConfigLoad());

$stmt = ConfigLoad::$connection->prepare("DROP TABLE IF EXISTS hofladen;");
$stmt->execute();
