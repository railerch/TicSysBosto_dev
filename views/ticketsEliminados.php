<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// MOSTRAR TICKETS SEGUN EL DEPARTAMENTO
$area = filtrar_depto();
$stmt = $conn->prepare("SELECT * FROM tickets $area AND estatus = 'eliminado'");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

?>

<div style="background: #5b5b5b;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-trash-o" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Tickets eliminados</h1>
    <hr style="background: #969696;">
    <div class="table-striped" style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered" style="text-align:center">
            <thead>
                <tr style="background: #353535;color: rgb(255,255,255);">
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Locación</th>
                    <th>Usuario</th>
                    <th>Prioridad</th>
                    <th>Técnico</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ticket = $stmt->fetch()) { ?>

                    <tr id="<?php echo $ticket['id_ticket'] ?>">
                        <td><?php echo $ticket['id_ticket'] ?></td>
                        <td><?php echo $ticket['fecha'] ?></td>
                        <td><?php echo $ticket['locacion'] ?></td>
                        <td><?php echo $ticket['persona'] ?></td>
                        <?php
                        switch ($ticket['prioridad']) {
                            case 'baja':
                                $style = "style='background-color:lightskyblue;color:white'";
                                break;
                            case 'media':
                                $style = "style='background-color:lightsalmon;color:white'";
                                break;
                            case 'alta':
                                $style = "style='background-color:orange;color:white'";
                                break;
                            case 'urgente':
                                $style = "style='background-color:red;color:white'";
                                break;
                        }

                        ?>
                        <td <?php echo $style ?>><?php echo $ticket['prioridad'] ?></td>
                        <td><?php if (!$ticket['tecnico']) {
                                echo 'Sin tècnico';
                            } else {
                                echo $ticket['tecnico'];
                            } ?></td>
                        <td>
                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                <div class="btn-group" role="group">

                                    <button class="btn btn-outline-primary btn-sm verTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Ver ticket" data-target="#ver<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>

                                    <button class="btn btn-outline-danger btn-sm eliminarTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Eliminar ticket de la papelera" data-target="#eliminar<?php echo $ticket['id_ticket'] ?>" data-eliminar-id="<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- VENTANAS MODAL -->
                    <?php
                        $pagina = 'ticketsEliminados.php';
                        include('ventanasModal.php');
                    ?>

                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
avisos(@$_SESSION['avisos']);
ocultar_aviso();
?>

<!-- IDIOMA ESPAÑOL PARA EL DATATABLE -->
<script>
    $(document).ready(function() {
        $(".table").DataTable({
            "language": {
                "url": "config/dataTableSpanish.json"
            },
            "order": [
                [1, "desc"]
            ]
        });
    })
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/ticketsEliminados.php");

        // ELIMINAR TICKET
        $(".eliminarConfirmacion").click(function() {

            // PREOLOADER
            setTimeout(function() {
                $("#contenido").html(
                    "<figure style='display:block;width:100%;position:absolute;top:45%;text-align:center;'><img src='assets/img/preloader.gif'></figure>"
                );
            }, 300);

            // IDENTIFICADOR UNICO DEL TICKET A ELIMINAR
            var id = $(this).attr("data-eliminar-id");

            // EJECUTAR ELIMINACIÓN
            $.ajax({
                type: "GET",
                url: `main_controller.php?id=${id}&eliminarTicket=true`,
                success: function(data) {
                    setTimeout(function() {
                        $("#contenido").load("views/ticketsEliminados.php");
                    }, 500);
                }

            })

        })

    })
</script>