<?php
require_once "conexion/conexion.php";
require_once "responses.class.php";


class movimientos extends conexion
{

    private $table = "movements";
    private $id = "";
    private $product = "";
    private $amount = "";
    private $category = "";
    private $id_category = "";
    private $token = "";
    private $idUser = "";


    public function getMovements($token, $pageNumber, $firstDate, $endDate, $idUser)
    {
$_respuestas = new responses;
        $firstPage  = 0;
        $cuantityRows = 30;
        $this->token = $token;
        if ($pageNumber > 1) {
            $firstPage = ($cuantityRows * ($pageNumber - 1)) + 1;
            $cuantityRows = $cuantityRows * $pageNumber;
        }
        $query = "SELECT m.id, product, dateofmovements, amount, c.category AS category FROM movements AS m INNER JOIN categories AS c ON m.id_category = c.id WHERE m.id_user = $idUser AND m.dateofmovements BETWEEN '$firstDate' AND '$endDate' LIMIT $firstPage, $cuantityRows";
        $isValidToken = $this->findToken();
        if ($isValidToken) {
            $datos = parent::getData($query);
            return ($datos);
        } else {
            return $_respuestas->error_401();
        }
    }

    public function getSumMovements($token, $firstDate, $endDate, $idUser) {
$_respuestas = new responses;
        $query = "SELECT c.category AS category, sum(Amount) AS TotalSum FROM movements AS m INNER JOIN categories AS c ON m.id_category = c.id WHERE m.id_user = $idUser AND m.dateofmovements BETWEEN '$firstDate' AND '$endDate' GROUP BY m.id_category";
        $this->token = $token;
        $isValidToken = $this->findToken();
        if ($isValidToken) {
            $datos = parent::getData($query);
            return ($datos);
        } else {
            return $_respuestas->error_401();
        }
    }

    public function getUniqueMovement($movementID)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = '$movementID'";
        return parent::getData($query);
    }

    public function addNewMovement($json)
    {
        $_respuestas = new responses;
        $datos = json_decode($json, true);
        if (!isset($datos['token'])) {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $isValidToken = $this->findToken();
            if ($isValidToken) {
                if (!isset($datos['product']) || !isset($datos['id_category']) || !isset($datos['amount']) || !isset($datos['idUser'])) {
                    return $_respuestas->error_400();
                } else {
                    $this->product = $datos['product'];
                    $this->id_category = $datos['id_category'];
                    $this->amount = $datos['amount'];
                    $resp = $this->postMovement();
                    if ($resp >= 1) {
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "movementID" => $resp
                        );
                        return $respuesta;
                    } else {
                        return $_respuestas->error_500();
                    }
                }
            } else {
                return $_respuestas->error_401("Anauthorize, Session Expired");
            }
        }
    }


    private function postMovement()
    {
        $query = "INSERT INTO " . $this->table . " (product,id_category,amount,id_user)
        values ('" . $this->product . "','" . $this->id_category. "','" . $this->amount . "','" . $this->idUser . "')";
        $resp = parent::postData($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }

    public function editMovement($json)
    {
        $_respuestas = new responses;
        $datos = json_decode($json, true);
        if (!isset($datos['token'])) {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $isValidToken = $this->findToken();           
            if ($isValidToken) {
                if ($datos['id'] === '') { 
                    return $_respuestas->error_400();
                } else {
                    $this->id = $datos['id'];
                    if ($datos['product'] != '') {
                        $this->product = $datos['product'];
                    }
                    if ($datos['amount']!= '') {
                        $this->amount = $datos['amount'];
                    }
                    if ($datos['id_category']!= '') {
                        $this->id_category = $datos['id_category'];
                    }
                    $resp = $this->modifyMovement();
                    if ($resp) {
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id" => $this->id
                        );
                        return $respuesta;
                    } else {
                        return $_respuestas->error_500();
                    }
                }
            } else {
                return $_respuestas->error_401("El Token enviado es invalido o ha caducado");
            }
        }
    }


    private function modifyMovement()
    {
        $query = "UPDATE " . $this->table . " SET product= '" . $this->product . "', amount= ' $this->amount ', id_category=' $this->id_category ' WHERE id=$this->id ";
        $resp = parent::modifyOrDeleteData($query);
        if ($resp) {
            return $resp;
        } else {
            return 0;
        }
    }


    public function delete($json)
    {
        $_respuestas = new responses;
        $datos = json_decode($json, true);
        if (!isset($datos['token'])) {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $isValidToken =   $this->findToken();
            if ($isValidToken) {

                if (!isset($datos['id'])) {
                    return $_respuestas->error_400();
                } else {
                    $this->id = $datos['id'];
                    $resp = $this->deleteMovement();
                    if ($resp) {
                        $respuesta = $_respuestas->response;
                        $respuesta["result"] = array(
                            "id" => $this->id
                        );
                        return $respuesta;
                    } else {
                        return $_respuestas->error_500();
                    }
                }
            } else {
                return $_respuestas->error_401("Anauthorize, Session Expired");
            }
        }
    }


    private function deleteMovement()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id= '" . $this->id . "'";
        $resp = parent::modifyOrDeleteData($query);
        if ($resp) {
            return $resp;
        } else {
            return 0;
        }
    }


    private function findToken()
    {
        $query = "SELECT TokenId,UsuarioId,Estado from usuarios_token WHERE Token = '" . $this->token . "' AND Estado = 'Activo'";
        $resp = parent::getData($query);
        if ($resp) {
            return $resp;
        } else {
            return 0;
        }
    }


    private function actualizarToken($tokenid)
    {
        $date = date("Y-m-d H:i");
        $query = "UPDATE usuarios_token SET Fecha = '$date' WHERE TokenId = '$tokenid' ";
        $resp = parent::modifyOrDeleteData($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }
}