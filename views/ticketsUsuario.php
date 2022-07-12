<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// CONSULTAR POR TICKETS DEL USUARIO
$usuario = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT * FROM tickets WHERE usuario = '$usuario' AND estatus <> 'eliminado'");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

?>
<style>
    #alertaDeCierre {
        padding-top: 150px;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background-color: #00000090;
        text-align: center;
        color: red;
        cursor: pointer;
    }

    #innerDisplay {
        width: 50%;
        margin: 0 auto;
        padding: 10px;
        background-color: #00000090;
        border-radius: 10px;
    }

    #alertaDeCierre h1,
    h2 {
        text-shadow: 1px 1px 1px #fff;
    }

    #alertaDeCierre h2 {
        color: orange;
    }

    @keyframes cerrar {
        0% {
            text-shadow: 0 0 0 #fff;
        }

        50% {
            text-shadow: 0 0 5px #00ff00;
        }

        100% {
            text-shadow: 0 0 0 #fff;
        }
    }

    #alertaDeCierre h5 {
        animation-name: cerrar;
        animation-duration: 1s;
        animation-iteration-count: infinite;
        color: #fff;
    }
</style>

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
                    <th>Solicitud</th>
                    <th>Prioridad</th>
                    <th>Técnico</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ticket = $stmt->fetch()) {
                    if ($ticket['comentarios'] == 'Alerta de cierre activada') {
                        $ticketsConAlerta[] = $ticket['id_ticket'];
                        $_SESSION['gracias'] = true;
                    }
                ?>
                    <tr id="<?php echo $ticket['id_ticket'] ?>" style="background-color:<?php echo $color ?>">
                        <td><?php echo $ticket['id_ticket'] ?></td>
                        <td><?php echo $ticket['fecha'] ?></td>
                        <td><?php echo $ticket['solicitud'] ?></td>
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
                        <?php switch ($ticket['estatus']) {
                            case 'abierto':
                                $color = 'color: #007bff';
                                break;
                            case 'espera':
                                $color = 'color:orange';
                                break;
                            case 'cerrado':
                                $color = 'color: #28a745';
                                break;
                        } ?>
                        <td style="<?php echo $color ?>"><?php echo $ticket['estatus'] ?></td>
                        <td>
                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                <div class="btn-group" role="group">

                                    <button class="btn btn-outline-primary btn-sm verTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Ver ticket" data-target="#ver<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>

                                    <?php if ($ticket['estatus'] != 'precierre' && $ticket['estatus'] != 'cerrado') {
                                        if ($ticket['tecnico'] == NULL) {
                                            $disable = 'disabled';
                                        } else {
                                            $disable = NULL;
                                        }
                                    ?>
                                        <button class="btn btn-outline-success btn-sm cerrarTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Cerrar ticket" data-target="#cerrar<?php echo $ticket['id_ticket'] ?>" <?php echo $disable ?>>
                                            <i class="fa fa-check"></i>
                                        </button>

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
                    $pagina = 'ticketsUsuario.php';
                    include('ventanasModal.php');
                    ?>

                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php

// AVISO DE CIERRE DE TICKET PARA USUARIOS
if (isset($ticketsConAlerta)) {
    echo '<div id="alertaDeCierre">
        <div id="innerDisplay">
                <h1>IMPORTANTE</h1>
        ';
    foreach ($ticketsConAlerta as $id) {
        echo "
            <h2>POR FAVOR CERRAR EL TICKET #{$id}</h2>";
    }
    echo '<h5>Clic en la pantalla para cerrar aviso!</h5>
                <small style="color: lightgray">Este aviso dejara de mostrarse cuando no tenga tickets pendientes para cerrar.</small>
            </div>
        </div>';
} else if (isset($_SESSION['gracias'])) {
    echo '<div id="alertaDeCierre">
        <div id="innerDisplay">
                <h1 style="color: lightgreen">GRACIAS POR SU COLABORACIÓN!</h1>
        <h5>Clic en la pantalla para cerrar aviso!</h5>
            </div>
        </div>';
    $_SESSION['gracias'] = NULL;
}

// AVISO DE ACCIONES
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
        sessionStorage.setItem("pagina_actual", "views/ticketsUsuario.php");

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
            var remitente = $(this).attr("data-usr");

            // CREAR EL OBJETO FORMDATA
            var data = new FormData();

            // ADJUNTAR ARCHIVO AL FORMDATA
            data.append("locacion", locacion);
            data.append("id_ticket", id_ticket);
            data.append("remitente", remitente);
            data.append("mensaje", $(`input[data-msj=${id_ticket}]`).val());
            data.append("archivo", $(`#archivo${id_ticket}`)[0].files[0]);

            $.ajax({
                url: "main_controller.php?enviarMensaje=true&usuario=true",
                type: "POST",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                data: data,
                success: function(data) {
                    $("input[name=mensaje]").val(null);
                    // RECUPERAR MENSAJES
                    $("#chat" + id_ticket).load(`main_controller.php?recuperarMensajes=true&id_ticket=${id_ticket}`);
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
                var remitente = $(this).attr("data-usr");

                // CREAR EL OBJETO FORMDATA
                var data = new FormData();

                // ADJUNTAR ARCHIVO AL FORMDATA
                data.append("locacion", locacion);
                data.append("id_ticket", id_ticket);
                data.append("remitente", remitente);
                data.append("mensaje", $(`input[data-msj=${id_ticket}]`).val());
                data.append("archivo", $(`#archivo${id_ticket}`)[0].files[0]);

                $.ajax({
                    url: "main_controller.php?enviarMensaje=true&usuario=true",
                    type: "POST",
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    data: data,
                    success: function(data) {
                        // console.log(data);
                        $("input[name=mensaje]").val(null);
                        // RECUPERAR MENSAJES
                        $("#chat" + id_ticket).load(`main_controller.php?recuperarMensajes=true&id_ticket=${id_ticket}`);
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
            var comentarioTxt = "El usuario dio por finalizado el soporte";

            // ENVIAR MENSAJE DE CIERRE DE TICKET POR EL USUARIO
            var locacion = $(this).attr("data-locacion");
            var remitente = $(this).attr("data-usuario");
            var mensaje = "Ticket cerrado por el usuario!";

            // PREPARAR DATOS PARA EL ENVIO
            var datos = new FormData();
            datos.append("locacion", locacion);
            datos.append("id_ticket", id);
            datos.append("remitente", remitente);
            datos.append("mensaje", mensaje);

            $.ajax({
                type: "POST",
                processData: false,
                contentType: false,
                data: datos,
                url: "main_controller.php?enviarMensaje=true&usuario=true",
                success: function(data) {
                    console.log(data)
                }
            })

            // EJECUTAR PRECIERRE DEL TICKET
            $.ajax({
                type: "GET",
                url: `main_controller.php?id=${id}&nombreUsr=${nombreUsr}&tecnico=${tecnico}&solucion=${comentarioTxt}&agregarBitacora=true&preCierre=true`,
                success: function(data) {
                    setTimeout(function() {
                        $("#contenido").load("views/ticketsUsuario.php");
                    }, 500);
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
                        $("#contenido").load("views/ticketsUsuario.php");
                    }, 500);
                }

            })
        })

        // AL VER TICKET VERIFICAR SI EL MISMO TIENE HISTORIAL DE CHAT PARA EVITAR SU ELIMINACION
        $(".verTicket").click(function() {
            var str = $(this).attr("data-target");
            var id_ticket = str.substring(4);

            // HACER SCROLL HASTA EL ULTIMO MENSAJE
            setTimeout(function() {
                $(`div#chat${id_ticket}.chatWindow`).scrollTop(9999999);
            }, 500);

            $("#inputMensaje").focus(function() {
                $(`div#chat${id_ticket}.chatWindow`).scrollTop(9999999);
            })

            // OCULTAR EL BOTON DE ELIMINAR
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

            
        })

        // OCULTAR ALERTA DE CIERRE
        $("#alertaDeCierre").click(function() {
            $("#alertaDeCierre").hide();
        })

    })
</script>