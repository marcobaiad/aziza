<?php 

class responses {

    public  $response = [
        'status' => "ok",
        "result" => array()
    ];


    public function error_405(){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "405",
            "error_msg" => "Method Not Allowed"
        );
        return $this->response;
    }

    public function error_404($valor = "Not Found"){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "404",
            "error_msg" => $valor
        );
        return $this->response;
    }

    public function error_406($valor = "Not Acceptable"){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "406",
            "error_msg" => $valor
        );
        return $this->response;
    }


    public function error_400(){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "400",
            "error_msg" => "Bad Request"
        );
        return $this->response;
    }


    public function error_500($valor = "Server Error, please Try Again"){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "500",
            "error_msg" => $valor
        );
        return $this->response;
    }


    public function error_401($valor = "Unauthorized"){
        $this->response['status'] = "error";
        $this->response['result'] = array(
            "error_id" => "401",
            "error_msg" => $valor
        );
        return $this->response;
    }
    
    

}

?>