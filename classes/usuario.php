<?php

declare(strict_types=1);

/* CLASE USUARIO
Gestion de datos de usuarios
*/

class Usuario
{

    private $info;
    public $estatus = false;
    public $exception = NULL;

    public function __construct($datos = NULL)
    {
        $this->info = $datos;
    }

    public function registrar_usuario(): void
    {
        global $conn;

        try {
            $stmt = $conn->prepare("INSERT INTO usuarios (id_usuario, nombre, locacion, depto, usuario, nivel, clave, clave_enc, estatus, ult_sesion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $id_usuario = NULL;
            $nombre     = filter_var($this->info['nombre'], FILTER_SANITIZE_STRING);
            $locacion   = $this->info['locacion'];
            $depto      = $this->info['depto'];
            $usuario    = filter_var($this->info['usuario'], FILTER_SANITIZE_STRING);
            $nivel      = isset($this->info['nivel']) ? $this->info['nivel'] : "usuario";
            $clave      = filter_var($this->info['clave'], FILTER_SANITIZE_STRING);
            $clave_enc  = md5($clave);
            $estatus    = 0;
            $ult_sesion = NULL;

            $stmt->bindParam(1, $id_usuario);
            $stmt->bindParam(2, $nombre);
            $stmt->bindParam(3, $locacion);
            $stmt->bindParam(4, $depto);
            $stmt->bindParam(5, $usuario);
            $stmt->bindParam(6, $nivel);
            $stmt->bindParam(7, $clave);
            $stmt->bindParam(8, $clave_enc);
            $stmt->bindParam(9, $estatus);
            $stmt->bindParam(10, $ult_sesion);

            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log('Nuevo registro: ' . $_POST['nombre']);
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function actualizar_usuario(): void
    {

        global $conn;

        $id = $this->info['id'];

        if (isset($this->info['locacion']) and $this->info['locacion'] != 'NULL') {
            $locacion = "locacion = '{$this->info['locacion']}',";
            if ($_SESSION['nivel'] != 'tecnico') {
                $_SESSION['locacion'] = $this->info['locacion'];
            }
        } else {
            $locacion = NULL;
        }

        if (isset($this->info['depto']) and $this->info['depto'] != 'NULL') {
            $depto = "depto = '{$this->info['depto']}',";
        } else {
            $depto = NULL;
        }

        if (isset($this->info['nivel']) and $this->info['nivel'] != 'NULL') {
            $nivel = "nivel = '{$this->info['nivel']}',";
        } else {
            $nivel = NULL;
        }

        if (isset($this->info['clave'])) {
            $clave_enc  = md5($this->info['clave']);
            $clave = "clave = '{$this->info['clave']}', clave_enc = '{$clave_enc}'";
        } else {
            $clave = NULL;
        }

        try {
            $stmt = $conn->prepare("UPDATE usuarios SET $locacion $depto $nivel $clave WHERE id_usuario = '$id'");
            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log("Datos del usuario #{$id} actualizados");
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function usuarios_activos(): string
    {
        global $conn;

        try {

            $stmt = $conn->prepare("SELECT * FROM usuarios ORDER BY nombre");
            $stmt->execute();
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }

        // ARRAY DE USUARIOS Y DATOS GENERALES
        $usuarios = [];
        $datos    = [];


        // CONTADORES
        $totalUsuarios = $conectados = $desconectados = 0;

        while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $usrDesc  = NULL;

            // VERIFICAR TIEMPO DE SESION
            if ($user['estatus'] == 1) {

                $fechaActual        = intVal(date('U'));
                $fechaUltSesion     = intVal($user['ult_sesion']);
                $tiempoDesconectado = intVal($fechaActual - $fechaUltSesion);

                // ACTUALIZAR ESTATUS DE SESION DEL USUARIO EN CASO DE LLEVAR MAS DE 5MIN SIN ACTIVIDAD
                # la actividad la genera el timer al final de cada Dashboard
                if ($tiempoDesconectado > 300) {

                    try {
                        // En caso de haber cerrado la app de forma incorrecta y dejar la sesion activa en db
                        $stmt_st = $conn->prepare("UPDATE usuarios SET estatus = 0 WHERE usuario = '{$user['usuario']}'");
                        $stmt_st->execute();

                        $usrDesc = 0;
                        $desconectados++;
                    } catch (PDOException $e) {

                        $this->exception = $e->getMessage();
                        Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
                    }
                }

                $conectados++;
            } else {

                $desconectados++;
            }

            // VERIFICAR HAY MENSAJES DE OTROS USUARIOS QUE NO HAYAN SIDO LEIDOS
            try {

                $stmt_nl = $conn->prepare("SELECT id_chat FROM interchat WHERE emisor = '{$user['usuario']}' AND receptor = '{$_SESSION['usuario']}' AND leido = 0");
                $stmt_nl->execute();
                
                $stmt_rt = $conn->prepare("SELECT id_chat FROM interchat WHERE emisor = 'root' AND receptor = '{$_SESSION['usuario']}' AND leido = 0");
                $stmt_rt->execute();
                
            } catch (PDOException $e) {

                $this->exception = $e->getMessage();
                Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
            }

            // Msjs no leidos de otros usuarios
            $temp = $stmt_nl->fetch(PDO::FETCH_ASSOC);

            if ($temp) {
                $msjsNoLeidos = true;
            } else {
                $msjsNoLeidos = false;
            }

            $temp_rt = $stmt_rt->fetch(PDO::FETCH_ASSOC);

            if ($temp_rt) {
                $noLeidosRt = true;
            } else {
                $noLeidosRt = false;
            }

            // PREPARAR DATOS DEL USUARIO PARA ENVARLOS
            $usuario = [];
            $usuario['nombre']   = $user['nombre'];
            $usuario['usuario']  = $user['usuario'];
            $usuario['locacion'] = $user['locacion'];
            $usuario['depto']    = $user['depto'];
            $usuario['estatus']  = isset($usrDesc) ? $usrDesc : $user['estatus'];
            $usuario['noLeidos'] = $msjsNoLeidos;
            $usuario['noLeidosRt'] = isset($noLeidosRt) ? $noLeidosRt : NULL;

            // SE CARGA EL USUARIO EN EL ARRAY GENERAL DE USUARIOS
            $usuarios[] = $usuario;

            $totalUsuarios++;
        }

        // ESTADISTICA DE USUARIOS ACTIVOS/INACTIVOS
        $datos['usuariosTotales']   = $totalUsuarios;
        $datos['usuariosActivos']   = $conectados;
        $datos['usuariosInactivos'] = $desconectados;

        array_unshift($usuarios, $datos);

        $this->estatus = true;
        Log::registrar_log('Ver usuarios activos');

        return json_encode($usuarios);
    }

    public function consultar_usuario(): array
    {

        global $conn;

        try {

            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = '{$this->info['id_usuario']}'");
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $row = $stmt->fetch();
            return $rowArray[] = $row;

            $this->estatus = true;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function eliminar_usuario(): void
    {
        global $conn;
        try {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = '{$this->info['id']}'");
            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log("Usuario #{$this->info['id']} eliminado");
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function comprobar_nombre_usuario(array $datos): int
    {
        global $conn;
        try {

            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = '{$datos['usuario']}' ");
            $stmt->execute();
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }

        if ($datos = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return 1;
        }
    }

    public function __destruct()
    {
    }
}
