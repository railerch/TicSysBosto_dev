<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// MOSTRAR AREAS E INCIDENCIAS REGULARES
$stmt_0 = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'depto' ORDER BY descripcion ASC");
$stmt_0->setFetchMode(PDO::FETCH_ASSOC);
$stmt_0->execute();

// CONSULTAR SI EL USUARIO TIENE TICKETS ABIERTOS PARA MOSTRARLE UN AVISO
$stmt_1 = $conn->prepare("SELECT COUNT(id_ticket) AS total FROM tickets WHERE estatus = 'abierto' AND usuario = '{$_SESSION['usuario']}'");
$stmt_1->execute();
$tickets = $stmt_1->fetch(PDO::FETCH_ASSOC);

if (@$_SESSION['avisos'] != "Ticket creado exitosamente!") {
    if ($tickets['total'] >= 5) {
        $_SESSION['avisos'] = "Ud tiene <span style='color: orange'>{$tickets['total']}</span> tickets abiertos! <br> Cierre los resueltos para crear nuevos tickets";
        $deshabilitar = 'disabled';
    } else {
        if ($tickets['total'] > 0) {
            if ($tickets['total'] == 1) {
                $temp = 'ticket abierto';
            } else {
                $temp = 'tickets abiertos';
            }
            $_SESSION['avisos'] = "Ud tiene <span style='color: orange'>{$tickets['total']}</span> {$temp}";
            $deshabilitar = NULL;
        } else {
            $deshabilitar = NULL;
        }
    }
} else {
    $deshabilitar = NULL;
}

?>

<div style="background: #5b5b5b;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;padding: 0.5em;">
    <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Crear Ticket</h1>
    <hr>
    <form id="ticketForm">
        <div class="form-group">
            <h4>Departamento al que va dirigido el ticket</h4>
            <select id="area" class="form-control" name="area" style="min-width:200px;max-width:80%;margin: 0 0.5em 0.5em 0;" <?php echo $deshabilitar ?>>
                <option style="color:#aaa" selected>Seleccione el departamento</option>
                <?php while ($depto = $stmt_0->fetch()) {
                    if ($depto['descripcion'] != $_SESSION['depto']) {
                ?>
                        <option style='color:#555' value="<?php echo $depto['descripcion'] ?>">
                            <?php echo $depto['descripcion'] ?></option>
                <?php }
                } ?>
            </select>
            <h4>Titulo del ticket</h4>
            <input id="solicitud" class="form-control" type="text" name="solicitud" placeholder="Breve encabezado de su solicitud" maxlength="50" <?php echo $deshabilitar ?>>
            <br>
            <!-- PRIORIDAD -->
            <select id="prioridad" class="form-control" name="prioridad" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;" <?php echo $deshabilitar ?>>
                <option selected>Prioridad</option>
                <option value="baja">Baja</option>
                <option value="media">Media</option>
                <option value="alta">Alta</option>
                <option value="urgente">Urgente</option>
            </select>
        </div>
        <div class="form-group">
            <h4>Descripción</h4>
            <textarea id="descripcion" class="form-control" name="descripcion" style="min-height: 5em; max-height: 5em;" placeholder="Describa su solicitud, de ser necesario habilite ANYDESK y envie los datos de conexión" maxlength="500" required <?php echo @$deshabilitar ?>></textarea>
        </div>
        <div class="form-group d-md-flex justify-content-md-end">
            <button class="btn btn-primary" type="submit" <?php echo $deshabilitar ?>>Crear ticket</button>
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
        sessionStorage.setItem("pagina_actual", "views/ticketUserCrear.php");

        // CREAR TICKET
        $("button[type=submit]").click(function() {
            event.preventDefault();

            // VALIDAR CAMPOS Y SELECCIONES
            <?php
            echo validar_selecciones("area", "Seleccione el departamento");
            echo validar_selecciones("solicitud", "");
            echo validar_selecciones("prioridad", "Prioridad");
            echo validar_selecciones("descripcion", "");
            ?>

            // ENVIAR DATOS
            if (localStorage.getItem("inputOK") == 4) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearTicket=true",
                    data: $("#ticketForm").serialize(),
                    success: function(data) {
                        $("#contenido").load("views/ticketUserCrear.php");
                        console.log(data);
                    }
                })
            }
        })
    })
</script>