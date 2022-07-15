<?php

declare(strict_types=1);

/* CLASE TAREA
Gestion de Tareas asignadas a los analistas de sistemas
*/

class Tarea
{
    private $info;
    public $estatus = false;
    public $exception = NULL;

    public function __construct($datos = NULL)
    {
        $this->info = $datos;
    }

    public function registrar_Tarea()
    {
        global $conn;

        try {
            $stmt        = $conn->prepare("INSERT INTO tareas (id_tarea, fecha, descripcion, adjunto, analista, valoracion, estatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $id_tarea    = NULL;
            $fecha       = date('Y-m-d H:i:s');
            $descripcion = htmlspecialchars($this->info['descripcion']);
            $adjunto     = $this->info[0];
            $analista     = $this->info['analista'];
            $valoracion  = $this->info['valoracion'];
            $estatus     = 'Pendiente';

            $stmt->bindParam(1, $id_tarea);
            $stmt->bindParam(2, $fecha);
            $stmt->bindParam(3, $descripcion);
            $stmt->bindParam(4, $adjunto);
            $stmt->bindParam(5, $analista);
            $stmt->bindParam(6, $valoracion);
            $stmt->bindParam(7, $estatus);

            $stmt->execute();

            $this->estatus = true;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
        }
    }

    public function adjuntar_archivo(array $archivo)
    {
        $temp = isset($archivo['archivo']['tmp_name']) ? $archivo['archivo']['tmp_name'] : NULL;

        if ($temp != NULL) {
            $fileName = str_replace(' ', '_', $archivo['archivo']['name']);
            if ($archivo['archivo']['type'] == 'image/jpeg' || $archivo['archivo']['type'] == 'image/png' || $archivo['archivo']['type'] == 'image/bmp' || $archivo['archivo']['type'] == 'image/gif' || $archivo['archivo']['type'] == 'text/plain' || $archivo['archivo']['type'] == 'application/pdf' || $archivo['archivo']['type'] == 'application/msword' || $archivo['archivo']['type'] == 'application/vnd.ms-excel' || $archivo['archivo']['size'] < 3000000) {
                if (move_uploaded_file($temp, 'uploads/' . $fileName)) {
                    $this->info[0] = 'uploads/' . $fileName;
                }
            } else {
                $this->info[0] = 'ERROR: tipo de archivo no admitido';
            }
        } else {
            $this->info[0] = 'ERROR: al adjuntar archivo';
        }
    }

    public function actualizar_tarea()
    {   
        /* 
        Actualizar tareas desde la cuenta de root
        */

        global $conn;

        try {

            $id_tarea    = substr($this->info[0], 3);
            $valoracion  = $this->info['valoracion'];
            $descripcion = addslashes($this->info['descripcion']);

            $stmt_up = $conn->prepare("UPDATE tareas SET valoracion = '$valoracion', descripcion = '$descripcion' WHERE id_tarea = '$id_tarea'");
            $stmt_up->execute();

            $this->estatus = true;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
        }
    }

    public function tomar_tarea(mixed $analista, mixed $id_tarea)
    {
        /*
        Tomar o liberar una tarea desde el dashboard de analista mediante doble clic
        */

        global $conn;

        try {

            $stmt_chk = $conn->prepare("SELECT analista FROM tareas WHERE id_tarea = '$id_tarea'");
            $stmt_chk->execute();
            $tec = $stmt_chk->fetch(PDO::FETCH_ASSOC);

            if ($tec['analista'] == 'Sin asignar') {
                $stmt = $conn->prepare("UPDATE tareas SET analista = '$analista' WHERE id_tarea = '$id_tarea'");
                $stmt->execute();

                $this->estatus = true;
                return 'tareaTomada';
            } else {
                $stmt = $conn->prepare("UPDATE tareas SET analista = 'Sin asignar' WHERE id_tarea = '$id_tarea'");
                $stmt->execute();

                $this->estatus = true;
                return 'tareaLiberada';
            }
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
        }
    }

    public function ultima_tarea()
    {
        /*
        Devuelve el ID de la ultima tarea, esto para precisar las tareas nuevas generadas
        */

        global $conn;

        try {
            $stmt = $conn->prepare("SELECT id_tarea, fecha FROM tareas ORDER BY fecha DESC");
            $stmt->execute();
            $ultimaTarea = $stmt->fetch(PDO::FETCH_ASSOC);
            return $ultimaTarea['id_tarea'];
        } catch (PDOException $e) {
            $this->exception = $e->getMessage();
        }
    }

    public function cambiar_estatus_Tarea(mixed $id_tarea, mixed $estatus)
    {
        global $conn;

        try {

            $id_tarea = substr($id_tarea, 3);
            $stmt_st  = $conn->prepare("UPDATE tareas SET estatus = '$estatus' WHERE id_tarea = '$id_tarea'");
            $stmt_st->execute();

            $this->estatus = true;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
        }
    }


    public function eliminar_Tarea(mixed $id_tarea)
    {   
    
        global $conn;

        try {

            $stmt_del = $conn->prepare("DELETE FROM tareas WHERE id_tarea = '$id_tarea'");
            $stmt_del->execute();

            $this->estatus = true;
        } catch (PDOException $e) {

            $this->exception = $e->getMessage();
        }
    }

    public function __destruct()
    {
    }
}
