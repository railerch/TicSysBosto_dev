<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// CREAR LOG
Log::registrar_log("Reporte de tickets de {$_SESSION['depto']} generado");

// PERIODO DE FECHA PARA EL REPORTE
$fechaInicial = isset($_POST['fechaInicial']) ? $_POST['fechaInicial'] . ' 00:00:00' : "2020-01-01 00:00:00";
$fechaFinal   = isset($_POST['fechaFinal']) ? $_POST['fechaFinal'] . ' 23:59:59' : date("Y-m-d 23:59:59");

// CONSULTAR TICKETS GLOBALES DEL PERIODO PARA EL DEPTO INDICADO
$area = $_SESSION['depto'];

$stmt_st = $conn->prepare("SELECT analista, estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND depto_receptor = '$area' AND estatus <> 'eliminado'");
$stmt_st->execute();

$abiertos = $espera = $preCierre = $cerrados = 0;
$sinTecnico = NULL;

while ($ticket = $stmt_st->fetch(PDO::FETCH_ASSOC)) {

    if ($ticket['analista'] == NULL) {
        $sinTecnico++;
    }

    switch ($ticket['estatus']) {
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

        h1 span {
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

<div id="docReporte" style="background: #5b5b5b; padding: 0.5em; border-radius: 1em; box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <div id="locDiv">
        <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
        <h1 class="d-inline-block">Reporte: <span style="font-weight:lighter">Tickets por empresa</span></h1>
    </div>
    <h5 id="fechaReporte">
        <!-- FECHA DEL REPORTE -->
    </h5>
    <hr style="background: #969696; margin-top:1em;">
    <p style="text-align:right">
        <?php echo '<b>Periodo:</b> ' . $_POST['fechaInicial'] . ' <b>al</b> ' . $_POST['fechaFinal'] ?></p>

    <!-- ESTADISTICAS DE TICKETS POR TECNICO -->
    <h3><?php echo strtoupper($_SESSION['empresa']) . ' - ' . $_SESSION['depto'] ?></h3>
    <hr style="background: #969696; margin-top:1em;">
    <div id="datosTecnico">
        <table>
            <tr>
                <td><b>Abiertos:</b></td>
                <td><?php echo isset($abiertos) ? $abiertos : 0 ?>
                    <?php echo isset($sinTecnico) ? '<sup>(' . $sinTecnico . ' Sin atención )</sup>' : NULL ?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>En espera:</b></td>
                <td><?php echo isset($espera) ? $espera : 0 ?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Pre-cierre:</b></td>
                <td><?php echo isset($preCierre) ? $preCierre : 0 ?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Cerrados:</b></td>
                <td><?php echo isset($cerrados) ? $cerrados : 0 ?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Tickets totales:</b></td>
                <td><?php echo isset($ticketsTotales) ? $ticketsTotales : 0 ?></td>
            </tr>
        </table>
    </div>
    <hr>
    <!-- ESTADITISCAS DE TICKETS POR EMPRESA -->
    <div id="historial" class="table-striped" style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="text-align: center;background: #505050;color: rgb(255,255,255);">
                    <th>ID</th>
                    <th>Empresa</th>
                    <th>T/abiertos</th>
                    <th>T/espera</th>
                    <th>T/Pre-cierre</th>
                    <th>T/cerrados</th>
                    <th>T/totales</th>
                    <th>Nvl/incidencias</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // CONSULTAR EMPRESAS
                $stmt_E = $conn->query("SELECT descripcion FROM miscelaneos WHERE tipo = 'empresa' ORDER BY 'descripcion' ");

                // ID DE FILA EN TABLA
                $cont = 1;

                while ($row = $stmt_E->fetch(PDO::FETCH_ASSOC)) {
                    //*******************************************************************************
                    // EMPRESA EN CURSO
                    $empresa = $row['descripcion'];
                    $area    = $_SESSION['depto'];

                    // CONSULTAR TICKETS DEL PERIODO
                    $stmtEmp = $conn->query("SELECT estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND empresa = '$empresa' AND depto_receptor = '$area' AND estatus <> 'eliminado'");

                    $abiertosE = $esperaE = $preCierreE = $cerradosE = 0;

                    while ($ticket = $stmtEmp->fetch(PDO::FETCH_ASSOC)) {
                        switch ($ticket['estatus']) {
                            case 'abierto':
                                $abiertosE++;
                                break;
                            case 'espera':
                                $esperaE++;
                                break;
                            case 'precierre':
                                $preCierreE++;
                                break;
                            case 'cerrado':
                                $cerradosE++;
                                break;
                        }
                    }

                    // PORCENTAJE DE TICKETS TOMADOS
                    $ticketsEmp = $abiertosE + $esperaE + $preCierreE + $cerradosE;
                    if ($ticketsTotales > 0) {
                        $porcentajeEmp = (100 / $ticketsTotales) * $ticketsEmp;
                    } else {
                        $porcentajeEmp = 0;
                    }

                    if ($ticketsEmp != 0) {
                        //*******************************************************************************
                ?>

                        <tr class="ticketRow" style="text-align:center">
                            <td><?php echo $cont ?></td>
                            <td><?php echo $empresa ?></td>
                            <td><?php echo $abiertosE ?></td>
                            <td><?php echo $esperaE ?></td>
                            <td><?php echo $preCierreE ?></td>
                            <td><?php echo $cerradosE ?></td>
                            <td><?php echo $ticketsEmp ?></td>
                            <td><?php echo number_format($porcentajeEmp, 2) ?>%</td>
                        </tr>

                <?php $cont++;
                    }
                } ?>
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

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

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