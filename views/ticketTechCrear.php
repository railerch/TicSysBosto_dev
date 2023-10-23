<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

?>
<style>
    .ocultar {
        display: none;
    }
</style>

<div style="background: #505050;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;padding: 0.5em;">
    <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Crear Ticket</h1>
    <hr>
    <form id="ticketForm">
        <div class="form-group">
            <h4>Datos del ticket</h4>
            <div class="d-inline-flex flex-wrap" style="width:100%">
                <select id="empresa-emisora" class="form-control" name="empresa-emisora" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" value="" selected>Empresa emisora</option>
                </select>
                <select id="depto-emisor" class="form-control" name="depto-emisor" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" value="" selected>Depto emisor</option>
                </select>
                <select id="nombre" class="form-control" name="nombre" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" value="" selected>Usuario emisor</option>
                </select>
                <select id="empresa-receptora" class="form-control" name="empresa-receptora" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" value="" selected>Empresa receptora</option>
                </select>
                <select id="depto-receptor" class="form-control" name="depto-receptor" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" value="" selected>Depto receptor</option>
                </select>
                <select id="categoria" class="form-control" name="categoria" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;" <?php echo @$deshabilitar ?>>
                    <option style="color:#aaa" value="" selected>Categoría</option>
                </select>
                <select id="prioridad" class="form-control" name="prioridad" style="min-width:200px;max-width:15%;margin: 0 0.5em 0.5em 0;">
                    <option value="" selected>Prioridad</option>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>

            <div class="d-flex flex-wrap d-grid gap-2 mb-2">
                <div class="mx-0 mx-sm-1" style="flex-grow: 1">
                    <h4>Asunto</h4>
                    <input id="asunto" class="form-control" type="text" name="asunto" placeholder="Breve encabezado de la solicitud" maxlength="50">
                </div>
                <div id="monto-div" style="display:none; flex-grow: 1">
                    <h4>Monto (<small>Tasa cambio: <span id="monto-tasa-cambio">0.00</span></small>)</h4>
                    <div class="d-flex">
                        <div class="input-group">
                            <label class="input-group-text">USD</label>
                            <input id="monto-usd" class="form-control mr-1" type="number" name="monto" min="0" placeholder="Consultando tasa de cambio..." maxlength="50" disabled>
                        </div>

                        <div class="input-group">
                            <label class="input-group-text">BSD</label>
                            <input id="monto-bs" class="form-control" type="number" name="monto" min="0" placeholder="Consultando tasa de cambio..." maxlength="50" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <h4>Descripción</h4>
            <textarea id="descripcion" class="form-control" name="descripcion" style="min-height: 5em;max-height: 5em;" placeholder="Describa su solicitud" maxlength="250" required></textarea>
        </div>

        <div class="form-group d-md-flex justify-content-md-end">
            <button id="crear-ticket-btn" class="btn btn-primary" type="button">Crear ticket</button>
        </div>
    </form>
</div>

