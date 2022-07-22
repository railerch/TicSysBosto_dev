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
                <select id="empresa" class="form-control" name="empresa" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Empresa que emite</option>
                </select>
                <select id="deptoEmisor" class="form-control" name="deptoEmisor" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Depto emisor</option>
                </select>
                <select id="nombre" class="form-control" name="nombre" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Usuario emisor</option>
                </select>
                <select id="area" class="form-control" name="area" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Depto receptor</option>
                </select>
                <select id="categoria" class="form-control" name="categoria" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;" <?php echo @$deshabilitar ?>>
                    <option style="color:#aaa" selected>Categoría</option>
                </select>
                <select id="prioridad" class="form-control" name="prioridad" style="min-width:200px;max-width:15%;margin: 0 0.5em 0.5em 0;">
                    <option selected>Prioridad</option>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <h4>Asunto</h4>
            <input id="asunto" class="form-control mb-2" type="text" name="asunto" placeholder="Breve encabezado de la solicitud" maxlength="50">
            <h4>Descripción</h4>
            <textarea id="descripcion" class="form-control" name="descripcion" style="min-height: 5em;max-height: 5em;" placeholder="Describa su solicitud, de ser necesario habilite ANYDESK y envie los datos de conexión" maxlength="250" required></textarea>
        </div>
        <div class="form-group d-md-flex justify-content-md-end">
            <button class="btn btn-primary" type="submit">Crear ticket</button>
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

        // CARGAR EMPRESAS EN REGISTRO DE USUARIO
        let datosPhp = ["empresa", "Seleccione la empresa", "empresasRegistradas"]
        opciones_select(...datosPhp)

        // FILTRAR DEPTOS SEGUN LA EMPRESA SELECCIONADA
        $("#empresa").change(function() {
            let empresa = $(this).val();
            let deptoRt = document.getElementById("deptoEmisor");

            // Depto emisor
            let datosPhpRt = ["deptoEmisor", "Depto emisor", "empresaDeptos", empresa]
            opciones_select(...datosPhpRt)

            // Depto receptor
            let datosPhpRx = ["area", "Depto receptor", "empresaDeptos", empresa]
            opciones_select(...datosPhpRx)
        })

        // FILTRAR USUARIOS EMISORES SEGUN EL DEPTO
        $("#deptoEmisor").change(function() {
            let empresa = $("#empresa").val();
            let depto = $(this).val();
            let datosPhp = ["nombre", "Usuario emisor", "deptoUsuarios", empresa, depto]
            opciones_select(...datosPhp)
        })

        // FILTRAR CATEGORIAS SEGUN EL DEPTO SELECCIONADO
        let tipoSesion = sessionStorage.getItem("tipoSesion");

        $("#area").change(function() {
            let empresa = $("#empresa").val();
            let depto = $(this).val();
            let datosPhp = ["categoria", "Categoria", "deptoCats", empresa, depto]
            opciones_select(...datosPhp)
        })

        // CREAR TICKET
        $("button[type=submit]").click(function() {
            event.preventDefault();

            // VALIDAR CAMPOS Y SELECCIONES
            <?php
            echo validar_selecciones("empresa", "Empresa");
            echo validar_selecciones("nombre", "Usuario");
            echo validar_selecciones("area", "Departamento al que va dirigida la solicitud");
            echo validar_selecciones("asunto", "");
            echo validar_selecciones("prioridad", "Prioridad");
            echo validar_selecciones("descripcion", "");

            ?>

            // ENVIAR DATOS
            if (localStorage.getItem("inputOK") == 6) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearTicket=true",
                    data: $("#ticketForm").serialize(),
                    success: function(data) {
                        $("#contenido").load("views/ticketTechCrear.php");
                        console.log(data);
                    }
                })
            }
        })

    })
</script>