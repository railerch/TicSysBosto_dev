<?php

declare(strict_types=1);

/* CLASE TICKET
Gestion de tickets de usuario
*/

class Ticket
{

    private $info;
    public $estatus = false;
    public $exception = NULL;

    public function __construct($datos)
    {
        $this->info = $datos;
    }

    public function registrar_ticket(): void
    {
        global $conn;

        try {
            $stmt       = $conn->prepare("INSERT INTO tickets (id_ticket, fecha, locacion, persona, usuario, area, solicitud, descripcion, prioridad, tecnico, estatus, comentarios) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $id_ticket  = NULL;
            $fecha      = date('Y-m-d H:i:s');
            $locacion   = isset($this->info['locacion']) ? $this->info['locacion'] : $_SESSION['locacion'];
            $persona    = isset($this->info['persona']) ? $this->info['persona'] : $_SESSION['nombre'];

            if (isset($this->info['persona'])) {
                // BUSCAR USUARIO EN CASO DE QUE EL TICKET LO HAYA CREADO UN TECNICO
                $nombre    = $this->info['persona'];
                $stmt_usr  = $conn->prepare("SELECT usuario FROM usuarios WHERE nombre = '$nombre'");
                $stmt_usr->execute();
                $usuario   = $stmt_usr->fetch(PDO::FETCH_ASSOC);
                $usrTmp    = $usuario['usuario'];
            }

            $usuario    = isset($usrTmp) ? $usrTmp : $_SESSION['usuario'];
            $area       = $this->info['area'];
            $solicitud  = $this->info['solicitud'];

            if ($this->info['descripcion'] != "") {
                $descripcion = $this->info['descripcion'];
            } else {
                $descripcion = 'Sin descripción';
            }

            $prioridad = $this->info['prioridad'];
            $tecnico   = NULL;

            $estatus     = 'abierto';
            $comentarios = NULL;

            $stmt->bindParam(1, $id_ticket);
            $stmt->bindParam(2, $fecha);
            $stmt->bindParam(3, $locacion);
            $stmt->bindParam(4, $persona);
            $stmt->bindParam(5, $usuario);
            $stmt->bindParam(6, $area);
            $stmt->bindParam(7, $solicitud);
            $stmt->bindParam(8, $descripcion);
            $stmt->bindParam(9, $prioridad);
            $stmt->bindParam(10, $tecnico);
            $stmt->bindParam(11, $estatus);
            $stmt->bindParam(12, $comentarios);

            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log('Nuevo ticket registrado');
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function asignar_ticket(string $tecnico): void
    {
        global $conn;

        try {
            $stmt = $conn->prepare("UPDATE tickets SET tecnico = '$tecnico' WHERE id_ticket = '{$this->info['id_ticket']}'");
            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log("Ticket #{$this->info['id_ticket']} atendido");
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function tecnico_asignado(): string
    {
        global $conn;
        $tecnico = 'Test';
        try {
            $stmt = $conn->prepare("SELECT tecnico FROM tickets WHERE id_ticket = '{$this->info['id_ticket']}'");
            $stmt->execute();
            $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->estatus = true;
        return isset($tecnico['tecnico']) ? $tecnico['tecnico'] : '<span style="color:orange">Aun sin atender!</span>';
        } catch (PDOException $e) {

            return '<span style="color: red">ERROR, al consultar el técnico asignado!</span>';
            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function listar_tickets(): array
    {
        global $conn;

        try {
            $stmt = $conn->prepare("SELECT * FROM tickets");
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $row = $stmt->fetch();
            $rowArray[] = $row;

            $this->estatus = true;
            return $rowArray;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public static function contar_tickets(string $departamento): int
    {
        global $conn;
        try {

            $depto = "AND area = '$departamento'";

            $stmt = $conn->prepare("SELECT count(id_ticket) AS total FROM tickets WHERE estatus = 'abierto' $depto");
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC);

            return intval($total['total']);
        } catch (PDOException $e) {

            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $e->getMessage());
        }
    }

    public function cambiar_estatus_ticket(string $estatus): void
    {
        global $conn;

        switch ($estatus) {
            case 'espera':
                try {

                    $id = $this->info['id_ticket'];
                    $stmt = $conn->prepare("UPDATE tickets SET estatus = 'espera' WHERE id_ticket = '$id'");
                    $stmt->execute();

                    $this->estatus = 'espera';
                    Log::registrar_log("Ticket #{$id} puesto en espera");
                } catch (PDOException $e) {

                    $this->exception = $e->getMessage();
                    Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
                }

                break;

            case 'abierto':

                try {
                    $id = $this->info['id_ticket'];
                    $stmt = $conn->prepare("UPDATE tickets SET estatus = 'abierto' WHERE id_ticket = '$id'");
                    $stmt->execute();

                    $_SESSION['avisos'] = "Ticket #{$id} movido a la bandeja de entrada";

                    $this->estatus = 'abierto';
                    Log::registrar_log("Ticket #{$id} movido a la bandeja de entrada");
                } catch (PDOException $e) {

                    $this->exception = $e->getMessage();
                    Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
                }

                break;
        }
    }

    public function cerrar_ticket(): void
    {
        global $conn;

        try {
            $tecnico    = $this->info['tecnico'];
            $estatus    = isset($this->info['estatus']) ? $this->info['estatus'] : "estatus";
            $comentario = $this->info['comentario'];
            $id         = $this->info['id'];

            $stmt = $conn->prepare("UPDATE tickets SET tecnico = '$tecnico', estatus = '$estatus', comentarios = '$comentario' WHERE id_ticket = '$id'");
            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log("Ticket #{$_GET['id']} cerrado");
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function enviar_ticket_papelera(): void
    {
        global $conn;

        if ($_SESSION['nivel'] == 'tecnico') {
            $coment = "Ticket eliminado por el técnico";
        } else {
            $coment = "Ticket eliminado por el usuario";
        }

        try {
            $stmt = $conn->prepare("UPDATE tickets SET estatus = 'eliminado', comentarios = '$coment' WHERE id_ticket = '{$this->info['id']}'");
            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log("Ticket #{$this->info['id']} enviado a la papelera");
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function eliminar_ticket(): void
    {
        global $conn;

        try {
            $stmt = $conn->prepare("DELETE FROM tickets WHERE id_ticket = '{$this->info['id']}'");
            $stmt->execute();

            $this->estatus = true;
            Log::registrar_log("Ticket #{$this->info['id']} eliminado");
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function __destruct()
    {
    }
}
