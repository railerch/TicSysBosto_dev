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
$stmt = $conn->prepare("SELECT * FROM tickets $area AND estatus IN ('abierto', 'precierre')");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

?>

<div style="background: #5b5b5b;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Tickets abiertos</h1>
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
                <?php while ($ticket = $stmt->fetch()) {

                    // PONER EN VERDE SI EL TICKET NO TIENEN UN TECNICO ASIGNADO
                    $color = 'transparent';
                    if (!$ticket['tecnico']) {
                        $color   = "#aeffae";
                        $pointer = 'cursor:pointer;';
                        $title   = "Doble clic para tomar el ticket";
                        $deshabilitar = 'disabled';
                    } else {
                        $pointer = 'cursor:pointer;';
                        $title   = "Doble clic para tomar el ticket";
                        $deshabilitar = NULL;
                    }

                ?>

                    <tr id="<?php echo $ticket['id_ticket'] ?>" class="ticketRow" style="<?php echo $pointer ?>background-color:<?php echo $color ?>" title="<?php echo $title ?>" data-tecnico="<?php echo $ticket['tecnico'] ?>">
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
                        <td><?php echo $ticket['tecnico'] ?></td>
                        <td style="text-align: unset">
                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                <div class="btn-group" role="group">

                                    <button class="btn btn-outline-primary btn-sm verTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Ver ticket" data-target="#ver<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>

                                    <?php if ($ticket['estatus'] != "cerrado") {

                                        if ($ticket['estatus'] == 'precierre') {
                                            $style = "style='display: none'";
                                        } else {
                                            $style = NULL;
                                        }

                                    ?>

                                        <button class="btn btn-outline-warning btn-sm ticketEspera" data-toggle="modal" type="button" data-bs-tooltip="" title="Poner en espera" data-espera-id="<?php echo $ticket['id_ticket'] ?>" <?php echo $style, isset($deshabilitar) ? $deshabilitar : NULL ?>>
                                            <i class="fa fa-hand-stop-o"></i>
                                        </button>


                                        <?php if ($ticket['estatus'] == 'precierre') { ?>
                                            <button class="btn btn-outline-success btn-sm cerrarTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Cerrar ticket" data-target="#cerrar<?php echo $ticket['id_ticket'] ?>" data-id-ticket="<?php echo $ticket['id_ticket'] ?>" data-tecnico="<?php echo $ticket['tecnico'] ?>" data-sesion="<?php echo $_SESSION['nombre'] ?>">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        <?php } ?>

                                        <?php if (consultarMsjs($ticket['id_ticket']) == 0) { ?>
                                            <button class="btn btn-outline-danger btn-sm eliminarTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Eliminar ticket" data-target="#eliminar<?php echo $ticket['id_ticket'] ?>" data-eliminar-id="<?php echo $ticket['id_ticket'] ?>">
                                                <i class="fa fa-trash-o"></i>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>

                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- VENTANAS MODAL -->
                    <?php
                        $pagina = 'ticketsAbiertos.php';
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
        sessionStorage.setItem("pagina_actual", "views/ticketsAbiertos.php");

        // LIMPIAR SESSION STORAGE PARA DESACTIVAR LA ALERTA VISUAL
        sessionStorage.removeItem("totalTickets");
        $("audio").html("");

        // AUTOASIGNACION DE TICKET MEDIANTE DOBLE CLIC
        $(".ticketRow").dblclick(function() {
            var id = $(this).attr("id");
            $.ajax({
                type: "GET",
                url: `main_controller.php?asignarTicket=true&id_ticket=${id}`,
                success: function() {
                    $("#contenido").load("views/ticketsAbiertos.php");
                }
            })
        });

        // VER TICKET
        $(".verTicket").click(function() {
            var str = $(this).attr("data-target");
            var id_ticket = str.substring(4);

            // HACER SCROLL HASTA EL ULTIMO MENSAJE
            setTimeout(function() {
                $(`div#chat${id_ticket}.chatWindow`).scrollTop(9999999);
            }, 500);

            $("input[name=mensaje]").focus(function() {
                $(`div#chat${id_ticket}.chatWindow`).scrollTop(9999999);
            })

            // INDICAR TECNICO A CARGO
            $.ajax({
                type: "GET",
                url: `main_controller.php?actualizarTecnico=true&id_ticket=${id_ticket}`,
                success: function(data) {
                    if (data != null) {
                        $("#tecnico" + id_ticket).html(data);
                    } else {
                        console.log("Sin técnico asignado");
                    }
                }
            });

            // OCULTAR EL BOTON DE ELIMINAR SI HAY MSJS EN EL CHAT
            $.ajax({
                type: "GET",
                url: `main_controller.php?verificarChat=true&id_ticket=${id_ticket}`,
                success: function(data) {
                    var estatus = data.substring(0, 1);
                    var ticket = data.substring(1);
                    if (estatus == "T") {
                        $("button[data-eliminar-id=" + ticket + "]").css("display", "none");
                    }
                }
            });
        })

        // ADJUNTAR ARCHIVO
        $(".adjuntarArchivo").click(function() {
            var id_ticket = $(this).attr("data-tic");
            $(`.archivoAdjunto${id_ticket}`).slideToggle();
        })

        // ENVIAR MENSAJE CON BOTON
        $(".enviarMensaje").click(function() {

            // VARIABLES
            var locacion = $(this).attr("data-loc");
            var id_ticket = $(this).attr("data-tic");
            var remitente = $(this).attr("data-tec");

            // CREAR EL OBJETO FORMDATA
            var data = new FormData();

            // ADJUNTAR ARCHIVO AL FORMDATA
            data.append("locacion", locacion);
            data.append("id_ticket", id_ticket);
            data.append("remitente", remitente);
            data.append("mensaje", $(`input[data-msj=${id_ticket}]`).val());
            data.append("archivo", $(`#archivo${id_ticket}`)[0].files[0]);

            $.ajax({
                url: "main_controller.php?enviarMensaje=true&admin=true",
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                data: data,
                success: function(data) {
                    console.log(data);
                    $("input[name=mensaje]").val(null);
                    // RECUPERAR MENSAJES
                    $("#chat" + id_ticket).load(
                        `main_controller.php?recuperarMensajes=true&id_ticket=${id_ticket}`);
                    $("#chat" + id_ticket).scrollTop();
                    $(":file").val(null);
                },
                error: function(e) {
                    console.log("ERROR : \n", e);
                }
            });

            // HACER SCROLL HASTA EL ULTIMO MENSAJE
            setTimeout(function() {
                $(`div#chat${id_ticket}.chatWindow`).scrollTop(9999999);
            }, 300);


        });

        // ENVIAR MENSAJE CON ENTER
        $("input[name=mensaje]").keypress(function(e) {
            if (e.keyCode == 13) {

                // VARIABLES
                var locacion = $(this).attr("data-loc");
                var id_ticket = $(this).attr("data-tic");
                var remitente = $(this).attr("data-tec");

                // CREAR EL OBJETO FORMDATA
                var data = new FormData();

                // ADJUNTAR ARCHIVO AL FORMDATA
                data.append("locacion", locacion);
                data.append("id_ticket", id_ticket);
                data.append("remitente", remitente);
                data.append("mensaje", $(`input[data-msj=${id_ticket}]`).val());
                data.append("archivo", $(`#archivo${id_ticket}`)[0].files[0]);

                $.ajax({
                    url: "main_controller.php?enviarMensaje=true&admin=true",
                    type: "POST",
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    data: data,
                    success: function(data) {
                        $("input[name=mensaje]").val(null);
                        // RECUPERAR MENSAJES
                        $("#chat" + id_ticket).load(
                            `main_controller.php?recuperarMensajes=true&id_ticket=${id_ticket}`);
                        $("#chat" + id_ticket).scrollTop();
                        $(":file").val(null);
                    },
                    error: function(e) {
                        console.log("ERROR : \n", e);
                    }
                });

                // HACER SCROLL HASTA EL ULTIMO MENSAJE
                setTimeout(function() {
                    $(`div#chat${id_ticket}.chatWindow`).scrollTop(9999999);
                }, 300);


            }
        });

        // CERRAR TICKET
        $(".cerrarTicket").click(function() {
            var tecnico = $(this).attr("data-tecnico");
            var sesion = $(this).attr("data-sesion");
            var id_ticket = $(this).attr("data-target").substring(7);

            // SI NO TIENE TECNICO A CARGO O NO SE POSEE EL TICKET
            if (tecnico == "" || sesion !== tecnico) {
                $(`#cerrar${id_ticket} .modal-body`).html(
                    "ATENCIÓN: no se puede cerrar el ticket. <br>Razones:<br> 1. El mismo no tiene un técnico a cargo<br>2. El técnico a cargo no es usted."
                );
                $(`#cerrar${id_ticket} .modal-footer button`).hide();
            } else {
                // SI ES EL TECNICO A CARGO
                $(".cerrarConfirmacion").click(function() {
                    // PREOLOADER
                    setTimeout(function() {
                        $("#contenido").html(
                            "<figure style='display:block;width:100%;position:absolute;top:45%;text-align:center;'><img src='assets/img/preloader.gif'></figure>"
                        );
                    }, 300);

                    // IDENTIFICADOR UNICO DEL TICKET A CERRAR
                    var id = $(this).attr("data-cerrar-id");
                    var tecnico = $(this).attr("data-tecnico");
                    var nombreUsr = $(this).attr("data-nombre-usuario");


                    // ESTABLECER EL COMENTARIO SEGUN EL TIPO DE SESION
                    if (sessionStorage.getItem("tipoSesion") == "usuario") {
                        var comentarioTxt = "Ticket cerrado por el usuario";
                    } else if (sessionStorage.getItem("tipoSesion") == "admin") {
                        var comentarioTxt = "Ticket cerrado por el técnico";
                    };

                    // AGREGAR BITACORA DE TECNICO
                    var solucion = $(`#bitacora${id}`).val();
                    if (solucion == "") {
                        solucion = "Solución no especificada.";
                    }

                    $.ajax({
                        type: "GET",
                        url: `main_controller.php?id=${id}&tecnico=${tecnico}&nombreUsr=${nombreUsr}&solucion=${solucion}&agregarBitacora=true&cierreTec=true`,
                        success: function(data) {
                            console.log(data)
                        }
                    })

                    // EJECUTAR CIERRE DEL TICKET
                    $.ajax({
                        type: "GET",
                        url: `main_controller.php?id=${id}&estatus=cerrado&tecnico=${tecnico}&comentario=${comentarioTxt}&cerrarTicket=true`,
                        success: function(data) {
                            setTimeout(function() {
                                $("#contenido").load("views/ticketsAbiertos.php");
                            }, 500);
                            console.log(data)
                        }
                    })

                })
            }
        })

        // ALERTA DE CIERRE DEL TICKET
        $(".activarAlerta").click(function() {
            let id_ticket = $(this).attr("data-ticket");
            $.ajax({
                type: "get",
                url: `main_controller.php?activarAlerta=true&id_ticket=${id_ticket}`,
                success: function(data) {
                    if (data == "OK") {
                        $(`#ver${id_ticket} .alertaActiva`).css("display", "unset");
                        $(`#ver${id_ticket} .activarAlerta`).css("display", "none");
                    }
                },
                error: function(error) {
                    console.log(error)
                }
            })
        })

        // PONER TICKET EN ESPERA
        $(".ticketEspera").click(function() {

            // PREOLOADER
            $("#contenido").html("<figure style='display:block;width:100%;position:absolute;top:45%;text-align:center;'><img src='assets/img/preloader.gif'></figure>");

            // IDENTIFICADOR UNICO DEL TICKET
            var id = $(this).attr("data-espera-id");

            $.ajax({
                type: "GET",
                url: `main_controller.php?id_ticket=${id}&estatus=espera&estatusTicket=true`,
                success: function() {
                    setTimeout(function() {
                        $("#contenido").load("views/ticketsAbiertos.php");
                    }, 250);
                }
            })
        })

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
                url: `main_controller.php?id=${id}&papelera=true&eliminarTicket=true`,
                success: function(data) {
                    console.log(data);
                    setTimeout(function() {
                        $("#contenido").load("views/ticketsAbiertos.php");
                    }, 500);
                }

            })

        })

    })
</script>