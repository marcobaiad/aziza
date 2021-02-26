<?php
require_once 'conexion/conexion.php';
require_once 'responses.class.php';
require_once 'token.class.php';

class auth extends conexion
{


    public function login($json)
    {
        $_respustas = new responses;
        $datos = json_decode($json, true);
        if (!isset($datos['user']) || !isset($datos["password"])) {
            //error con los campos
            return $_respustas->error_400();
        } else {
            //todo esta bien 
            $user = $datos['user'];
            $password = $datos['password'];
            $password = parent::encript($password);
            $responseUser = $this->getUserData($user);
            if ($responseUser >= 1) {
                //verificar si la contraseña es igual
                if ($password == $responseUser[0]->{'Password'}) {
                    if ($responseUser[0]->{'Estado'} == "Activo") {
                        //crear el token
                        $verificar  = $this->insertToken($responseUser[0]->{'UsuarioId'});
                        if ($verificar) {
                            // si se guardo
                            $result = $_respustas->response;
                            $result["result"] = array(
                                "token" => $verificar,
                                "idUser" => $responseUser[0]->{'UsuarioId'}
                            );
                            return $result;
                        } else {
                            //error al guardar
                            return $_respustas->error_500("Internal Server Error, we couldn't save token");
                        }
                    } else {
                        //el usuario esta inactivo
                        return $_respustas->error_406("User inactive, please contact an administrator");
                    }
                } else {
                    //la contraseña no es igual
                    return $_respustas->error_404("Not Found c");
                }
            } else {
                //no existe el usuario
                return $_respustas->error_404("Not Found u");
            }
        }
    }



    private function getUserData($correo)
    {
        $query = "SELECT UsuarioId,Password,Estado FROM usuarios WHERE Usuario = '$correo'";
        $userData = parent::getData($query);
        return $userData;
    }


    private function insertToken($usuarioid)
    {
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16, $val));
        $date = date("Y-m-d H:i");
        $estado = "Activo";
        $query = "INSERT INTO usuarios_token (UsuarioId,Token,Estado,Fecha)VALUES('$usuarioid','$token','$estado','$date')";
        $verifica = parent::postData($query);
        if ($verifica) {
            return $token;
        } else {
            return 0;
        }
    }
}