<?php
require_once 'clases/responses.class.php';
require_once 'clases/movements.class.php';
include 'cors.php';

$_respuestas = new responses;
$_movements = new movimientos;

cors();

if ($_SERVER['REQUEST_METHOD'] == "GET") {

    if (isset($_GET["token"])) {
        $token = $_GET["token"];
        if (!isset($_GET["page"]) && !isset($_GET['id']) && isset($_GET["firstDate"]) && isset($_GET["endDate"]) && $_GET["idUser"] != undefined) {
            $firstDate = $_GET["firstDate"];
            $endDate = $_GET["endDate"];
            $idUser = $_GET["idUser"];
            $sumMovements = $_movements->getSumMovements($token, $firstDate, $endDate, $idUser);
            header("Content-Type: application/json");
            echo json_encode($sumMovements);
            http_response_code(200);
        } else if ($_GET["page"] != undefined && !isset($_GET['id']) && $_GET["token"] != undefined && $_GET["firstDate"] != undefined && $_GET["endDate"] != undefined && $_GET["idUser"] != undefined) {
            $pagina = $_GET["page"];
            $firstDate = $_GET["firstDate"]; 
            $endDate = $_GET["endDate"];
            $idUser = $_GET["idUser"];
            $movements = $_movements->getMovements($token, $pagina, $firstDate, $endDate, $idUser);
            header("Content-Type: application/json");
            echo json_encode($movements);
            http_response_code(200);
        } else if (!isset($_GET["page"]) && isset($_GET["token"]) && !isset($_GET['id'])) {
            $pagina = 1;
            $movements = $_movements->getMovements($token, $pagina);
            header("Content-Type: application/json");
            echo json_encode($movements);
            http_response_code(200);
        } else if (isset($_GET['id']) && isset($_GET["token"]) && !isset($_GET["page"])) {
            $productID = $_GET['id'];
            $datosMovement = $_movements->getUniqueMovement($productID);
            header("Content-Type: application/json");
            echo json_encode($datosMovement);
            http_response_code(200);
        }
    } else {
        $datosArray = $_respuestas->error_401();
        echo json_encode($datosArray);
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $postBody = file_get_contents("php://input");
    $datosArray = $_movements->addNewMovement($postBody);
    header('Content-Type: application/json');
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else if ($_SERVER['REQUEST_METHOD'] == "PUT") {
    $postBody = file_get_contents("php://input");
    $datosArray = $_movements->editMovement($postBody);
    header('Content-Type: application/json');
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

    $headers = getallheaders();
    if (isset($headers["token"]) && isset($headers["id"])) {
        //recibimos los datos enviados por el header
        $send = [
            "token" => $headers["token"],
            "id" => $headers["id"]
        ];
        $postBody = json_encode($send);
    } else {
        //recibimos los datos enviados
        $postBody = file_get_contents("php://input");
    }

    //enviamos datos al manejador
    $datosArray = $_movements->delete($postBody);
    //delvovemos una respuesta 
    header('Content-Type: application/json');
    if (isset($datosArray["result"]["error_id"])) {
        $responseCode = $datosArray["result"]["error_id"];
        http_response_code($responseCode);
    } else {
        http_response_code(200);
    }
    echo json_encode($datosArray);
} else {
    header('Content-Type: application/json');
    $datosArray = $_respuestas->error_405();
    echo json_encode($datosArray);
}