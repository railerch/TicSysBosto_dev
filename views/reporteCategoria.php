<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// CREAR LOG
Log::registrar_log('Reporte de tickets por categoria: ' . $_POST['categoria']);

// DATOS PARA EL REPORTE
$categoria    = isset($_POST['categoria']) ? $_POST['categoria'] : NULL;
if ($_SESSION['nivel'] == 'admin') {
    $empresa = 'empresa';
} else {
    $empresa = '\'' . $_SESSION['empresa'] . '\'';
}

$depto        = $_SESSION['depto'];
$fechaInicial = isset($_POST['fechaInicial']) ? $_POST['fechaInicial'] . ' 00:00:00' : "2020-01-01 00:00:00";
$fechaFinal   = isset($_POST['fechaFinal']) ? $_POST['fechaFinal'] . ' 23:59:59' : date("Y-m-d 23:59:59");

// PORCENTAJE DE INCIDENCIAS CON RESPECTO A OTROS DEPTOS
$stmtTicDepto = $conn->query("SELECT empresa, depto, estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND empresa = $empresa AND categoria = '$categoria' AND estatus <> 'eliminado'");

$abiertos = $espera = $preCierre = $cerrados = 0;
$incidencias = [];
$ticketsTotales = 0;
$porcTicket = 0;

while ($incDepto = $stmtTicDepto->fetch(PDO::FETCH_ASSOC)) {
    // Ticket por depto

    // Total por estatus
    @$incidencias['<b>' . $incDepto['empresa'] . '</b>' . ' - ' . $incDepto['depto']] += 1;
    switch ($incDepto['estatus']) {
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

    // Tickets totales
    $ticketsTotales++;
}

// PORCENTAJE POR TICKET
if ($ticketsTotales > 0) {
    $porcTicket = 100 / $ticketsTotales;
}

?>

<style>
    #ticketsCategoria {
        display: flex;
        justify-content: space-around;
        margin-bottom: 1em;
    }

    #ticketsCategoria div {
        width: 30%;
    }

    #ticketsCategoria table {
        color: #d7d7d7;
    }

    #ticketsCategoria table tr td:first-child {
        text-align: right;
    }

    #ticketsCategoria table tr td:last-child {
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
        <i class="fa fa-user-circle-o" style="font-size: 5vw;margin-right: 0.3em;"></i>
        <h1 class="d-inline-block">Reporte: <span style="font-weight:lighter">Tickets por categoría</span></h1>
    </div>
    <h5 id="fechaReporte">
        <!-- FECHA DEL REPORTE -->
    </h5>
    <hr style="background: #969696; margin-top:1em;">
    <p style="text-align:right">
        <?php echo '<b>Periodo:</b> ' . $_POST['fechaInicial'] . ' <b>al</b> ' . $_POST['fechaFinal'] ?></p>

    <h3><?php echo strtoupper($_SESSION['empresa']) . ' - ' . $_SESSION['depto'] ?> [<span style="font-weight:lighter"><?php echo $categoria ?></span>]</h3>
    <hr style="background: #969696; margin-top:1em;">
    <div id="ticketsCategoria">
        <table>
            <tr>
                <td><b>Abiertos:</b></td>
                <td><?php echo isset($abiertos) ? $abiertos : 0 ?></td>
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
    <div id="historial" class="table-striped" style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="text-align: center;background: #505050;color: rgb(255,255,255);">
                    <th>ID</th>
                    <th>Emisor</th>
                    <th>Tickets totales</th>
                    <th>Nvl/incidencias</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ID DE FILA EN TABLA
                $cont = 1;

                foreach ($incidencias as $key => $val) {

                ?>

                    <tr class="ticketRow">
                        <td><?php echo $cont ?></td>
                        <td><?php echo $key ?></td>
                        <td style="text-align:center"><?php echo $val ?></td>
                        <td style="text-align:center"><?php echo number_format(($val * $porcTicket), 2, '.') ?>%</td>
                    </tr>

                <?php $cont++;
                } ?>
            </tbody>
        </table>
    </div>

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