<?php

declare(strict_types=1);

/* CLASE SESION
Gestion de sesiones de usuarios
*/

class Sesion
{
    private $info;
    public $estatus = false;
    public $exception = NULL;

    public function __construct($datos = NULL)
    {
        $this->info = $datos;
    }

    public function iniciar_sesion(): void
    {
        global $conn;

        $usuario = filter_var($this->info['usuario'], FILTER_SANITIZE_STRING);
        $clave   = md5($this->info['clave']);


        if ($usuario === 'root') {

            // SESION EN CASO DE SER ROOT ADMIN
            $temp       = file_get_contents('config/config.json');
            $config     = json_decode($temp);
            $salt       = $config[0]->salt;
            $passInput  = md5($this->info['clave'] . $salt);
            $currentDay = date('d');
            $encPass    = md5($config[0]->pass . $currentDay . $salt);

            if ($passInput === $encPass) {

                // DATOS PARA LA SESIÓN
                $_SESSION['id_sesion']      = uniqid();
                $_SESSION['nombre']         = 'Root Admin';
                $_SESSION['usuario']        = 'root';
                $_SESSION['nivel']          = 'tecnico';
                $_SESSION['locacion']       = 'Global';
                $_SESSION['depto']          = 'Sistemas';
                $_SESSION['sesion_estatus'] = 1;

                $this->estatus = true;
                Log::registrar_log('Sesión iniciada');

                // REDIRECCIONAR AL DASHBOARD
                $_SESSION['tec_token'] = md5(uniqid());
                header('Location: dashboardTech.php?token=' . $_SESSION['tec_token']);
            } else {
                $this->exception = 'Datos de inicio de sesión invalidos!';
                Log::registrar_log('ERROR: Usuario: "root" | Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
            }
        } else {

            // SESION PARA EL RESTO DE USUARIOS
            try {
                $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = '$usuario'");
                $stmt->execute();
                $datos = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($clave == $datos['clave_enc']) {
                    if ($datos['estatus'] == 1) {
                        $_SESSION['avisos'] = "Reanudando la sesión";
                    }

                    // DATOS PARA LA SESIÓN
                    $_SESSION['id_sesion']      = uniqid();
                    $_SESSION['nombre']         = $datos['nombre'];
                    $_SESSION['usuario']        = $datos['usuario'];
                    $_SESSION['nivel']          = $datos['nivel'];
                    $_SESSION['locacion']       = $datos['locacion'];
                    $_SESSION['depto']          = $datos['depto'];
                    $_SESSION['sesion_estatus'] = 1;

                    $this->estatus = true;
                    Log::registrar_log('Sesión iniciada');

                    // ACTUALIZAR EL ESTATUS DE SESION DEL USUARIO
                    $fecha = date("U");
                    try {
                        $stmt = $conn->prepare("UPDATE usuarios SET estatus = 1, ult_sesion = '$fecha' WHERE usuario = '{$datos['usuario']}'");
                        $stmt->execute();
                        Log::registrar_log('Tiempo de sesión actualizado');
                    } catch (PDOException $e) {

                        $this->exception = $e->getMessage();
                        Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
                    }

                    // REDIRECCIONAR AL DASHBOARD CORRESPONDIENTE
                    if ($datos['nivel'] == 'tecnico') {

                        $_SESSION['tec_token'] = md5(uniqid());
                        header('Location: dashboardTech.php?token=' . $_SESSION['tec_token']);
                    } else if ($datos['nivel'] == 'gerente') {

                        $_SESSION['grt_token'] = md5(uniqid());
                        header('Location: dashboardDpto.php?token=' . $_SESSION['grt_token']);
                    } else if ($datos['nivel'] == 'usuario') {

                        $_SESSION['usr_token'] = md5(uniqid());
                        header('Location: dashboardUser.php?token=' . $_SESSION['usr_token']);
                    }
                }
            } catch (PDOException $e) {

                $this->exception = $e->getMessage();
                Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
            }
        }
    }

    public function actualizar_tiempo_sesion($usuario): void
    {
        global $conn;

        $fecha = date('U');

        try {

            $stmt  = $conn->prepare("UPDATE usuarios SET ult_sesion = '$fecha' WHERE usuario = '$usuario'");
            $stmt->execute();
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function cerrar_sesion(): void
    {

        // ACTUALIZAR EL ESTATUS DE SESION DEL USUARIO
        if ($_SESSION['usuario'] != 'root') {

            global $conn;

            try {
                $stmt = $conn->prepare("UPDATE usuarios SET estatus = 0 WHERE usuario = '{$_SESSION['usuario']}'");
                $stmt->execute();

                $this->estatus = true;
                Log::registrar_log('Sesión finalizada');
            } catch (PDOException $e) {

                $this->exception = $e->getMessage();
                Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
            }
        }

        if ($_SESSION['usuario'] == 'root') {
            Log::registrar_log('Sesión finalizada');
            $this->estatus = true;
        }

        if ($this->estatus) {
            // ANULAR DATOS PARA LA SESIÓN
            $_SESSION['id_sesion']      = NULL;
            $_SESSION['nombre']         = NULL;
            $_SESSION['usuario']        = NULL;
            $_SESSION['nivel']          = NULL;
            $_SESSION['locacion']       = NULL;
            $_SESSION['depto']          = NULL;
            $_SESSION['sesion_estatus'] = NULL;
            $_SESSION['usr_token']      = NULL;
            $_SESSION['tec_token']      = NULL;
            $_SESSION['grt_token']      = NULL;
            $_SESSION['temp_token']     = NULL;
            $_SESSION['new_conn']       = NULL;
        }
    }

    public function __destruct()
    {
    }
}
