<?php 
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// CREAR LOG
Log::registrar_log('Reporte global generado');

// PERIODO DE FECHA PARA EL REPORTE
$fechaInicial = isset($_POST['fechaInicial']) ? $_POST['fechaInicial'].' 00:00:00' : '2020-01-01 00:00:00';
$fechaFinal   = isset($_POST['fechaFinal']) ? $_POST['fechaFinal'].' 23:59:59' : date("Y-m-d 23:59:59");

// CONSULTAR TICKETS GLOBALES DEL PERIODO
$stmt_st = $conn->prepare("SELECT tecnico, estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND area = 'Sistemas' AND estatus <> 'eliminado'");
$stmt_st->execute();

$abiertosG = $esperaG = $preCierreG = $cerradosG = 0;
$sinTecnico = NULL;

while($ticket = $stmt_st->fetch(PDO::FETCH_ASSOC)){
    
    // OMITIR LOS TICKETS DEL ROOT
    if($ticket['tecnico'] != 'root'){
        
        if($ticket['tecnico'] == NULL){
            $sinTecnico++;
        }
        
        switch($ticket['estatus']){
            case 'abierto':
                $abiertosG++;
                break;
            case 'espera':
                $esperaG++;
                break;
            case 'precierre':
                $preCierreG++;
                break;
            case 'cerrado':
                $cerradosG++;
                break;
        }
    }
    
}

// TOTAL TICKETS
$ticketsGlobales = $abiertosG + $esperaG + $preCierreG + $cerradosG;

// CONSULTAR TAREAS GLOBALES DEL PERIODO
$stmt_stats = $conn->prepare("SELECT tecnico, estatus FROM tareas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal'");
$stmt_stats->execute();

$pendienteG = $colaG = $procesandoG = $finalizadaG = $verificadaG = 0;
$sinAsignar = NULL;

while($tarea = $stmt_stats->fetch(PDO::FETCH_ASSOC)){

    if($tarea['tecnico'] == 'Sin asignar'){
        $sinAsignar++;
    }

    $estatus  = $tarea['estatus'];

    switch($estatus){
        case 'Pendiente':
            $pendienteG++;
            break;
        case 'En espera':
            $colaG++;
            break;
        case 'Procesando':
            $procesandoG++;
            break;
        case 'Finalizada':
            $finalizadaG++;
            break;
        case 'Verificada':
            $verificadaG++;
            break;
    }

}

