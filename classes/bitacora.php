<?php

declare(strict_types=1);

/* CLASE BITACORA
* Gestion de bitacoras de resolucion de tickets
*/

class Bitacora
{

    private $info;
    public $estatus = false;
    public $precierre = false;
    public $exception = NULL;

    public function __construct(array $datos)
    {
        $this->info = $datos;
    }

    public function registrar_bitacora(): void
    {
        global $conn;

        if (@$this->info['preCierre']) {

            try {

                $stmt = $conn->prepare("UPDATE tickets SET estatus = 'precierre', comentarios = 'precierre de usuario' WHERE id_ticket = '{$this->info['id']}'");
                $stmt->execute();

                $this->precierre = true;
                Log::registrar_log("Ticket #{$this->info['id']} preCerrado");
            } catch (PDOException $e) {

                $this->exception = $e->getMessage();
                Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
            }
        }

        if ($this->precierre xor $this->info['cierreTec']) {
            $fecha     = date("Y-m-d H:i:s");
            $id_ticket = $this->info['id'];
            $usuario   = $this->info['nombreUsr'];
            $tecnico   = $this->info['tecnico'];
            $solucion  = $this->info['solucion'];

            try {

                $stmt_bit = $conn->prepare("INSERT INTO bitacora (id, fecha, id_ticket, usuario, tecnico, solucion) VALUES (NULL, '$fecha', '$id_ticket', '$usuario', '$tecnico', '$solucion')");
                $stmt_bit->execute();

                $this->estatus = true;
                Log::registrar_log("Bitacora de ticket #{$_GET['id']} registrada");
            } catch (PDOException $e) {

                $this->exception = $e->getMessage();
                Log::registrar_log('ERROR: Metodo: ' . __FUNCTION__ . ' | Clase: ' . __CLASS__ . ' | ' . $this->exception);
            }
        }
    }

    public function __destruct()
    {
    }
}
