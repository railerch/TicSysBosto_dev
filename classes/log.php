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
        // Datos
        $fecha      = date('Y-m-d h:i:s');
        $user_addr  = $_SERVER['REMOTE_ADDR'];
        $user_name  = $_SESSION['nombre'] . ' (' . $_SESSION['locacion'] . ')';
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $uri        = explode('?', $_SERVER['REQUEST_URI']);

        // Si no se indica la actividad se tomara la URI como parametro de actividad
        if ($cadena == NULL) {
            $actividad = $uri[0];
        } else {
            $actividad = $cadena;
        }

        // Registro
        $log = $fecha . ' | ' . $user_addr . ' | ' . $user_name . ' | ' . $user_agent . ' | ' . $actividad . "\n";

        if (strpos($uri[0], 'reporte')) {
            file_put_contents('../log.md', $log, FILE_APPEND);
        } else {
            file_put_contents('log.md', $log, FILE_APPEND);
        }

        $_SESSION['new_conn'] = $_SERVER['REMOTE_ADDR'];
    }

    public function __destruct()
    {
    }
}
