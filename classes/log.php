<?php

declare(strict_types=1);

/* CLASE LOG
Registro y consulta de logs generados por el sistema
*/

class Log
{
    public $log = false;

    public function __construct()
    {
        $this->log = true;
    }

    static public function registrar_log(string $cadena = NULL): void
    {
        global $conn;

        // Datos
        $fecha      = date('Y-m-d h:i:s');
        $user_addr  = $_SERVER['REMOTE_ADDR'];
        $user_name  = $_SESSION['nombre'] . ' (' . $_SESSION['empresa'] . ')';
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $uri        = explode('?', $_SERVER['REQUEST_URI']);

        // Si no se indica la actividad se tomara la URI como parametro de actividad
        if ($cadena == NULL) {
            $accion = $uri[0];
        } else {
            $accion = $cadena;
        }

        // Registro
        try {
            $conn->query("INSERT INTO logs 
            (id, fecha, ip, usuario, plataforma, accion) 
            VALUES 
            (NULL, '$fecha', '$user_addr', '$user_name', '$user_agent', '$accion')");

            $_SESSION['new_conn'] = $_SERVER['REMOTE_ADDR'];
        } catch (PDOException $e) {
            echo "HA OCURRIDO UN ERROR: " . $e->getMessage();
        }
    }

    public function __destruct()
    {
    }
}
