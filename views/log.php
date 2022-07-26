<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

?>

<table id="sysLogs" class="table table-bordered align-middle" style="text-align:center; width:100% !important; font-family:monospace; font-size:0.8em;">
    <thead>
        <tr style="background: #505050;color: rgb(255,255,255);">
            <td>ID</td>
            <th>Fecha</th>
            <th>IP</th>
            <th>Usuario</th>
            <th style="max-width:35%">Plataforma/Navegador</th>
            <th>Actividad</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // CARGAR LOGS
        $stmt = $conn->query("SELECT * FROM logs");
        while ($log = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
            <tr>
                <td style="width: 5% !important"><?php echo $log['id'] ?></td>
                <td><?php echo $log['fecha'] ?></td>
                <td><?php echo $log['ip'] ?></td>
                <td><?php echo $log['usuario'] ?></td>
                <td style="max-width:35%"><?php echo $log['plataforma'] ?></td>
                <td><?php echo $log['accion'] ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        // IDIOMA ESPAÑOL PARA DATATABLES
        $("#sysLogs").DataTable({
            "language": {
                "url": "config/dataTableSpanish.json"
            }
        });
    })
</script>