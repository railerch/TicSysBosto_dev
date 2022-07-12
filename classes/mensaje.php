<?php
declare(strict_types=1);

/*
CLASE MENSAJE
Gestion de mensajes de chats en tickets de usuarios
*/

class Mensaje implements metodos_chats
{

    public $leido;
    public $info;
    public $estatus = false;
    public $exception = NULL;

    public function __construct($datos = NULL)
    {
        $this->info = $datos;
    }

    public function enviar_mensaje()
    {

        global $conn;

        $id         = NULL;
        $fecha      = date('Y-m-d H:i:s');
        $locacion   = $this->info['locacion'];
        $id_ticket  = $this->info['id_ticket'];
        $remitente  = $this->info['remitente'];
        $mensaje    = filter_var($this->info[1], FILTER_SANITIZE_STRING);
        $leido      =  "0";

        try {
            $stmt = $conn->prepare("INSERT INTO chats (id, fecha, locacion, id_ticket, remitente, mensaje, leido) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->bindParam(1, $id);
            $stmt->bindParam(2, $fecha);
            $stmt->bindParam(3, $locacion);
            $stmt->bindParam(4, $id_ticket);
            $stmt->bindParam(5, $remitente);
            $stmt->bindParam(6, $mensaje);
            $stmt->bindParam(7, $leido);

            $stmt->execute();

            $this->estatus = true;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function adjuntar_archivo(array $archivo)
    {
        $temp = isset($archivo['archivo']['tmp_name']) ? $archivo['archivo']['tmp_name'] : NULL;

        if ($temp != NULL) {
            $fileName = str_replace(' ', '_', $archivo['archivo']['name']);
            if ($archivo['archivo']['size'] < 25000000) {
                if ($archivo['archivo']['type'] == 'image/jpeg' || $archivo['archivo']['type'] == 'image/png' || $archivo['archivo']['type'] == 'image/bmp' || $archivo['archivo']['type'] == 'image/gif' || $archivo['archivo']['type'] == 'text/plain' || $archivo['archivo']['type'] == 'application/pdf' || $archivo['archivo']['type'] == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || $archivo['archivo']['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                    if (move_uploaded_file($temp, 'uploads/' . $_POST['id_ticket'] . $_POST['remitente'] . '_' . $fileName)) {
                        $this->info[0] = 'uploads/' . $_POST['id_ticket'] . $_POST['remitente'] . '_' . $fileName;
                    }
                } else {
                    $this->info[0] = 'ERROR: tipo de archivo no admitido';
                }
            } else {
                $this->info[0] = 'ERROR: tamaño de archivo excedido';
            }
        } else {
            $this->info[0] = 'ERROR: al adjuntar archivo';
        }
    }

    public function recuperar_mensajes($id): array
    {
        global $conn;
        $mensajes = [];

        try {
            $stmt = $conn->prepare("SELECT * FROM chats WHERE id_ticket = '$id'");
            $stmt->execute();
            while ($msj = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mensajes[] = $msj;
            };

            return $mensajes;
            
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function comprobar_actividad_chat($id_ticket)
    {
        /*
        Verificar si un chat tiene mensajes para ocultar el boton de eliminar en caso de que la 
        pagina no haya sido actualizada.
        */

        global $conn;

        try {
            $stmt = $conn->prepare("SELECT count(id_ticket) AS total FROM chats WHERE id_ticket = '$id_ticket'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['total'] > 0) {
                $this->info =  "T" . $id_ticket;
            } else {
                $this->info = "F" . $id_ticket;
            }

            $this->estatus = true;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function __destruct()
    {
    }
}
