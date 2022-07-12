<?php 
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// CREAR LOG
Log::registrar_log('Reporte de tickets: ' . $_POST['tecnico']);

// DATOS PARA EL REPORTE
$nombre       = isset($_POST['tecnico']) ? $_POST['tecnico'] : NULL;
$fechaInicial = isset($_POST['fechaInicial']) ? $_POST['fechaInicial'].' 00:00:00' : "2020-01-01 00:00:00";
$fechaFinal   = isset($_POST['fechaFinal']) ? $_POST['fechaFinal'].' 23:59:59' : date("Y-m-d 23:59:59");

// CONSULTAR TICKETS GLOBALES DEL PERIODO
$stmtG = $conn->prepare("SELECT tecnico FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND area = 'Sistemas' AND estatus <> 'eliminado'");
$stmtG->execute();

// OMITIR LOS TICKETS DEL ROOT
$tmp = 0;
while($ticket = $stmtG->fetch(PDO::FETCH_ASSOC)){
    if($ticket['tecnico'] != 'root'){
        $tmp++;
    }
}

$ticketsGlobales = $tmp;

// CONSULTAR ESTATUS DE TICKETS
$stmtTec = $conn->prepare("SELECT estatus FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND tecnico = '$nombre'");
$stmtTec->execute();

$abiertos = $espera = $preCierre = $cerrados = 0;

while($ticket = $stmtTec->fetch(PDO::FETCH_ASSOC)){

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

// PORCENTAJE DE TICKETS TOMADOS
$ticketsTec = $abiertos + $espera + $preCierre + $cerrados;
if($ticketsGlobales > 0){
    $porcentaje = (100 / $ticketsGlobales) * $ticketsTec; 
}else{
    $porcentaje = 0;
}

// CONSULTAR TICKETS DE TECNICO
$stmtTic = $conn->prepare("SELECT * FROM tickets WHERE fecha BETWEEN '$fechaInicial' AND '$fechaFinal' AND tecnico = '$nombre' AND estatus <> 'eliminado'");
$stmtTic->execute();

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
        <h1 class="d-inline-block">Reporte: <span style="font-weight:lighter"><?php echo $nombre?></span></h1>
    </div>
    <h5 id="fechaReporte">
        <!-- FECHA DEL REPORTE -->
    </h5>
    <hr style="background: #969696; margin-top:1em;">
    <p style="text-align:right">
        <?php echo '<b>Periodo:</b> '.$_POST['fechaInicial'].' <b>al</b> '.$_POST['fechaFinal'] ?></p>

    <h3>Historial de tickets atendidos</h3>
    <hr style="background: #969696; margin-top:1em;">
    <div id="datosTecnico">
        <table>
            <tr>
                <td><b>Abiertos:</b></td>
                <td><?php echo isset($abiertos) ? $abiertos : 0?></td>
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
                <td><?php echo isset($ticketsTec) ? $ticketsTec : 0?></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><b>Nvl atención:</b></td>
                <td><?php echo isset($porcentaje) ? number_format($porcentaje, 2) : 0?>%</td>
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
                    <th>Fecha</th>
                    <th>Ubicación</th>
                    <th>Departamento</th>
                    <th>Usuario</th>
                    <th>Incidencia</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // ID DE FILA EN TABLA
                $cont = 1;
                
                while($ticket = $stmtTic->fetch(PDO::FETCH_ASSOC)){
                    
                    // DEPARTAMENTO DEL USUARIO
                    $persona = $ticket['persona'];
                    $stmt_dpto = $conn->prepare("SELECT depto FROM usuarios WHERE nombre = '$persona'");
                    $stmt_dpto->execute();
                    $usuario = $stmt_dpto->fetch(PDO::FETCH_ASSOC) ;
                ?>

                <tr class="ticketRow" style="text-align:center">
                    <td><?php echo $cont?></td>
                    <td><?php echo $ticket['fecha']?></td>
                    <td><?php echo $ticket['locacion']?></td>
                    <td><?php if(@$usuario['depto']){
                        echo $usuario['depto'];
                    }else{
                        echo 'N/A';
                    }
                    ?></td>
                    <td><?php echo $ticket['persona']?></td>
                    <td><?php echo $ticket['solicitud']?></td>
                    <td><?php echo $ticket['estatus']?></td>
                </tr>

                <?php $cont++; }?>
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