<?php
avisos(@$_SESSION['avisos']);
ocultar_aviso();
?>

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
        localStorage.clear();

        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/ticketTechCrear.php");

        // CARGAR EMPRESAS EMISORA
        let datosPhpRt = ["empresa-emisora", "Empresa emisora", "empresasRegistradas"]
        opciones_select(...datosPhpRt)

        // CARGAR EMPRESAS RECEPTORA
        let datosPhpRx = ["empresa-receptora", "Empresa receptora", "empresasRegistradas"]
        opciones_select(...datosPhpRx)

        // CARGAR DEPTO EMISOR SEGUN EMPRESA EMISORA
        $("#empresa-emisora").change(function() {
            if ($("#empresa-emisora").val() != "") {
                let empresa = $(this).val();
                // Depto emisor
                let datosPhpRt = ["depto-emisor", "Depto emisor", "empresaDeptos", empresa]
                opciones_select(...datosPhpRt)
            } else {
                $("#depto-emisor").html("<option style='color:#aaa' value=''>Depto emisor</option>");
                $("#nombre").html("<option style='color:#aaa' value=''>Usuario emisor</option>");
            }
        })

        // FILTRAR USUARIOS EMISORES SEGUN EL DEPTO
        $("#depto-emisor").change(function() {
            if ($("#depto-emisor").val() != "") {
                let empresa = $("#empresa-emisora").val();
                let depto = $(this).val();
                let datosPhp = ["nombre", "Usuario emisor", "deptoUsuarios", empresa, depto]
                opciones_select(...datosPhp)
            } else {
                $("#nombre").html("<option style='color:#aaa' value=''>Usuario emisor</option>");
            }
        })

        // CARGAR DEPTO RECEPTOR SEGUN EMPRESA RECEPTORA
        $("#empresa-receptora").change(function() {
            if ($("#empresa-receptora").val() != "") {
                let empresa = $(this).val();
                // Depto receptor
                let datosPhpRx = ["depto-receptor", "Depto receptor", "empresaDeptos", empresa]
                opciones_select(...datosPhpRx)
            } else {
                $("#depto-receptor").html("<option style='color:#aaa' value=''>Depto receptor</option>");
                $("#categoria").html("<option style='color:#aaa' value=''>Categoría</option>");
                $("#monto-div").css({
                    display: "none"
                })
            }

        })

        // FILTRAR CATEGORIAS SEGUN EL DEPTO SELECCIONADO
        $("#depto-receptor").change(function() {
            $("#monto-tasa-cambio").text("0.00");
            if ($("#depto-receptor").val() != "") {
                let empresa = $("#empresa-receptora").val();
                let depto = $(this).val();
                let datosPhp = ["categoria", "Categoria", "deptoCats", empresa, depto]
                opciones_select(...datosPhp)

                // Mostrar campo de monto sugerido en caso de que el depto receptor sea finanzas
                if ($(this).val() == "Finanzas") {
                    $("#monto-usd, #monto-bs").val("");
                    $("#monto-div").css({
                        display: "block"
                    })

                    // Consultar tasa de cambio
                    let cred = btoa("root:$ro123ot$");

                    fetch("http://nodesrv.dnsalias.com:8185/divisas/bcv" /*, {headers: {Authorization: `Basic ${cred}`}}*/ )
                        .then(res => res.json())
                        .then(res => {
                            sessionStorage.setItem("tasaCambioUSD", res);
                            $("#monto-usd, #monto-bs").attr("placeholder", "Monto estimado (opcional)")
                            $("#monto-usd, #monto-bs").removeAttr("disabled");
                            $("#monto-tasa-cambio").text(res);
                            $("#monto-usd, #monto-bs").css({
                                border: "1px solid #ced4da"
                            })
                        }).catch(err => {
                            console.error("ERROR AL CONSULTAR TASA DE CAMBIO: " + err);
                            $("#monto-usd, #monto-bs").attr("placeholder", "Error en tasa de cambio")
                            $("#monto-usd, #monto-bs").css({
                                border: "2px solid red"
                            })
                            sessionStorage.setItem("tasaCambioUSD", 1);
                        })
                } else {
                    $("#monto-div").css({
                        display: "none"
                    })
                }
            } else {
                $("#categoria").html("<option style='color:#aaa' value=''>Categoría</option>");
                $("#monto-div").css({
                    display: "none"
                })
            }
        })

        // REALIZAR CONVERSION ENTRE MONTOS USD/BS
        $("#monto-usd").blur(function() {
            let montoUSD = parseFloat($(this).val());
            let tasaUSD = parseFloat(sessionStorage.getItem("tasaCambioUSD"));
            $("#monto-bs").val((montoUSD * tasaUSD).toFixed(2));
        })

        $("#monto-bs").blur(function() {
            let montoBS = parseFloat($(this).val());
            let tasaUSD = parseFloat(sessionStorage.getItem("tasaCambioUSD"));
            $("#monto-usd").val((montoBS / tasaUSD).toFixed(2));
        })

        // CREAR TICKET
        $("#crear-ticket-btn").click(function() {
            event.preventDefault();

            // VALIDAR CAMPOS Y SELECCIONES
            <?php
            echo validar_selecciones("empresa-emisora", "");
            echo validar_selecciones("depto-emisor", "");
            echo validar_selecciones("nombre", "");
            echo validar_selecciones("empresa-receptora", "");
            echo validar_selecciones("depto-receptor", "");
            echo validar_selecciones("categoria", "");
            echo validar_selecciones("asunto", "");
            echo validar_montos("monto-usd", "");
            echo validar_montos("monto-bs", "");
            echo validar_selecciones("prioridad", "");
            echo validar_selecciones("descripcion", "");
            ?>

            // ENVIAR DATOS
            // Validar si el depto receptor es finanzas para modificar el conteo de verificaciones
            let campos = 9;
            if ($("#depto-receptor").val() == "Finanzas") {
                campos = 11;
            }

            if (localStorage.getItem("inputOK") == campos) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearTicket=true",
                    data: $("#ticketForm").serialize(),
                    success: function(data) {
                        $("#contenido").load("views/ticketTechCrear.php");
                    }
                })
            }
        })

    })
</script>