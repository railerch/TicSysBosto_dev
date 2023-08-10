<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

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
                <?php if ($_SESSION['nivel'] == 'admin' || $_SESSION['nivel'] == 'tecnico') { ?>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Analista"><label class="form-check-label">Tickets por analista</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Tareas"><label class="form-check-label">Tareas por analista</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Categoria"><label class="form-check-label">Tickets por categoria</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Depto"><label class="form-check-label">Tickets por departamento</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Empresa"><label class="form-check-label">Tickets por empresa</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Integral"><label class="form-check-label">Reporte integral</label>
                    </div>
                <?php } else if ($_SESSION['nivel'] == 'gerente' || $_SESSION['nivel'] == 'analista') { ?>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Analista"><label class="form-check-label">Tickets por analista</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Categoria"><label class="form-check-label">Tickets por categoria</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Depto"><label class="form-check-label">Tickets por departamento</label>
                    </div>
                    <div class="form-check"><input class="form-check-input reporte" type="radio" name="reporte" value="Empresa"><label class="form-check-label">Tickets por empresa</label>
                    </div>
                <?php } ?>
            </fieldset>
        </div>
        <div id="data">
            <div id="tech-div">
                <select id="analista" class="form-control" name="analista" style="width:50%">
                    <option style="color:#aaa" value="">Seleccione el analista</option>
                    <!-- ANALISTAS DEL DEPTO -->
                </select>
            </div>

            <div id="categoria-div">
                <select id="categoria" class="form-control" name="categoria" style="width:50%">
                    <option style="color:#aaa" value="">Seleccione la categoría</option>
                    <!-- CATEGORIAS DEL DEPTO -->
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

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

<script>
    $(document).ready(() => {

        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/reportes.php");

        // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
        localStorage.clear();

        // OCULTAR INPUT TECNICO/EMPRESA
        $("#data").hide();
        $("#tech-div").hide();
        $("#depto-div").hide();
        $("#empresa-div").hide();
        $("#categoria-div").hide();

        $("input[value=Analista]").focus(function() {
            $("#data").show();
            $("#tech-div").show();
            $("#categoria-div").hide();
            $("#depto-div").hide();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "Analista");

            // Consultar analistas del departamento
            let datosPhp = ["analista", "Seleccione el analista", "analistasDepto"]
            opciones_select(...datosPhp)

            localStorage.clear();
            localStorage.setItem("inputOK", 0);
        })

        $("input[value=Tareas]").focus(function() {
            $("#data").show();
            $("#tech-div").show();
            $("#categoria-div").hide();
            $("#depto-div").hide();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "Tareas");

            // Consultar analistas del departamento
            let datosPhp = ["analista", "Seleccione el analista", "analistasDepto"]
            opciones_select(...datosPhp)

            localStorage.clear();
            localStorage.setItem("inputOK", 0);
        })

        $("input[value=Categoria]").focus(function() {
            $("#data").show();
            $("#tech-div").hide();
            $("#categoria-div").show();
            $("#depto-div").hide();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "Categoria");

            // Consultar categorias del departamento
            let datosPhp = ["categoria", "Seleccione la categoría", "deptoCats"]
            opciones_select(...datosPhp)

            localStorage.clear();
            localStorage.setItem("inputOK", 0);
        })

        $("input[value=Depto]").focus(function() {
            $("#data").show();
            $("#tech-div").hide();
            $("#categoria-div").hide();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "Depto");

            localStorage.clear();
            localStorage.setItem("inputOK", 1);
        })

        $("input[value=Empresa]").focus(function() {
            $("#data").show();
            $("#tech-div").hide();
            $("#categoria-div").hide();
            $("#depto-div").hide();
            $("#generarReporte").attr("data-tipo", "Empresa");

            localStorage.clear();
            localStorage.setItem("inputOK", 1);
        })

        $("input[value=Integral]").focus(function() {
            $("#data").show();
            $("#tech-div").hide();
            $("#categoria-div").hide();
            $("#depto-div").hide();
            $("#empresa-div").hide();
            $("#generarReporte").attr("data-tipo", "Integral");

            localStorage.clear();
            localStorage.setItem("inputOK", 1)
        })

        // GENERAR REPORTE
        $("#generarReporte").click(function() {

            // OBTENER EL TIPO DE REPORTE
            var tipo = $("#generarReporte").attr("data-tipo");
            console.log("TIPO REPORTE: " + tipo);

            // VALIDAR SELECCIONES
            if (tipo == "Analista" || tipo == "Tareas") {
                <?php echo validar_selecciones("analista", "") ?>
            } else if (tipo == "Categoria") {
                <?php echo validar_selecciones("categoria", "") ?>
            }

            <?php echo validar_selecciones("fechaInicial", "") ?>
            <?php echo validar_selecciones("fechaFinal", "") ?>

            if (localStorage.getItem("inputOK") == 3) {
                $.ajax({
                    type: "POST",
                    url: `views/reporte${tipo}.php`,
                    data: $("#formularioReporte").serialize(),
                    success: function(data) {
                        $("#contenido").html(data);
                        // console.log(data);
                    }
                })
            } else {
                console.log("Realice todas las selecciones indicadas para generar el reporte.")
            }

        })


    })
</script>