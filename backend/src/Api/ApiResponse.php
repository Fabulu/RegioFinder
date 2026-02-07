<?php

namespace RegioMap\Api;

use RegioMap\Config\ConfigLoad;

class ApiResponse
{


    public function render()
    {


        (new ConfigLoad());

        //header("Content-Type: application/json");

        //http_response_code($this->statusCode); header('Content-Length: ' . $this->filesize);

        //ader('Content-Type: application/json');
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");


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
                        $users[] = $row;
                    }

                    echo json_encode($users, JSON_PRETTY_PRINT);

                }
                break;

            case 'POST':

                $betrieb = $input['betrieb'];
                $strasse = $input['strasse'];
                $plz = $input['plz'];
                $ort = $input['ort'];
                $web = $input['web'];
                $lat = $input['lat'];
                $lon = $input['lon'];

                $data = [];
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