// TOTAL TAREAS GLOBALES
$totaltareas = $pendienteG + $colaG + $procesandoG + $finalizadaG + $verificadaG;

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
        <i class="fa fa-globe" style="font-size: 5vw;margin-right: 0.3em;"></i>
        <h1 class="d-inline-block">Reporte: <span style="font-weight:lighter">global <?php echo strtolower($_SESSION['depto'])?></span></h1>
    </div>
    <h5 id="fechaReporte">
        <!-- FECHA DEL REPORTE -->
    </h5>
    <hr style="background: #969696; margin-top:1em;">
    <p style="text-align:right">
        <?php echo '<b>Periodo:</b> '.$_POST['fechaInicial'].' <b>al</b> '.$_POST['fechaFinal'] ?></p>

    <!-- ESTADISTICAS DE TICKETS POR TECNICO -->
    <h3>Estadistica de tickets por técnico</h3>
    <hr style="background: #969696; margin-top:1em;">
    <div id="datosTecnico">
        <table>
            <tr>
                <td><b>Abiertos:</b></td>
                <td><?php echo isset($abiertosG) ? $abiertosG : 0?>
                    <?php echo isset($sinTecnico) ? '<sup>(' . $sinTecnico . ' Sin atención )</sup>' : NULL?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>En espera:</b></td>
                <td><?php echo isset($esperaG) ? $esperaG : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Pre-cierre:</b></td>
                <td><?php echo isset($preCierreG) ? $preCierreG : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Cerrados:</b></td>
                <td><?php echo isset($cerradosG) ? $cerradosG : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Tickets totales:</b></td>
                <td><?php echo isset($ticketsGlobales) ? $ticketsGlobales : 0?></td>
            </tr>
        </table>
    </div>
    <hr>
    <div id="historial" class="table-striped"
        style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="text-align: center;background: #353535;color: rgb(255,255,255);">
                    <th>ID</th>
                    <th>Técnico</th>
                    <th>T/abiertos</th>
                    <th>T/espera</th>
                    <th>T/Pre-cierre</th>
                    <th>T/cerrados</th>
                    <th>T/totales</th>
                    <th>Nvl/atención</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // CONSULTAR TECNICOS
                $stmt_tck = $conn->prepare("SELECT * FROM usuarios WHERE nivel = 'tecnico' ORDER BY nombre");
                $stmt_tck->execute();

                // ID DE FILA EN TABLA
                $cont = 1;
                
                while($tecnico = $stmt_tck->fetch(PDO::FETCH_ASSOC)){ 
                //*******************************************************************************
                    // TECNICO EN CURSO
                    $nombre = $tecnico['nombre'];

                    // CONSULTAR TICKETS ESTADISTICAS
                    $stmt_st = $conn->prepare("SELECT estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND tecnico = '$nombre'");
                    $stmt_st->execute();

                    $abiertosT = $esperaT = $preCierreT = $cerradosT = 0;

                    while($ticket = $stmt_st->fetch(PDO::FETCH_ASSOC)){

                        $estatusT  = $ticket['estatus'];
                        
                        switch($estatusT){
                            case 'abierto':
                                $abiertosT++;
                                break;
                            case 'espera':
                                $esperaT++;
                                break;
                            case 'precierre':
                                $preCierreT++;
                                break;
                            case 'cerrado':
                                $cerradosT++;
                                break;
                        }
                    }

                    // TICKETS TOTALES Y PORCENTAJE DE INCIDENCIA
                    $ticketsTec = $abiertosT + $esperaT + $preCierreT + $cerradosT;
                    if($ticketsGlobales > 0){
                        $porcentajeT = (100 / $ticketsGlobales) * $ticketsTec;
                    }else{
                        $porcentajeT = 0;
                    }
                    
                //*******************************************************************************
                ?>

                <tr class="ticketRow" style="text-align:center">
                    <td><?php echo $cont?></td>
                    <td><?php echo $nombre?></td>
                    <td><?php echo $abiertosT?></td>
                    <td><?php echo $esperaT?></td>
                    <td><?php echo $preCierreT?></td>
                    <td><?php echo $cerradosT?></td>
                    <td><?php echo $ticketsTec?></td>
                    <td><?php echo isset($porcentajeT) ? number_format($porcentajeT, 2) : 0?>%</td>
                </tr>

                <?php $cont++; }?>
            </tbody>
        </table>
    </div>

    <!-- ESTADISTICAS DE TAREAS -->
    <h3>Estadistica de tareas por técnico</h3>
    <hr style="background: #969696; margin-top:1em;">
    <div id="datosTecnico">
        <table>
            <tr>
                <td><b>Pendientes:</b></td>
                <td><?php echo isset($pendienteG) ? $pendienteG : 0?>
                    <?php echo isset($sinAsignar) ? '<sup>(' . $sinAsignar . ' Sin atención )</sup>' : NULL?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>En espera:</b></td>
                <td><?php echo isset($colaG) ? $colaG : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>En proceso:</b></td>
                <td><?php echo isset($procesandoG) ? $procesandoG : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Finalizadas:</b></td>
                <td><?php echo isset($finalizadaG) ? $finalizadaG : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Verificadas:</b></td>
                <td><?php echo isset($verificadaG) ? $verificadaG : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Tareas totales:</b></td>
                <td><?php echo isset($totaltareas) ? $totaltareas : 0?></td>
            </tr>
        </table>
    </div>
    <hr>
    <div id="historial" class="table-striped"
        style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="text-align: center;background: #353535;color: rgb(255,255,255);">
                    <th>ID</th>
                    <th>Técnico</th>
                    <th>T/pendientes</th>
                    <th>T/en cola</th>
                    <th>T/en proceso</th>
                    <th>T/finalizadas</th>
                    <th>T/verificadas</th>
                    <th>Puntaje</th>
                    <th>Efectividad</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // CONSULTAR TECNICOS
                $stmt_tsk = $conn->prepare("SELECT * FROM usuarios WHERE nivel = 'tecnico' AND usuario <> 'root' ORDER BY nombre");
                $stmt_tsk->execute();

                // ID DE FILA EN TABLA
                $cont = 1;
                
                while($tecnico = $stmt_tsk->fetch(PDO::FETCH_ASSOC)){ 
                //*******************************************************************************
                    // TECNICO EN CURSO
                    $nombre = $tecnico['nombre'];

                    // CONSULTAR TAREAS ESTADISTICAS
                    $stmt_stats = $conn->prepare("SELECT valoracion, estatus FROM tareas WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND tecnico = '$nombre'");
                    $stmt_stats->execute();

                    $pendiente = $cola = $procesando = $finalizada = $verificada = $efectividad = $puntaje = 0;

                    while($tarea = $stmt_stats->fetch(PDO::FETCH_ASSOC)){
                        
                        $valor    = $tarea['valoracion'];
                        $estatus  = $tarea['estatus'];
                        
                        switch($estatus){
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
                    
                    
                    
                //*******************************************************************************
                ?>

                <tr class="ticketRow" style="text-align:center">
                    <td><?php echo $cont?></td>
                    <td><?php echo $nombre?></td>
                    <td><?php echo $pendiente?></td>
                    <td><?php echo $cola?></td>
                    <td><?php echo $procesando?></td>
                    <td><?php echo $finalizada?></td>
                    <td><?php echo $verificada?></td>
                    <td><?php echo $puntaje?></td>
                    <td><?php echo number_format($efectividad, 2)?>%</td>
                </tr>

                <?php $cont++; }?>
            </tbody>
        </table>
    </div>

    <!-- ESTADITISCAS DE TICKETS POR LOCACION -->
    <h3>Estadistica de tickets por locaciones</h3>
    <div id="historial" class="table-striped"
        style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="text-align: center;background: #353535;color: rgb(255,255,255);">
                    <th>ID</th>
                    <th>Locación</th>
                    <th>T/abiertos</th>
                    <th>T/espera</th>
                    <th>T/Pre-cierre</th>
                    <th>T/cerrados</th>
                    <th>T/totales</th>
                    <th>Nvl/Incidencias</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // CONSULTAR LOCACIONES
                $stmt_L = $conn->prepare("SELECT descripcion FROM miscelaneos WHERE tipo = 'locacion' ORDER BY 'locacion' ");
                $stmt_L->execute();

                // ID DE FILA EN TABLA
                $cont = 1;
                
                while($locacion = $stmt_L->fetch(PDO::FETCH_ASSOC)){ 
                //*******************************************************************************
                    // LOCACION EN CURSO
                    $locacion = $locacion['descripcion'];

                    // CONSULTAR TICKETS DEL PERIODO
                    $stmtLoc = $conn->prepare("SELECT tecnico, estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND estatus <> 'eliminado' AND locacion = '$locacion' AND area = 'Sistemas'");
                    $stmtLoc->execute();

                    $abiertosL = $esperaL = $preCierreL = $cerradosL = 0;

                    while($ticket = $stmtLoc->fetch(PDO::FETCH_ASSOC)){

                        // OMITIR LOS TICKETS DEL ROOT
                        if($ticket['tecnico'] != 'root'){
                            $estatusL = $ticket['estatus'];
                            
                            switch($estatusL){
                                case 'abierto':
                                    $abiertosL++;
                                    break;
                                case 'espera':
                                    $esperaL++;
                                    break;
                                case 'precierre':
                                    $preCierreL++;
                                    break;
                                case 'cerrado':
                                    $cerradosL++;
                                    break;
                            }
                            
                        }
                    }

                    // TICKETS TOTALES Y PORCENTAJE DE INCIDENCIA
                    $ticketsLoc = $abiertosL + $esperaL + $preCierreL + $cerradosL;
                    if($ticketsGlobales > 0){
                        $porcentajeLoc = (100 / $ticketsGlobales) * $ticketsLoc;
                    }
                    
                    if($ticketsLoc != 0){
                //*******************************************************************************
                ?>

                <tr class="ticketRow" style="text-align:center">
                    <td><?php echo $cont?></td>
                    <td><?php echo $locacion?></td>
                    <td><?php echo $abiertosL?></td>
                    <td><?php echo $esperaL?></td>
                    <td><?php echo $preCierreL?></td>
                    <td><?php echo $cerradosL?></td>
                    <td><?php echo $ticketsLoc?></td>
                    <td><?php echo number_format($porcentajeLoc, 2)?>%</td>
                </tr>

                <?php } $cont++; }?>
            </tbody>
        </table>
    </div>

    <!-- BOTONES DEL REPORTE -->
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