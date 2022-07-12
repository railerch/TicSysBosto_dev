<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// CONSULTAR NOMBRES DE ADMINISTRADORES
$stmt_admin = $conn->prepare("SELECT nombre FROM usuarios WHERE nivel = 'tecnico'");
$stmt_admin->execute();

// CONSULTAR LOCACIONES
$stmt_loc = $conn->prepare("SELECT descripcion FROM miscelaneos WHERE tipo = 'locacion'");
$stmt_loc->execute();

?>
<div
    style="background: #5b5b5b;padding: 1em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-line-chart" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Reportes</h1>
    <hr style="background: #969696;">
    <p>Seleccione el tipo de reporte que desea obtener, a continuación indique el rango de fecha del cual desea
        obtener información y luego haga clic en "Generar reporte".&nbsp;</p>
    <form id="formularioReporte">
        <div class="d-lg-flex justify-content-lg-" style="margin-bottom: 1em;">
            <fieldset>
                <legend>Tipo de reporte</legend>
                <?php if ($_SESSION['nivel'] == 'tecnico') {?>
                <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte"
                        value="tecnico"><label class="form-check-label" for="formCheck-1">Tickets por técnico</label>
                </div>
                <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte"
                        value="tareas"><label class="form-check-label" for="formCheck-1">Tareas por técnico</label>
                </div>
                <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte"
                        value="locacion"><label class="form-check-label" for="formCheck-2">Tickets por locación</label>
                </div>
                <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte"
                        value="global"><label class="form-check-label" for="formCheck-2">Reporte general</label>
                </div>
                <?php }else if ($_SESSION['nivel'] == 'gerente') {?>
                <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte"
                        value="TotalTicketsDepto"><label class="form-check-label" for="formCheck-1">Tickets
                        totales</label>
                </div>
                <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte"
                        value="locacion"><label class="form-check-label" for="formCheck-1">Tickets por
                        tienda</label>
                </div>
                <?php }?>
            </fieldset>
        </div>
        <div id="data">
            <div id="tech">
                <select id="tecnico" class="form-control" name="tecnico">
                    <option style="color:#aaa" value="">Seleccione el técnico</option>
                    <?php while($tecnico = $stmt_admin->fetch(PDO::FETCH_ASSOC)){?>
                    <option value="<?php echo $tecnico['nombre']?>"><?php echo $tecnico['nombre']?></option>
                    <?php }?>
                </select>
            </div>

            <div id="location">
                <select id="locacion" class="form-control" name="locacion">
                    <option style="color:#aaa" value="">Seleccione la ubicación</option>
                    <?php while($ubicacion = $stmt_loc->fetch(PDO::FETCH_ASSOC)){?>
                    <option value="<?php echo $ubicacion['descripcion']?>"><?php echo $ubicacion['descripcion']?>
                    </option>
                    <?php }?>
                </select>
            </div>

            <fieldset class="d-md-flex justify-content-md-start align-items-md-center"
                style="padding-top: 0.5em;margin-bottom: 1em;">
                <legend>Rango de fecha del reporte</legend>
                <label><strong>Desde</strong></label>
                <input id="fechaInicial" class="form-control" name="fechaInicial" type="date" style="width: 20%;">
                <label><strong>Hasta</strong></label>
                <input id="fechaFinal" class="form-control" name="fechaFinal" type="date" style="width: 20%;">
            </fieldset>
        </div>
        <button id="generarReporte" class="btn btn-primary btn-block" type="button" style="margin: 0.5em;">Generar
            reporte</button>
    </form>
</div>


<script>
$(document).ready(() => {

    // ESTABLECER LA PAGINA ACTUAL
    sessionStorage.setItem("pagina_actual", "views/reportes.php");

    // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
    localStorage.clear();

    // OCULTAR INPUT TECNICO/LOCACION
    $("#data").hide();
    $("#tech").hide();
    $("#location").hide();

    $("input[value=tecnico]").focus(function() {
        $("#data").show();
        $("#tech").show();
        $("#location").hide();
        $("#generarReporte").attr("data-tipo", "tecnico");
        localStorage.setItem("inputOK", 0)
    })

    $("input[value=tareas]").focus(function() {
        $("#data").show();
        $("#tech").show();
        $("#location").hide();
        $("#generarReporte").attr("data-tipo", "tareas");
        localStorage.setItem("inputOK", 0)
    })

    $("input[value=locacion]").focus(function() {
        $("#data").show();
        $("#tech").hide();
        $("#location").show();
        $("#generarReporte").attr("data-tipo", "locacion");
        localStorage.setItem("inputOK", 0)
    })

    $("input[value=TotalTicketsDepto]").focus(function() {
        $("#data").show();
        $("#tech").hide();
        $("#location").hide();
        $("#generarReporte").attr("data-tipo", "TotalTicketsDepto");
        localStorage.setItem("inputOK", 1)
    })

    $("input[value=global]").focus(function() {
        $("#data").show();
        $("#tech").hide();
        $("#location").hide();
        $("#generarReporte").attr("data-tipo", "global");
        localStorage.setItem("inputOK", 1)
    })

    // GENERAR REPORTE
    $("#generarReporte").click(function() {

        // OBTENER EL TIPO DE REPORTE
        var tipo = $("#generarReporte").attr("data-tipo");
        console.log("TIPO REPORTE: " + tipo);

        // VALIDAR SELECCIONES
        if (tipo == "tecnico" || tipo == "tareas") {
            <?php echo validar_selecciones("tecnico", "") ?>
        } else if (tipo == "locacion") {
            <?php echo validar_selecciones("locacion", "") ?>
        }

        <?php echo validar_selecciones("fechaInicial", "") ?>
        <?php echo validar_selecciones("fechaFinal", "") ?>

        if (localStorage.getItem("inputOK") >= 3) {
            $.ajax({
                type: "POST",
                url: `views/reporte${tipo}.php`,
                data: $("#formularioReporte").serialize(),
                success: function(data) {
                    $("#contenido").html(data);
                    console.log(data);
                }
            })
        } else {
            console.log("Realice todas las selecciones indicadas para generar el reporte.")
        }

    })


})
</script>