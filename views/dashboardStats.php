<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// MOSTRAR TICKETS SEGUN EL DEPARTAMENTO
if ($_SESSION['nivel'] == 'gerente') {
    if ($_SESSION['depto'] != 'Sistemas') {
        $area = "WHERE area = '{$_SESSION['depto']}'";
    } else {
        echo '<span style="background-color:red; padding:10px; border-radius:5px;">ERROR: el nivel de usuario GERENTE no corresponde con el departamento asignado.</span>';
        exit();
    }
} else if ($_SESSION['nivel'] == 'tecnico') {
    if ($_SESSION['depto'] == 'Sistemas') {
        $area = "WHERE area = '{$_SESSION['depto']}'";
    } else {
        echo '<span style="background-color:red; padding:10px; border-radius:5px;">ERROR: el nivel de usuario TÉCNICO no corresponde con el departamento asignado.</span>';
        exit();
    }
}

$stmt = $conn->prepare("SELECT fecha, estatus FROM tickets $area AND estatus <> 'eliminado'");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

// CONTADORES TICKETS
$abierto = $espera = $precierre = $cerrado = 0;
// CONTADORES TICKETS POR MES
$ene = $feb = $mar = $abr = $may = $jun = $jul = $ago = $sep = $oct = $nov = $dic = 0;
// CONTADORES TICKETS CERRADOS POR MES
$eneC = $febC = $marC = $abrC = $mayC = $junC = $julC = $agoC = $sepC = $octC = $novC = $dicC = 0;

while ($ticket = $stmt->fetch()) {
    $cerradoDelMes = 0;
    // ESTATUS DE TICKETS
    $estatus = $ticket['estatus'];
    switch ($estatus) {
        case "abierto":
            $abierto++;
            break;
        case "espera":
            $espera++;
            break;
        case "precierre":
            $precierre++;
            break;
        case "cerrado":
            $cerrado++;
            $cerradoDelMes++;
            break;
    }

    // TOTAL TICKETS MENSUALES
    $mes = substr($ticket['fecha'], 5, 2);
    switch ($mes) {
        case '01':
            $ene++;
            $eneC += $cerradoDelMes;
            break;
        case '02':
            $feb++;
            $febC += $cerradoDelMes;
            break;
        case '03':
            $mar++;
            $marC += $cerradoDelMes;
            break;
        case '04':
            $abr++;
            $abrC += $cerradoDelMes;
            break;
        case '05':
            $may++;
            $mayC += $cerradoDelMes;
            break;
        case '06':
            $jun++;
            $junC += $cerradoDelMes;
            break;
        case '07':
            $jul++;
            $julC += $cerradoDelMes;
            break;
        case '08':
            $ago++;
            $agoC += $cerradoDelMes;
            break;
        case '09':
            $sep++;
            $sepC += $cerradoDelMes;
            break;
        case '10':
            $oct++;
            $octC += $cerradoDelMes;
            break;
        case '11':
            $nov++;
            $novC += $cerradoDelMes;
            break;
        case '12':
            $dic++;
            $dicC += $cerradoDelMes;
            break;
    }
}

// TICKETS TOTALES
$totalTickets = $abierto + $espera + $cerrado + $precierre;

