<?php 
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// CREAR LOG
Log::registrar_log('Reporte de tareas: ' . $_POST['tecnico']);

// DATOS PARA EL REPORTE
$nombre       = isset($_POST['tecnico']) ? $_POST['tecnico'] : NULL;
$fechaInicial = isset($_POST['fechaInicial']) ? $_POST['fechaInicial'].' 00:00:00' : "2020-01-01 00:00:00";
$fechaFinal   = isset($_POST['fechaFinal']) ? $_POST['fechaFinal'].' 23:59:59' : date("Y-m-d 23:59:59");

// CONSULTAR POR DATOS DEL USUARIO
$stmt_0 = $conn->prepare("SELECT nombre FROM usuarios WHERE nombre = '$nombre' ");
$stmt_0->execute();
$tecnico = $stmt_0->fetch(PDO::FETCH_ASSOC);

// CONSULTA PARA LA TABLA DE TAREAS ASIGNADAS
$stmt_tareas = $conn->prepare("SELECT * FROM tareas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND tecnico = '$nombre'");
$stmt_tareas->execute();

// CONSULTA DE TAREAS PARA ENCABEZADO DE ESTADISTICAS
$stmt_stats = $conn->prepare("SELECT tecnico, valoracion, estatus FROM tareas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND tecnico = '$nombre'");
$stmt_stats->execute();

$pendiente = $cola = $procesando = $finalizada = $verificada = $efectividad = $puntaje = 0;

while($tarea = $stmt_stats->fetch(PDO::FETCH_ASSOC)){
    
    $valor    = $tarea['valoracion'];
    
    switch($tarea['estatus']){
        case 'Pendiente':
            $pendiente++;
            break;
        case 'En espera':
            $cola++;
            break;
        case 'Procesando':
            $procesando++;
            break;
        case 'Finalizada':
            $finalizada++;
            break;
        case 'Verificada':
            $verificada++;
            $puntaje += $valor;
            break;
    }
}

// EFECTIVIDAD
$totalTareas = $pendiente + $cola + $procesando + $finalizada + $verificada;

if($totalTareas > 0){
    $efectividad = (100 / $totalTareas) * $verificada;
}else{
    $efectividad = 0;
}

?>

<style>
#datosTecnico {
    display: flex;
    justify-content: space-around;
    margin-bottom: 1em;
}

#datosTecnico div {
    width: 30%;
}

#datosTecnico table {
    color: #d7d7d7;
}

#datosTecnico table tr td:first-child {
    text-align: right;
}

#datosTecnico table tr td:last-child {
    padding-left: 1em;
}

#fechaReporte {
    display: none;
}

@media print {

    * {
        font-size: 12px;
        color: #000;
    }

    body {
        margin: 0 auto;
    }

    div {
        background-color: #fff !important;
    }

    h1 span{
        font-size: 1em;
    }

    #locDiv {
        margin-bottom: 20px;
        text-align: center;
    }

    #topBar div:first-child,
    #sidebar {
        display: none !important;
    }

    #contenido {
        position: absolute;
        top: 150px;
        left: 40px;
        overflow: visible !important;
    }

    #historial {
        overflow: visible !important;
    }

    #botones {
        visibility: hidden;
    }

    #fechaReporte {
        display: block;
        text-align: center !important;
    }
}
</style>

<div id="docReporte"
    style="background: #5b5b5b; padding: 0.5em; border-radius: 1em; box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <div id="locDiv">
        <i class="fa fa-user-circle-o" style="font-size: 5vw;margin-right: 0.3em;"></i>
        <h1 class="d-inline-block">Reporte: <span style="font-weight:lighter"><?php echo $tecnico['nombre']?></span></h1>
    </div>
    <h5 id="fechaReporte">
        <!-- FECHA DEL REPORTE -->
    </h5>
    <hr style="background: #969696; margin-top:1em;">
    <p style="text-align:right">
        <?php echo '<b>Periodo:</b> '.$_POST['fechaInicial'].' <b>al</b> '.$_POST['fechaFinal'] ?></p>

    <h3>Historial de tareas realizadas</h3>
    <hr style="background: #969696; margin-top:1em;">
    <div id="datosTecnico">
        <table>
            <tr>
                <td><b>Pendientes:</b></td>
                <td><?php echo isset($pendiente) ? $pendiente : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>En espera:</b></td>
                <td><?php echo isset($cola) ? $cola : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>En proceso:</b></td>
                <td><?php echo isset($procesando) ? $procesando : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Finalizadas:</b></td>
                <td><?php echo isset($finalizada) ? $finalizada : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Verificadas:</b></td>
                <td><?php echo isset($verificada) ? $verificada : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Tareas totales:</b></td>
                <td><?php echo isset($totalTareas) ? $totalTareas : 0?></td>
            </tr>
        </table>
    </div>
    <hr>
    <div id="historial" class="table-striped"
        style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="text-align: center;background: #353535;color: rgb(255,255,255);">
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 25%;">Tarea</th>
                    <th style="width: 10%;">Valor Pts</th>
                    <th style="width: 10%;">Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // ID DE FILA EN TABLA
                $cont = 1;
                
                while($tarea = $stmt_tareas->fetch(PDO::FETCH_ASSOC)) { 
            ?>

                <tr class="ticketRow" style="text-align:center">
                    <td><?php echo $cont?></td>
                    <td><?php echo $tarea['fecha']?></td>
                    <td style="text-align:left"><?php echo $tarea['descripcion']?></td>
                    <td><?php echo $tarea['valoracion']?></td>
                    <td><?php echo $tarea['estatus']?></td>
                </tr>

                <?php $cont++; }?>
            </tbody>
        </table>
        <span style="display: block; text-align:center; color:#333"><b>EFECTIVIDAD DEL PERIODO:</b>
            <?php echo number_format($efectividad, 2) ?>%</span>
        <span style="display: block; text-align:center; color:#333"><b>PUNTAJE TOTAL:</b> <?php echo $puntaje?>
            Pts</span>
    </div>
    <hr>
    <div id="botones" style="width:80%; margin: 0 auto;">
        <button id="volverReportes" class="btn btn-primary" type="button" style="width:45%;" onclick="location.reload()">
            Atras
        </button>

        <button id="imprimirReporte" class="btn btn-primary" type="button" style="width:45%;">
            Imprimir reporte
        </button>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

    // FECHA DEL REPORTE
    var fecha = new Date();
    $("#fechaReporte").html(`FECHA DE EMISION DEL REPORTE<br>${fecha}`);

    // IMPRIMIR REPORTE
    $("#imprimirReporte").click(function() {
        window.print();
    })

})
</script>