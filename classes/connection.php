<?php
// CONEXION A LA BASE DE DATOS MYSQL
#-----------------------------------

class Connection
{
    private $host;
    private $db;
    private $user;
    private $pass;
    public $error;

    public function __construct(string $ruta)
    {
        $temp = file_get_contents($ruta);
        $datos = json_decode($temp);
        $this->host = $datos[1]->host;
        $this->db   = $datos[1]->database;
        $this->user = $datos[1]->user;
        $this->pass = $datos[1]->pass;
    }

    public function db_conn()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db}";
            $conn = new PDO($dsn, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public function __destruct()
    {
    }
}