?>
<?php
// COMPROBAR USUARIO CONTRA DEPARTAMENTO
if (isset($error)) {
    echo $error;
    // exit();
}
?>
<h1 class="d-inline-block" style="color: #d7d7d7;">Estadisticas</h1>
<hr style="background: #969696;">
<h3 class="d-inline-block" style="color: #d7d7d7;margin-bottom: 0.8em;">Tickets actuales</h3>
<div class="d-flex flex-row flex-wrap justify-content-around">
    <div class="text-right" style="width: 15%;background: #ffffff;border-radius: 10px;padding: 0.5em;padding-right: 1em;padding-left: 1em;">
        <p style="color: #ce02f2;text-align: left;"><i class="fa fa-ticket"></i>&nbsp;Abiertos<br></p>
        <hr>
        <p style="text-align: right;color: #7e7e7e;margin: 0;">Total</p>
        <h3 id="openTicket" style="color: #7e7e7e;"><?php echo $abierto ?></h3>
    </div>
    <div class="text-right totales" style="width: 15%;background: #ffffff;border-radius: 10px;padding: 0.5em;padding-right: 1em;padding-left: 1em;">
        <p style="color: #ce02f2;text-align: left;"><i class="fa fa-hand-paper-o"></i>&nbsp;En espera</p>
        <hr>
        <p style="text-align: right;color: #7e7e7e;margin: 0;">Total</p>
        <h3 id="holdTicket" style="color: #7e7e7e;"><?php echo $espera ?></h3>
    </div>
    <div class="text-right totales" style="width: 15%;background: #ffffff;border-radius: 10px;padding: 0.5em;padding-right: 1em;padding-left: 1em;">
        <p style="color: #ce02f2;text-align: left;"><i class="fa fa-hand-grab-o"></i>&nbsp;Pre-cerrados</p>
        <hr>
        <p style="text-align: right;color: #7e7e7e;margin: 0;">Total</p>
        <h3 id="closedTicket" style="color: #7e7e7e;"><?php echo $precierre ?></h3>
    </div>
    <div class="text-right totales" style="width: 15%;background: #ffffff;border-radius: 10px;padding: 0.5em;padding-right: 1em;padding-left: 1em;">
        <p style="color: #ce02f2;text-align: left;"><i class="fa fa-check"></i>&nbsp;Cerrados</p>
        <hr>
        <p style="text-align: right;color: #7e7e7e;margin: 0;">Total</p>
        <h3 id="closedTicket" style="color: #7e7e7e;"><?php echo $cerrado ?></h3>
    </div>
    <div class="text-right totales" style="width: 15%;background: #ffffff;border-radius: 10px;padding: 0.5em;padding-right: 1em;padding-left: 1em;">
        <p style="color: #ce02f2;text-align: left;"><i class="fa fa-flag-o"></i>&nbsp;Tickets totales</p>
        <hr>
        <p style="text-align: right;color: #7e7e7e;margin: 0;">Total</p>
        <h3 id="closedTicket" style="color: #7e7e7e;"><?php echo $totalTickets ?></h3>
    </div>
</div>

<hr style="background: #969696;margin-top: 1.5em;">

