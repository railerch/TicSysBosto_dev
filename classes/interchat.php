<?php

declare(strict_types=1);

/*
CLASE MENSAJE
Gestion de mensajes de chats entre usuarios
*/

class Interchat implements metodos_chats
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
        $id_chat    = $this->info['id_chat'];
        $remitente  = $this->info['emisor'];
        $receptor   = $this->info['receptor'];
        $mensaje    = filter_var($this->info[1], FILTER_SANITIZE_STRING);
        $leido      =  "0";

        try {
            $stmt = $conn->prepare("INSERT INTO interchat (id, fecha, id_chat, emisor, receptor, mensaje, leido) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->bindParam(1, $id);
            $stmt->bindParam(2, $fecha);
            $stmt->bindParam(3, $id_chat);
            $stmt->bindParam(4, $remitente);
            $stmt->bindParam(5, $receptor);
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
            if ($archivo['archivo']['size'] < 100000000) {
                if ($archivo['archivo']['type'] == 'image/jpeg' || $archivo['archivo']['type'] == 'image/png' || $archivo['archivo']['type'] == 'image/bmp' || $archivo['archivo']['type'] == 'image/gif' || $archivo['archivo']['type'] == 'text/plain' || $archivo['archivo']['type'] == 'application/pdf' || $archivo['archivo']['type'] == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || $archivo['archivo']['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                    if (move_uploaded_file($temp, 'uploads/' . $_POST['id_chat'] . $_POST['emisor'] . '_' . $fileName)) {
                        $this->info[0] = 'uploads/' . $_POST['id_chat'] . $_POST['emisor'] . '_' . $fileName;
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
            $stmt = $conn->prepare("SELECT * FROM interchat WHERE id_chat = '$id'");
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

    public function id_chat_comun(string $emisor, string $receptor): string | NULL
    {   
        
        /*
        * Metodo para verificar si hay un chat activo entre el emisor y el receptor
        */

        global $conn;

        try {
            $stmt = $conn->prepare("SELECT id_chat FROM interchat WHERE emisor IN ('$emisor', '$receptor') AND receptor IN ('$emisor', '$receptor')");
            $stmt->execute();
            $id_chat = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return isset($id_chat['id_chat']) ? $id_chat['id_chat'] : NULL;

        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function msjs_no_leidos(string $usuario) : array | NULL
    {
        /*
        * Verificar si el usuario tiene mensajes no leidos de otros usuarios para activar la notificacion visual
        */

        global $conn;

        try {
            $stmt = $conn->prepare("SELECT DISTINCT id_chat FROM interchat WHERE receptor = '$usuario' AND leido = 0");
            $stmt->execute();

            while($chat = $stmt->fetch(PDO::FETCH_ASSOC)){
                $id_chats [] = $chat['id_chat'];
            }
            
            return isset($id_chats) ? $id_chats : NULL;

        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
            Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
        }
    }

    public function __destruct()
    {
    }
}
