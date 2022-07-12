<?php 
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// CREAR LOG
Log::registrar_log("Reporte de tickets de {$_SESSION['depto']} generado");

// PERIODO DE FECHA PARA EL REPORTE
$fechaInicial = isset($_POST['fechaInicial']) ? $_POST['fechaInicial'].' 00:00:00' : "2020-01-01 00:00:00";
$fechaFinal   = isset($_POST['fechaFinal']) ? $_POST['fechaFinal'].' 23:59:59' : date("Y-m-d 23:59:59");

// CONSULTAR TICKETS GLOBALES DEL PERIODO
$depto = $_SESSION['depto'];
$stmt_st = $conn->prepare("SELECT tecnico, estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND estatus <> 'eliminado' AND area = '$depto'");
$stmt_st->execute();

$abiertos = $espera = $preCierre = $cerrados = 0;
$sinTecnico = NULL;

while($ticket = $stmt_st->fetch(PDO::FETCH_ASSOC)){

    if($ticket['tecnico'] == NULL){
        $sinTecnico++;
    }
    
    switch($ticket['estatus']){
        case 'abierto':
            $abiertos++;
            break;
        case 'espera':
            $espera++;
            break;
        case 'precierre':
            $preCierre++;
            break;
        case 'cerrado':
            $cerrados++;
            break;
    }
    
}

// TOTAL TICKETS
$ticketsTotales = $abiertos + $espera + $preCierre + $cerrados;

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
        <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
        <h1 class="d-inline-block">Reporte: <span style="font-weight:lighter">tickets totales <?php echo strtolower($_SESSION['depto'])?></span></h1>
    </div>
    <h5 id="fechaReporte">
        <!-- FECHA DEL REPORTE -->
    </h5>
    <hr style="background: #969696; margin-top:1em;">
    <p style="text-align:right">
        <?php echo '<b>Periodo:</b> '.$_POST['fechaInicial'].' <b>al</b> '.$_POST['fechaFinal'] ?></p>
    
    <!-- ESTADISTICAS DE TICKETS POR TECNICO -->
    <h3>Historial de tickets por locaciones</h3>
    <hr style="background: #969696; margin-top:1em;">
    <div id="datosTecnico">
        <table>
            <tr>
                <td><b>Abiertos:</b></td>
                <td><?php echo isset($abiertos) ? $abiertos : 0?>
                    <?php echo isset($sinTecnico) ? '<sup>(' . $sinTecnico . ' Sin atención )</sup>' : NULL?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>En espera:</b></td>
                <td><?php echo isset($espera) ? $espera : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Pre-cierre:</b></td>
                <td><?php echo isset($preCierre) ? $preCierre : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Cerrados:</b></td>
                <td><?php echo isset($cerrados) ? $cerrados : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Tickets totales:</b></td>
                <td><?php echo isset($ticketsTotales) ? $ticketsTotales : 0?></td>
            </tr>
        </table>
    </div>
    <hr>
    <!-- ESTADITISCAS DE TICKETS POR LOCACION -->
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
                    $depto = $_SESSION['depto'];
                    $stmtLoc = $conn->prepare("SELECT tecnico, estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND locacion = '$locacion' AND estatus <> 'eliminado' AND area = '$depto'");
                    $stmtLoc->execute();

                    $abiertosL = $esperaL = $preCierreL = $cerradosL = 0;

                    while($ticket = $stmtLoc->fetch(PDO::FETCH_ASSOC)){
                        
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

                    // PORCENTAJE DE TICKETS TOMADOS
                    $ticketsLoc = $abiertosL + $esperaL + $preCierreL + $cerradosL;
                    if($ticketsTotales > 0){
                        $porcentajeLoc = (100 / $ticketsTotales) * $ticketsLoc;
                    }else{
                        $porcentajeLoc = 0;
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

                <?php $cont++; } }?>
            </tbody>
        </table>
    </div>

    <!-- BOTONES DEL REPORTE -->
    <div id="botones" style="width:80%; margin: 0 auto;">
        <button id="volverReportes" class="btn btn-primary" type="button" style="width:45%;"
            onclick="location.reload()">
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