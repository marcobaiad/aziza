<?php



class conexion
{

    private $server;
    private $user;
    private $password;
    private $database;
    private $connectionString;
    private $connectionOptions;
    private $connection;

    function __construct()
    {
        $listadatos = $this->dataConnection();
        foreach ($listadatos as $key => $value) {
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
            $this->database = $value['database'];
            $this->port = $value['port'];
        }
    }

    private function connect()
    {
        try {
            $this->connectionString = "mysql:host=" . $this->server . ";dbname=" . $this->database . "";
            $this->connectionOptions = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false];

            $this->connection = new PDO($this->connectionString, $this->user, $this->password, $this->connectionOptions);
            return $this->connection;
        } catch (PDOException $e) {
            print "\nError!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    private function disconnect() {
        $this->connection = null;
    }

    private function dataConnection()
    {
        $direccion = dirname(__FILE__);
        $jsondata = file_get_contents($direccion . "/" . "config");
        return json_decode($jsondata, true);
    }

    public function getData($sqlstr)
    {
        $this->checkTokenState();
        $getConsult = $this->connect()->query($sqlstr);
        $result = $getConsult->fetchAll(PDO::FETCH_OBJ);
        $this->disconnect();
        return $result;
    }

    public function checkTokenState() {
        $fecha = date("Y-m-d");
        $queryDisable = "UPDATE usuarios_token SET Estado = 'Inactivo' WHERE Estado = 'Activo' AND Fecha < :fecha";
        $results = $this->connect()->prepare($queryDisable);
        $results->execute(['fecha' => $fecha]);
    } 

    public function postData($sqlstr)
    {
        $this->checkTokenState();
        $results = $this->connect()->prepare($sqlstr);
        $isInsert = $results->execute();
        if ($isInsert) {
            $lastID = $this->connection->lastInsertId();
            return $lastID;
        }
    }

    public function modifyOrDeleteData($sqlstr)
    {
        $this->checkTokenState(); 
        $results = $this->connect()->prepare($sqlstr);
        $returnData = $results->execute();
        $this->disconnect();
        return $returnData;
    }

    protected function encript($string)
    {
        return md5($string);
    }
}