<?php
require_once 'clases/responses.class.php';
require_once 'clases/category.class.php';
include 'cors.php';

$_respuestas = new responses;
$_category = new category;

cors();

if ($_SERVER['REQUEST_METHOD'] == "GET") {

    if (isset($_GET["token"])) {
        $token = $_GET["token"];
        if (!isset($_GET['id']) && isset($_GET["token"])) {
            $categories = $_category->getCategories($token);
            header("Content-Type: application/json");
            echo json_encode($categories);
            http_response_code(200);
        } else if (isset($_GET['id']) && isset($_GET["token"])) {
            $id = $_GET['id'];
            $category = $_category->getUniqueCategory($token, $id);
            header("Content-Type: application/json");
            echo json_encode($category);
            http_response_code(200);
        }
    } else {
        $datosArray = $_respuestas->error_401();
        echo json_encode($datosArray);
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $postBody = file_get_contents("php://input");
    $datosArray = $_category->addNewCategry($postBody);
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
    $datosArray = $_category->editCategory($postBody);
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

var_dump($postBody);
    //enviamos datos al manejador
    $datosArray = $_category->delete($postBody);
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