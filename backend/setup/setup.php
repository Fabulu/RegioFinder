<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . "/../vendor/autoload.php";

(new \RegioMap\Config\ConfigLoad());



/*

$host = "localhost";
$dbname = "local_map";
$user = "root";
$pass = "123456";
*/
try {
  //  $pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
  //  $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  //  echo "Verbindung erfolgreich!";

    $sql_file = __DIR__ . '/database.sql';

    // SQL-Datei einlesen
    if (!file_exists($sql_file)) {
        die("Fehler: SQL-Datei wurde nicht gefunden: $sql_file");
    }

    $sql = file_get_contents($sql_file);

    // Mehrere Statements sauber ausführen
    \RegioMap\Config\ConfigLoad::$connection->exec($sql);

    echo "<b>Installation erfolgreich abgeschlossen!</b>";


} catch (\PDOException $e) {
    echo "Fehler: " . $e->getMessage();
}









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


$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $result = $conn->query("SELECT * FROM users WHERE id=$id");
            $data = $result->fetch_assoc();
            echo json_encode($data);
        } else {
            $result = $conn->query("SELECT * FROM users");
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            echo json_encode($users);
        }
        break;

    case 'POST':
        $name = $input['name'];
        $email = $input['email'];
        $age = $input['age'];
        $conn->query("INSERT INTO users (name, email, age) VALUES ('$name', '$email', $age)");
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

$conn->close();

*/

