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
$stmt = $conn->prepare("SELECT * FROM tickets $area AND estatus = 'cerrado'");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

?>

<div style="background: #505050;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-check" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Tickets Cerrados</h1>
    <hr style="background: #969696;">
    <div class="table-striped" style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="background: #505050;color:rgb(255,255,255);text-align:center">
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Emisor</th>
                    <?php if ($_SESSION['usuario'] == 'root') { ?>
                        <th>Receptor</th>
                    <?php } ?>
                    <th>Escalado</th>
                    <th>Categoria</th>
                    <th>Analista</th>
                    <th>Prioridad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ticket = $stmt->fetch()) { ?>

                    <tr id="tk-<?php echo $ticket['id_ticket'] ?>" class="ticketRow">
                        <td style="text-align:center;"><?php echo $ticket['id_ticket'] ?></td>
                        <td style="text-align:center;"><?php echo $ticket['fecha'] ?></td>
                        <td><b><?php echo $ticket['empresa'] ?></b> - <?php echo $ticket['depto'] ?></td>
                        <?php if ($_SESSION['usuario'] == 'root') { ?>
                            <td><b><?php echo $ticket['empresa_receptora'] ?></b> - <?php echo $ticket['depto_receptor'] ?></td>
                        <?php } ?>
                        <td><?php echo $ticket['escalado_a'] ?></td>
                        <td><?php echo $ticket['categoria'] ?></td>
                        <td><?php echo $ticket['analista'] ?></td>
                        <?php
                        switch ($ticket['prioridad']) {
                            case 'baja':
                                $style = "style='background-color:lightskyblue;color:white;text-align:center;'";
                                break;
                            case 'media':
                                $style = "style='background-color:lightsalmon;color:white;text-align:center;'";
                                break;
                            case 'alta':
                                $style = "style='background-color:orange;color:white;text-align:center;'";
                                break;
                            case 'urgente':
                                $style = "style='background-color:red;color:white;text-align:center;'";
                                break;
                        }

                        ?>
                        <td <?php echo $style ?>><?php echo $ticket['prioridad'] ?></td>
                        <td>
                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                <div class="btn-group" role="group">

                                    <button class="btn btn-outline-primary btn-sm verTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Ver ticket" data-target="#ver<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>

                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- VENTANAS MODAL -->
                    <?php
                    $pagina = 'ticketsCerrados.php';
                    include('ventanasModal.php');
                    ?>

                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // IDIOMA ESPAÑOL PARA EL DATATABLE
        $(".table").DataTable({
            "language": {
                "url": "config/dataTableSpanish.json"
            },
            "order": [
                [1, "desc"]
            ]
        });

        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/ticketsCerrados.php");

        // MENSAJES DE CHATS
        var regs = document.querySelectorAll("tbody tr");
        for (var i = 0; i < regs.length; i++) {

            var reg_id = regs[i].getAttribute("id");

            // REALIZAR QUERY
            $("#chat" + reg_id).load(`main_controller.php?recuperarMensajes=true&id_ticket=${reg_id}`);
        }

        // INDICAR TECNICO A CARGO
        $(".verTicket").click(function() {
            var str = $(this).attr("data-target");
            var id_ticket = str.substring(4);
            $.ajax({
                type: "GET",
                url: `main_controller.php?actualizarTecnico=true&id_ticket=${id_ticket}`,
                success: function(data) {
                    if (data != null) {
                        $("#analista" + id_ticket).html(data);
                    } else {
                        console.log("Sin analista asignado");
                    }
                }
            });

            // HACER SCROLL HASTA EL ULTIMO MENSAJE
            setTimeout(function() {
                $(`div#chat${id_ticket}.chatWindow`).scrollTop(9999999);
            }, 500);
        })

    })
</script>