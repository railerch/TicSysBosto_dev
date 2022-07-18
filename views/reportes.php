<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// CONSULTAR NOMBRES DE ADMINISTRADORES
$stmt_admin = $conn->prepare("SELECT nombre FROM usuarios WHERE nivel = 'admin'");
$stmt_admin->execute();

// CONSULTAR EMPRESAS
$stmt_loc = $conn->prepare("SELECT descripcion FROM miscelaneos WHERE tipo = 'empresa'");
$stmt_loc->execute();

?>
<div style="background: #5b5b5b;padding: 1em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-line-chart" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Reportes</h1>
    <hr style="background: #969696;">
    <p>Seleccione el tipo de reporte que desea obtener, a continuación indique el rango de fecha del cual desea
        obtener información y luego haga clic en "Generar reporte".&nbsp;</p>
    <form id="formularioReporte">
        <div class="d-lg-flex justify-content-lg-" style="margin-bottom: 1em;">
            <fieldset>
                <legend>Tipo de reporte</legend>
                <?php if ($_SESSION['nivel'] == 'admin') { ?>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="analista"><label class="form-check-label" for="formCheck-1">Tickets por analista</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="tareas"><label class="form-check-label" for="formCheck-1">Tareas por analista</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="empresa"><label class="form-check-label" for="formCheck-2">Tickets por empresa</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="global"><label class="form-check-label" for="formCheck-2">Reporte general</label>
                    </div>
                <?php } else if ($_SESSION['nivel'] == 'gerente' || $_SESSION['nivel'] == 'analista') { ?>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="TotalTicketsDepto"><label class="form-check-label" for="formCheck-1">Tickets
                            totales</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="empresa"><label class="form-check-label" for="formCheck-1">Tickets por empresa</label>
                    </div>
                <?php } ?>
            </fieldset>
        </div>
        <div id="data">
            <div id="tech">
                <select id="analista" class="form-control" name="analista">
                    <option style="color:#aaa" value="">Seleccione el analista</option>
                    <?php while ($analista = $stmt_admin->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $analista['nombre'] ?>"><?php echo $analista['nombre'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <div id="empresa-div">
                <select id="empresa" class="form-control" name="empresa">
                    <option style="color:#aaa" value="">Seleccione la empresa</option>
                    <?php while ($empresa = $stmt_loc->fetch(PDO::FETCH_ASSOC)) { ?>
                        <option value="<?php echo $empresa['descripcion'] ?>"><?php echo $empresa['descripcion'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <fieldset class="d-md-flex justify-content-md-start align-items-md-center" style="padding-top: 0.5em;margin-bottom: 1em;">
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

        // OCULTAR INPUT TECNICO/EMPRESA
        $("#data").hide();
        $("#tech").hide();
        $("#empresa-div").hide();

        $("input[value=analista]").focus(function() {
            $("#data").show();
            $("#tech").show();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "analista");
            localStorage.setItem("inputOK", 0)
        })

        $("input[value=tareas]").focus(function() {
            $("#data").show();
            $("#tech").show();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "tareas");
            localStorage.setItem("inputOK", 0)
        })

        $("input[value=empresa]").focus(function() {
            $("#data").show();
            $("#tech").hide();
            $("#empresa-div").show();
            $("#generarReporte").attr("data-tipo", "empresa");
            localStorage.setItem("inputOK", 0)
        })

        $("input[value=TotalTicketsDepto]").focus(function() {
            $("#data").show();
            $("#tech").hide();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "TotalTicketsDepto");
            localStorage.setItem("inputOK", 1)
        })

        $("input[value=global]").focus(function() {
            $("#data").show();
            $("#tech").hide();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "global");
            localStorage.setItem("inputOK", 1)
        })

        // GENERAR REPORTE
        $("#generarReporte").click(function() {

            // OBTENER EL TIPO DE REPORTE
            var tipo = $("#generarReporte").attr("data-tipo");
            console.log("TIPO REPORTE: " + tipo);

            // VALIDAR SELECCIONES
            if (tipo == "analista" || tipo == "tareas") {
                <?php echo validar_selecciones("analista", "") ?>
            } else if (tipo == "empresa") {
                <?php echo validar_selecciones("empresa", "") ?>
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