<?php

namespace Regiomap\Api;

use Regiomap\Config\ConfigLoad;

class ApiResponse
{


    public function render()
    {


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
                    $result = ConfigLoad::$connection->query("SELECT * FROM poi WHERE id=$id");
                    $data = $result->fetch_assoc();
                    echo json_encode($data);
                } else {
                    $result = ConfigLoad::$connection->query("SELECT * FROM poi");
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

                print_r($input);

                //print_r($_POST);
                //print_r($_GET);

                $name = $input['poi'];




                //$email = $input['email'];
                //$age = $input['age'];
                //$conn->query("INSERT INTO poi (name, email, age) VALUES ('$name', '$email', $age)");


                $stmt = ConfigLoad::$connection->prepare("INSERT INTO poi (poi) VALUES (:poi)");
                $stmt->execute([':poi' => $name]);


                echo json_encode(["message" => "User added successfully"]);
                break;

            case 'PUT':
                $id = $_GET['id'];
                $name = $input['name'];
                $email = $input['email'];
                $age = $input['age'];
                $conn->query("UPDATE users SET name='$name',
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