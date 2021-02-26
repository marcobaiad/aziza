<?php
require_once "conexion/conexion.php";
require_once "responses.class.php";


class category extends conexion
{

    private $table = "categories";
    private $id = "";
    private $category = "";
    private $token = "";
    private $enabled = "";

    public function getCategories($token)
    {
        $_respuestas = new responses;
        $this->token = $token;
        $query = "SELECT * FROM categories WHERE enabled = 1";
        $isValidToken = $this->findToken();
        if ($isValidToken) {
            $datos = parent::getData($query);
            return ($datos);
        } else {
            return $_respuestas->error_401();
        }
    }

    public function getUniqueCategory($token, $id)
    {
        $_respuestas = new responses;
        $this->token = $token;
        $query = "SELECT * FROM " . $this->table . " WHERE id = '$id'";
        $isValidToken = $this->findToken();
        if ($isValidToken) {
            $datos = parent::getData($query);
            return ($datos);
        } else {
            return $_respuestas->error_401();
        }
    }

    public function addNewCategry($json)
    {
        $_respuestas = new responses;
        $datos = json_decode($json, true);
        if (!isset($datos['token'])) {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $isValidToken = $this->findToken();
            if ($isValidToken) {
                if (!isset($datos['category'])) {
                    return $_respuestas->error_400();
                } else {
                    $this->category = $datos['category'];
                    $resp = $this->postCategory();
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


    private function postCategory()
    {
        $query = "INSERT INTO " . $this->table . " (category)
        values ('" . $this->category . "')";
        $resp = parent::postData($query);
        if ($resp >= 1) {
            return $resp;
        } else {
            return 0;
        }
    }

    public function editCategory($json)
    {
        $_respuestas = new responses;
        $datos = json_decode($json, true);
        if ($datos['token'] === '') {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $isValidToken = $this->findToken();
            if ($isValidToken) {
                if ($datos['id'] === '') {
                    return $_respuestas->error_400();
                } else {
                    $this->id = $datos['id'];
                    if ($datos['category'] != '') {
                        $this->category = $datos['category'];
                    }
                   if ($datos['enabled'] != '') {
                        $this->enabled = $datos['enabled'];
                    }
                    $resp = $this->modifyCategory();
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
                return $_respuestas->error_401("El Token que envio es invalido o ha caducado");
            }
        }
    }


    private function modifyCategory()
    {
        $query = "UPDATE " . $this->table . " SET category = '" . $this->category . "', enabled = '" . $this->enabled . "' WHERE id = $this->id";
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
        if ($datos['token'] == '') {
            return $_respuestas->error_401();
        } else {
            $this->token = $datos['token'];
            $isValidToken = $this->findToken();           
            if ($isValidToken) {
                if ($datos['id'] == '') {
                    return $_respuestas->error_400();
                } else {
                    $this->id = $datos['id'];
                    $resp = $this->deleteCategory();
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


    private function deleteCategory()
    {
        $query = "DELETE FROM " . $this->table . " WHERE id= '" . $this->id . "'";
        $resp = parent::modifyOrDeleteData($query);
        if ($resp >= 1) {
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