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
$stmt = $conn->query("SELECT * FROM tickets $area AND estatus = 'eliminado'");

?>

<div style="background: #505050;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i id="vaciar-papelera" class="fa fa-trash-o" style="font-size: 5vw;margin-right: 0.3em;cursor:pointer" title="Clic para vaciar la papelera"></i>
    <h1 class="d-inline-block">Tickets eliminados</h1>
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
                <?php while ($ticket = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                    <tr id="tk-<?php echo $ticket['id_ticket'] ?>">
                        <td style="text-align:center;"><?php echo $ticket['id_ticket'] ?></td>
                        <td style="text-align:center;"><?php echo $ticket['fecha'] ?></td>
                        <td><b><?php echo $ticket['empresa'] ?></b> - <?php echo $ticket['depto'] ?></td>
                        <?php if ($_SESSION['usuario'] == 'root') { ?>
                            <td><b><?php echo $ticket['empresa_receptora'] ?></b> - <?php echo $ticket['depto_receptor'] ?></td>
                        <?php } ?>
                        <td><?php echo $ticket['escalado_a'] ?></td>
                        <td><?php echo $ticket['categoria'] ?>
                        <td><?php echo $ticket['analista'] ?></td>
                        </td>
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

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/ticketsEliminados.php");

        // CAMBIAR ICONO DE PAPELERA SI HAY MENSAJES ELIMINADOS
        let filas = $("table tbody tr").length;
        if (filas > 0) {
            $("#vaciar-papelera").removeClass("fa-trash-o");
            $("#vaciar-papelera").addClass("fa-trash");
        }

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

        // VACIAR PAPELERA
        $("#vaciar-papelera").click(function() {
            // PREOLOADER
            $("#contenido").html(
                "<figure style='display:block;width:100%;position:absolute;top:45%;text-align:center;'><img src='assets/img/preloader.gif'></figure>"
            );

            fetch("main_controller.php?vaciarPapelera=true")
                .then(res => res.text())
                .then(res => {
                    if (res == 1) {
                        $("#contenido").load("views/ticketsEliminados.php");
                    }
                })
        })

    })
</script>