<!-- CHARTS -->
<h3 class="d-inline-block" style="color: #d7d7d7;">Historico de tickets</h3>
<div class="d-flex flex-row flex-wrap justify-content-around">
    <div class="col-12 col-lg-5" style="background: #5b5b5b;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;margin-top: 1em;width: 45%;">
        <h4 style="color: #d7d7d7;">Total tickets mensuales</h4>
        <hr style="background: #969696;">
        <div class="text-warning"><canvas data-bs-chart="{&quot;type&quot;:&quot;bar&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;Enero&quot;,&quot;Febrero&quot;,&quot;Marzo&quot;,&quot;Abril&quot;,&quot;Mayo&quot;,&quot;Junio&quot;,&quot;Julio&quot;,&quot;Agosto&quot;,&quot;Septiembre&quot;,&quot;Octubre&quot;,&quot;Noviembre&quot;,&quot;Diciembre&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;Tickets&quot;,&quot;backgroundColor&quot;:&quot;#ce02f2&quot;,&quot;borderColor&quot;:&quot;&quot;,&quot;borderWidth&quot;:&quot;&quot;,&quot;data&quot;:[&quot;<?php echo isset($ene) ? $ene : 0 ?>&quot;,&quot;<?php echo isset($feb) ? $feb : 0 ?>&quot;,&quot;<?php echo isset($mar) ? $mar : 0 ?>&quot;,&quot;<?php echo isset($abr) ? $abr : 0 ?>&quot;,&quot;<?php echo isset($may) ? $may : 0 ?>&quot;,&quot;<?php echo isset($jun) ? $jun : 0 ?>&quot;,&quot;<?php echo isset($jul) ? $jul : 0 ?>&quot;,&quot;<?php echo isset($ago) ? $ago : 0 ?>&quot;,&quot;<?php echo isset($sep) ? $sep : 0 ?>&quot;,&quot;<?php echo isset($oct) ? $oct : 0 ?>&quot;,&quot;<?php echo isset($nov) ? $nov : 0 ?>&quot;,&quot;<?php echo isset($dic) ? $dic : 0 ?>&quot;]}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:true,&quot;legend&quot;:{&quot;display&quot;:false,&quot;position&quot;:&quot;top&quot;},&quot;title&quot;:{&quot;fontColor&quot;:&quot;#ffffff&quot;},&quot;scales&quot;:{&quot;xAxes&quot;:[{&quot;gridLines&quot;:{&quot;drawBorder&quot;:true},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#d7d7d7&quot;}}],&quot;yAxes&quot;:[{&quot;gridLines&quot;:{&quot;drawBorder&quot;:true},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#d7d7d7&quot;}}]}}}"></canvas></div>
    </div>
    <div class="col-12 col-lg-5" style="background: #5b5b5b;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;margin-top: 1em;width: 45%;">
        <h4 style="color: #d7d7d7;">Tickets resueltos por mes</h4>
        <hr style="background: #969696;">
        <div class="text-warning">
            <canvas data-bs-chart="{&quot;type&quot;:&quot;bar&quot;,&quot;data&quot;:{&quot;labels&quot;:[&quot;Enero&quot;,&quot;Febrero&quot;,&quot;Marzo&quot;,&quot;Abril&quot;,&quot;Mayo&quot;,&quot;Junio&quot;,&quot;Julio&quot;,&quot;Agosto&quot;,&quot;Septiembre&quot;,&quot;Octubre&quot;,&quot;Noviembre&quot;,&quot;Diciembre&quot;],&quot;datasets&quot;:[{&quot;label&quot;:&quot;Tickets&quot;,&quot;backgroundColor&quot;:&quot;#07def3&quot;,&quot;borderColor&quot;:&quot;&quot;,&quot;borderWidth&quot;:&quot;&quot;,&quot;data&quot;:[&quot;<?php echo isset($eneC) ? $eneC : 0 ?>&quot;,&quot;<?php echo isset($febC) ? $febC : 0 ?>&quot;,&quot;<?php echo isset($marC) ? $marC : 0 ?>&quot;,&quot;<?php echo isset($abrC) ? $abrC : 0 ?>&quot;,&quot;<?php echo isset($mayC) ? $mayC : 0 ?>&quot;,&quot;<?php echo isset($junC) ? $junC : 0 ?>&quot;,&quot;<?php echo isset($julC) ? $julC : 0 ?>&quot;,&quot;<?php echo isset($agoC) ? $agoC : 0 ?>&quot;,&quot;<?php echo isset($sepC) ? $sepC : 0 ?>&quot;,&quot;<?php echo isset($octC) ? $octC : 0 ?>&quot;,&quot;<?php echo isset($novC) ? $novC : 0 ?>&quot;,&quot;<?php echo isset($dicC) ? $dicC : 0 ?>&quot;]}]},&quot;options&quot;:{&quot;maintainAspectRatio&quot;:true,&quot;legend&quot;:{&quot;display&quot;:false,&quot;position&quot;:&quot;top&quot;},&quot;title&quot;:{&quot;fontColor&quot;:&quot;#ffffff&quot;},&quot;scales&quot;:{&quot;xAxes&quot;:[{&quot;gridLines&quot;:{&quot;drawBorder&quot;:true},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#d7d7d7&quot;}}],&quot;yAxes&quot;:[{&quot;gridLines&quot;:{&quot;drawBorder&quot;:true},&quot;ticks&quot;:{&quot;fontColor&quot;:&quot;#d7d7d7&quot;}}]}}}"></canvas>
        </div>
    </div>
</div>


<script src="assets/js/chart.min.js"></script>
<script src="assets/js/bs-init.js"></script>

<script type="text/javascript">
    // ESTABLECER LA PAGINA ACTUAL
    sessionStorage.setItem("pagina_actual", "views/dashboardStats.php");
</script>