<?php

namespace RegioMap\Api;

use RegioMap\Config\ConfigLoad;

class ApiResponse
{


    public function render()
    {


        (new ConfigLoad());

        header("Content-Type: application/json");


       /* $host = "localhost";
        $dbname = "local_map";
        $user = "root";
        $pass = "123456";

        try {
            $conn = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            //echo "Verbindung erfolgreich!";
        } catch (\PDOException $e) {
            echo "Fehler: " . $e->getMessage();
        }*/


        /*
        $servername = 'localhost';
        $username = 'root';
        $password = '';
        $dbname = 'localmap';

        $conn = null;

        try {
            $conn = new \PDO("mysql:host=$servername;dbname=,$dbname", $username, $password);
            // set the PDO error mode to exception
            //$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully";
        } catch(\PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        */

        $method = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true);

        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    $id = $_GET['id'];
                    $result = ConfigLoad::$connection->query("SELECT * FROM hofladen WHERE id=$id");
                    $data = $result->fetch_assoc();
                    echo json_encode($data);
                } else {
                    $result = ConfigLoad::$connection->query("SELECT * FROM hofladen");
                    $users = [];


                    $pois = $result->fetchAll(\PDO::FETCH_ASSOC);

                    foreach ($pois as $row) {
                        //echo "<p><b>ID:</b> {$poi['id']} – {$poi['poi_description']}</p>";
                        $users[] = $row;
                    }



                    /*while ($row = $result->fetch()) {
                        $users[] = $row;
                    }*/
                    echo json_encode($users);
                }
                break;

            case 'POST':

                //print_r($input);

                //print_r($_POST);
                //print_r($_GET);

                $betrieb = $input['betrieb'];
$strasse= $input['strasse'];
$plz = $input['plz'];
$ort = $input['ort'];
$web = $input['web'];
$lat = $input['lat'];
$lon = $input['lon'];

                /*
                id          INT AUTO_INCREMENT PRIMARY KEY,
    poi     TEXT,
    betriebstyp int,
    strasse     TEXT,
    plz         varchar(10),
    web         varchar(255),
    lat         float,
    lon         float
                */


                //$email = $input['email'];
                //$age = $input['age'];
                //$conn->query("INSERT INTO poi (name, email, age) VALUES ('$name', '$email', $age)");


            $data=[];
            $data[":betrieb"] = $betrieb;
            $data[":strasse"] = $strasse;
            $data[":plz"] = $plz;
            $data[":ort"] = $ort;
            $data[":web"] = $web;
            $data[":lat"] = $lat;
            $data[":lon"] = $lon;


                $stmt = ConfigLoad::$connection->prepare("INSERT INTO hofladen (betrieb,strasse,plz,ort,web,lat,lon) VALUES (:betrieb,:strasse,:plz,:ort,:web,:lat,:lon)");
                $stmt->execute($data);

                echo json_encode(["message" => "Hoflade added successfully"]);
                break;

            case 'PUT':
                $id = $_GET['id'];
                $betrieb = $input['name'];
                $email = $input['email'];
                $age = $input['age'];
                $conn->query("UPDATE users SET name='$betrieb',
                     email='$email', age=$age WHERE id=$id");
                echo json_encode(["message" => "User updated successfully"]);
                break;

            case 'DELETE':
                $id = $_GET['id'];
                $conn->query("DELETE FROM users WHERE id=$id");
                echo json_encode(["message" => "User deleted successfully"]);
                break;

            default:
                echo json_encode(["message" => "Invalid request method"]);
                break;
        }



    }


}