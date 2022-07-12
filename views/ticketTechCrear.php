<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// MOSTRAR DEPARTAMENTOS, LOCACIONES E INCIDENCIAS REGULARES
$stmt_1 = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'locacion'");
$stmt_1->setFetchMode(PDO::FETCH_ASSOC);
$stmt_1->execute();

$stmt_2 = $conn->prepare("SELECT nombre FROM usuarios ORDER BY nombre ASC");
$stmt_2->setFetchMode(PDO::FETCH_ASSOC);
$stmt_2->execute();

$stmt_3 = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'depto' ORDER BY descripcion ASC");
$stmt_3->setFetchMode(PDO::FETCH_ASSOC);
$stmt_3->execute();

?>
<style>
    .ocultar {
        display: none;
    }
</style>

<div style="background: #5b5b5b;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;padding: 0.5em;">
    <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Crear Ticket</h1>
    <hr>
    <form id="ticketForm">
        <div class="form-group">
            <h4>Datos de usuario</h4>
            <div class="d-inline-flex" style="flex-wrap: wrap;">
                <select id="locacion" class="form-control" name="locacion" style="min-width:200px;max-width:23%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Locacion</option>
                    <?php while ($loc = $stmt_1->fetch()) { ?>
                        <option style='color:#555' value="<?php echo $loc['descripcion'] ?>"><?php echo $loc['descripcion'] ?>
                        </option>
                    <?php } ?>
                </select>
                <select id="persona" class="form-control" name="persona" style="min-width:200px;max-width:23%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Usuario</option>
                    <?php while ($usr = $stmt_2->fetch()) { ?>
                        <option style='color:#555' value="<?php echo $usr['nombre'] ?>"><?php echo $usr['nombre'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <h4>Departamento al que va dirigido el ticket</h4>
            <select id="area" class="form-control" name="area" style="min-width:200px;max-width:75%;margin: 0 0.5em 0.5em 0;">
                <option style="color:#aaa" selected>Seleccione el departamento</option>
                <?php while ($depto = $stmt_3->fetch()) { ?>
                    <option style='color:#555' value="<?php echo $depto['descripcion'] ?>"><?php echo $depto['descripcion'] ?>
                    </option>
                <?php } ?>
            </select>

            <h4>Titulo de la solicitud</h4>
            <input id="solicitud" class="form-control" type="text" name="solicitud" placeholder="Breve encabezado de la solicitud" maxlength="50">
            <br>
            <!-- PRIORIDAD -->
            <select id="prioridad" class="form-control" name="prioridad" style="min-width:200px;max-width:30%;margin: 0 0.5em 0.5em 0;">
                <option selected>Prioridad</option>
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
            </select>
        </div>
        <div class="form-group">
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

<script type="text/javascript">
    $(document).ready(function() {
        // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
        localStorage.clear();

        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/ticketTechCrear.php");

        // CREAR TICKET
        $("button[type=submit]").click(function() {
            event.preventDefault();

            // VALIDAR CAMPOS Y SELECCIONES
            <?php
            echo validar_selecciones("locacion", "Locacion");
            echo validar_selecciones("persona", "Usuario");
            echo validar_selecciones("area", "Departamento al que va dirigida la solicitud");
            echo validar_selecciones("solicitud", "");
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