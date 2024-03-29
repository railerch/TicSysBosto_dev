<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// CONSULTAR POR TICKETS DEL USUARIO
$usuario        = $_SESSION['usuario'];
$usuarioDepto   = $_SESSION['depto'];

if ($_SESSION['nivel'] == 'admin' || $_SESSION['nivel'] == 'gerente') {
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE (depto = '$usuarioDepto' AND depto_receptor = 'Finanzas') OR usuario = '$usuario' AND estatus <> 'eliminado'");
} else {
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE usuario = '$usuario' AND estatus <> 'eliminado'");
}

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

<div style="background: #505050;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Mis tickets</h1>
    <hr style="background: #969696;">
    <div class="table-striped" style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table table-bordered">
            <thead>
                <tr style="background: #505050;color:rgb(255,255,255);text-align:center">
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Receptor</th>
                    <th>Asunto</th>
                    <th>Prioridad</th>
                    <th>Analista</th>
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
                    <tr id="<?php echo $ticket['id_ticket'] ?>">
                        <td style="text-align:center;"><?php echo $ticket['id_ticket'] ?></td>
                        <td style="text-align:center;"><?php echo $ticket['fecha'] ?></td>
                        <td><b><?php echo $ticket['empresa_receptora'] ?></b> - <?php echo $ticket['depto_receptor'] ?></td>
                        <td><?php echo $ticket['asunto'] ?></td>
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
                        <td><?php echo $ticket['analista'] ?></td>
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
                        <td style="<?php echo $color ?>;text-align:center;"><?php echo $ticket['estatus'] ?></td>
                        <td>
                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                <div class="btn-group" role="group">

                                    <button class="btn btn-outline-primary btn-sm verTicket" data-toggle="modal" type="button" data-bs-tooltip="" title="Ver ticket" data-target="#ver<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-eye"></i>
                                    </button>

                                    <?php if ($ticket['estatus'] != 'precierre' && $ticket['estatus'] != 'cerrado') {
                                        if ($ticket['analista'] == NULL) {
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
        sessionStorage.setItem("pagina_actual", "views/ticketsUsuario.php");

        // CAMBIAR ESTATUS DE SOLICITUD O TICKET ENVIADO A FINANZAS (Solo gerentes/admin)
        document.querySelectorAll(".autorizacion-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                let ticketId = this.getAttribute("data-ticket");
                let autorizar = this.getAttribute("data-autorizar");

                fetch(`main_controller.php?autorizar_ticket=true&ticketId=${ticketId}&autorizado=${autorizar}`)
                    .then(res => res.text())
                    .then(res => {
                        $(`#ver${ticketId}`).modal("hide");
                        
                        setTimeout(() => {
                            $("#contenido").load("views/ticketsUsuario.php");
                        }, 300)
                    }).catch(err => {
                        console.log("ERROR: " + err);
                    })
            })
        })

        // ADJUNTAR ARCHIVO
        $(".adjuntarArchivo").click(function() {
            var id_ticket = $(this).attr("data-tic");
            $(`.archivoAdjunto${id_ticket}`).slideToggle();
        })

        // ENVIAR MENSAJE CON BOTON
        $(".enviarMensaje").click(function() {

            // VARIABLES
            var empresa = $(this).attr("data-loc");
            var id_ticket = $(this).attr("data-tic");
            var remitente = $(this).attr("data-usr");

            // CREAR EL OBJETO FORMDATA
            var data = new FormData();

            // ADJUNTAR ARCHIVO AL FORMDATA
            data.append("empresa", empresa);
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
                var empresa = $(this).attr("data-loc");
                var id_ticket = $(this).attr("data-tic");
                var remitente = $(this).attr("data-usr");

                // CREAR EL OBJETO FORMDATA
                var data = new FormData();

                // ADJUNTAR ARCHIVO AL FORMDATA
                data.append("empresa", empresa);
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
            var analista = $(this).attr("data-analista");
            var nombreUsr = $(this).attr("data-nombre-usuario");

            // ESTABLECER EL COMENTARIO SEGUN EL TIPO DE SESION
            var comentarioTxt = "El usuario dio por finalizado el soporte";

            // ENVIAR MENSAJE DE CIERRE DE TICKET POR EL USUARIO
            var empresa = $(this).attr("data-empresa");
            var remitente = $(this).attr("data-usuario");
            var mensaje = "Ticket cerrado por el usuario!";

            // PREPARAR DATOS PARA EL ENVIO
            var datos = new FormData();
            datos.append("empresa", empresa);
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
                url: `main_controller.php?id=${id}&nombreUsr=${nombreUsr}&analista=${analista}&solucion=${comentarioTxt}&agregarBitacora=true&preCierre=true`,
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
                        $("#analista" + id_ticket).html(data);
                    } else {
                        console.log("Sin analista asignado");
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