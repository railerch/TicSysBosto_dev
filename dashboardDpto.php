<?php
include('main_functions.php');

// CONEXION DB
$conexion = new Connection('config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// COMPROBAR ESTATUS DE SESION
estatus_de_sesion();

// CARGAR CONFIG
$config = cargar_config();

?>

<!DOCTYPE html>
<html style="height: 100vh;">

<head>
    <?php include('views/headTags.php') ?>
</head>

<body style="color: #d7d7d7;">
    <div>
        <div class="container-fluid" style="color: #d7d7d7;">
            <div class="row" style="height: 100vh;">
                <div class="col-xl-12 d-flex flex-row justify-content-between" id="topBar" style="background: #545354;color: rgb(255,255,255);text-align: right;padding-top: 2vh;height: 10%;padding-left: 2vw;padding-right: 4vw;padding-bottom: 2vh;">

                    <!-- AYUDA
                    <div id="help">
                        <a href="manual/manualTecnico.php" target="_blank">
                            <i class="fa fa-book"></i> Manual de usuario
                        </a>
                    </div>
                    -->

                    <div style="width:fit-content;display:flex;align-items:center;font-size:2.5vw;color:#d7d7d7;">

                        <!-- NOTIFICACION: NUMERO DE MENSAJES NO LEIDOS -->
                        <div id="msjCant"></div>

                        <div id="tiempoSesion">
                            Tiempo de sesón <br><span id="timer"></span>
                        </div>

                        <i id="userIcon" class="fa fa-user-o" style="margin-right:0.2em;color:#07def3;cursor:pointer;" data-query="yes"></i>

                        <span id="nombreUsuario" class="d-inline-block"><?php echo $_SESSION['nombre'] . ' - ' . $_SESSION['depto'] ?></span>

                        <!-- *********************************************************************************************** -->

                        <!-- TABLA DE USUARIOS CONECTADOS -->
                        <div id="usuariosConectados" style="display: flex; flex-direction:column; justify-content:space-between;visibility:hidden">
                            <div id="conectadosScroll">
                                <table id="none" style="font-size:0.5em;text-align:center;width:100%">
                                    <tbody>
                                        <!-- Usuarios conectados -->
                                    </tbody>
                                </table>
                            </div>
                            <div>
                                <span>
                                    <i class="fa fa-user-o" style="margin-right:0.2em;color:#0bf82b"></i>
                                    <span id="usuariosActivos"></span>
                                </span>
                                <span>
                                    <i class="fa fa-user-o" style="margin-right:0.2em;color:red"></i>
                                    <span id="usuariosInactivos"></span>
                                </span>
                                <span>
                                    <i class="fa fa-users" style="margin-right:0.2em;color:#07def3"></i>
                                    <span id="usuariosTotales"></span>
                                </span>
                            </div>
                        </div>

                        <!-- VENTANA DE CHAT -->
                        <?php
                        $pagina = 'dashboardUser.php';
                        include('views/ventanasModal.php');
                        ?>

                        <!-- *********************************************************************************************** -->

                    </div>

                    <div id="logo" style="width:fit-content;display:flex;align-items:center;">
                        <img class="img-fluid float-left" src="<?php echo $config[2]->logo ?>" style="height: 3.5vw;">
                    </div>

                </div>

                <div class="col-1 col-lg-1 col-xl-1 d-flex flex-column align-items-center" id="sidebar" style="background: #353535;padding-top: 1.5em;height: 90%;border-width: 2px;border-style: solid;border-top-style: none;border-right-width: 1px;border-right-style: none;border-bottom-style: none;border-left-style: none;">

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="dashboard" href="#/" title="Dashboard" data-btn="dashboardStats"><i class="fa fa-dashboard" id="dashboardbtn" style="font-size: 2.3vw;"></i></a>

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="crearTicket" href="#/" title="Crear ticket" data-btn="ticketTechCrear"><i class="fa fa-edit" style="font-size: 2.3vw;"></i></a>

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="ticketsEntrantes" href="#/" title="Tickets abiertos" data-btn="ticketsAbiertos"><i class="fa fa-ticket" style="font-size: 2.4vw;"></i></a>

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="ticketsEspera" href="#/" title="Tickets en espera" data-btn="ticketsEspera"><i class="fa fa-hand-paper-o" style="font-size: 2.3vw;"></i></a>

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="ticketsResueltos" href="#/" title="Tickets cerrados" data-btn="ticketsCerrados"><i class="fa fa-check" style="font-size: 2.3vw;"></i></a>

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="papelera" href="#/" title="Tickets eliminados" data-btn="ticketsEliminados"><i class="fa fa-trash-o" style="font-size: 2.3vw;"></i></a>

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="reportes" href="#/" title="Reportes" data-btn="reportes"><i class="fa fa-line-chart" style="font-size: 2.1vw;"></i></a>

                    <a class="text-muted sidebtn" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="datosCuenta" href="#/" title="Datos de cuenta" data-btn="datosCuenta"><i class="fa fa-user-o" style="font-size: 3vw"></i></a>

                    <a class="text-muted cerrarSesion" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" id="logout" href="main_controller.php?logout=true" title="Cerrar sesion"><i class="fa fa-sign-out" style="font-size: 2.3vw;"></i></a>
                </div>
                <div class="col-11 col-sm-11 col-md-11 col-lg-11 col-xl-11 flex-row justify-content-center align-items-center" id="contenido" style="background: #1c1c1c;padding-top: 2em;padding-bottom: 15px; max-height:90%;overflow-y:scroll;" data-page='tecnico'></div>
                <!-- CONTENIDO -->
            </div>
        </div>
    </div>
    <footer>
        <!-- AUDIO PARA NOTIFICACIONES -->
        <audio id="notificacion" style="display:none" preload="auto" autoplay loop controls></audio>

        <?php
        include('views/footerScripts.php');
        avisos(@$_SESSION['avisos']);
        ocultar_aviso();
        ?>

        <script>
            $(document).ready(() => {

                // ALERTAS DE SONIDO
                function audio_ticket() {
                    var audio = '<source src="assets/sound/Bip_01.wav" type="audio/wav">';
                    $("#notificacion").html(audio);
                }

                function audio_mensajes() {
                    var audio = '<source src="assets/sound/Bip_02.wav" type="audio/wav">';
                    $("#notificacion").html(audio);
                }

                function audio_tareas() {
                    var audio = '<source src="assets/sound/Sholden.mp3" type="audio/mpeg">';
                    $("#notificacion").html(audio);
                }

                // TIMER GENERAL DE NOTIFICACIONES
                // fijar tiempo de sesion 00:00:00
                if (!sessionStorage.getItem("tiempoSesion")) {
                    // Guardar tiempo de sesion
                    let tmp = {
                        horas: 0,
                        minutos: 0,
                        segundos: 0,
                        cero_h: 0,
                        cero_m: 0,
                        cero_s: 0
                    }

                    let datos = JSON.stringify(tmp);
                    sessionStorage.setItem("tiempoSesion", datos);
                }

                // Recuperar tiempo de sesion al reiniciar el navegador
                let tiempoSesion = sessionStorage.getItem("tiempoSesion");
                let tiempo = JSON.parse(tiempoSesion);
                let horas = tiempo.horas;
                let minutos = tiempo.minutos;
                let segundos = tiempo.segundos;
                let cero_h = tiempo.cero_h;
                let cero_m = tiempo.cero_m;
                let cero_s = tiempo.cero_s;

                setInterval(function() {
                    // ALERTA NUEVOS MENSAJES
                    $.ajax({
                        type: "GET",
                        url: "main_controller.php?actualizarMsjTecnico=true",
                        success: function(data) {
                            var tickets = data.split("/");
                            if (tickets != "") {
                                var espera = abierto = i = 0;
                                var len = tickets.length;
                                for (i = 0; i < len; i++) {
                                    if (tickets[i] != "") {
                                        // 1: tiene mensajes - 0: sin mensajes
                                        var msj = tickets[i].substring(0, 1);
                                        // Estatus: E -> espera, A -> abierto
                                        var stm = tickets[i].substring(1, 2);
                                        // Numero del ticket
                                        var tic = tickets[i].substring(2);

                                        // ALERTAS DE COLOR EN FILAS DE TICKETS
                                        if (msj > 0) {
                                            $("tr[id=" + tic + "]").css("background-color",
                                                "#ffd373");
                                            if (stm == "A") {
                                                abierto++;
                                            } else if (stm == "E") {
                                                espera++;
                                            }
                                        } else {
                                            $("tr[id=" + tic + "]").css("background-color",
                                                "unset");
                                        }

                                        // RESPUESTA DESDE EL SERVIDOR
                                        // console.log(data);
                                        // console.log(tickets);
                                        // console.log(`Msj: ${msj}, Stm: ${stm}, Tic: ${tic}`);
                                    }

                                    // ACTIVAR ALERTAS EN TICKETS ABIERTOS
                                    if (abierto > 0) {
                                        audio_mensajes();
                                        $("#ticketsEntrantes i").addClass("pulso");
                                    } else {
                                        $("#ticketsEntrantes i").removeClass("pulso");
                                    }

                                    // ACTIVAR ALERTAS EN TICKETS EN ESPERA
                                    if (espera > 0) {
                                        audio_mensajes();
                                        $("#ticketsEspera i").addClass("pulso");
                                    } else {
                                        $("#ticketsEspera i").removeClass("pulso");
                                    }

                                }
                            }
                        }
                    });

                    // ALERTA PARA NUEVOS TICKETS
                    $.ajax({
                        type: "GET",
                        url: "main_controller.php?contarTickets=true",
                        success: function(data) {
                            if (data != 0) {
                                if (sessionStorage.getItem("totalTickets")) {
                                    // EN CASO DE EXISTIR SE COMPARA EL TOTAL ACTUAL CON LA CONSULTA
                                    if (sessionStorage.getItem("totalTickets") < data) {
                                        // SI HAY NUEVOS TICKETS SE ACTIVA LA ALERTA AUDIO/VISUAL
                                        $("#ticketsEntrantes i").addClass("pulso");
                                        // ACTIVAR ALERTA DE SONIDO
                                        audio_ticket();
                                        $("#openTicket").html(data);
                                        // RECARGAR LA SECCION ACTUAL
                                        if (sessionStorage.getItem("pagina_actual") ===
                                            "ticketsAbiertos.php") {
                                            $("#contenido").load(sessionStorage.getItem(
                                                "pagina_actual"));
                                            $("#ticketsEntrantes i").removeClass("pulso");
                                        }
                                    } else if (sessionStorage.getItem("totalTickets") > data) {
                                        // ACTUALIZAR SI SE ELIMINAN ALGUN TICKET EN OTRA LOCACION
                                        sessionStorage.setItem("totalTickets", data);

                                        $("#openTicket").html(data);
                                        // RECARGAR LA SECCION Y DESACTIVAR ALERTA VISUAL
                                        if (sessionStorage.getItem("pagina_actual") ===
                                            "ticketsAbiertos.php") {
                                            $("#contenido").load(sessionStorage.getItem(
                                                "pagina_actual"));
                                            $("#ticketsEntrantes i").removeClass("pulso");
                                        }
                                    } else {
                                        $("#openTicket").html(data);
                                        if (sessionStorage.getItem("pagina_actual") ===
                                            "ticketsAbiertos.php") {
                                            $("#ticketsEntrantes i").removeClass("pulso");
                                        }
                                    }
                                } else {
                                    // ESTABLECER TOTAL DE TICKETS ACTUALES
                                    sessionStorage.setItem("totalTickets", data)
                                }
                            } else {
                                sessionStorage.setItem("totalTickets", data);
                            }

                        }
                    })

                    // ALERTA NUEVAS TAREAS
                    if ($("#nombreUsuario").text() !== "Super Admin") {
                        $.ajax({
                            type: "GET",
                            url: "main_controller.php?revisarTareas=true",
                            success: function(data) {
                                if (sessionStorage.getItem("ultimaTarea")) {
                                    var ultimaTarea = parseInt(sessionStorage.getItem(
                                        "ultimaTarea"));
                                    if (ultimaTarea < data) {

                                        // NO ACTIVAR LAS ALERTAS SI SE ESTA EN LA PAGINA DE TAREAS
                                        if (sessionStorage.getItem("pagina_actual") !==
                                            "tareasAsignadas.php") {
                                            $("a[data-btn=tareasAsignadas] i").addClass(
                                                "pulso");
                                            audio_tareas();
                                        } else {
                                            // SUGERIR RECARGAR SECCION DE TAREAS
                                            sessionStorage.setItem("recargarPagina", "ok");
                                        }

                                        // AGREGAR NUEVAS TAREAS AL ALMACENAMIENTO DEL NAVEGADOR
                                        if (sessionStorage.getItem("tareasSinRevisar")) {
                                            var temp = sessionStorage.getItem(
                                                "tareasSinRevisar");
                                            masTareas = `${temp}${data}/`;
                                            sessionStorage.setItem("tareasSinRevisar",
                                                masTareas);
                                            sessionStorage.setItem("ultimaTarea", data);
                                            console.log("Ultimas tareas actualizadas");
                                        } else {
                                            sessionStorage.setItem("ultimaTarea", data);
                                            sessionStorage.setItem("tareasSinRevisar", data +
                                                "/");
                                            console.log("Ultimas tareas agregadas");
                                        }

                                    } else if (ultimaTarea > data) {
                                        sessionStorage.setItem("ultimaTarea", data);
                                        sessionStorage.removeItem("tareasSinRevisar");
                                        $("#contenido").load("views/tareasAsignadas.php");
                                    }
                                } else {
                                    sessionStorage.setItem("ultimaTarea", data);
                                    console.log("Se fijo la ultima tarea");
                                }

                            }
                        })
                    }

                    // ELIMINAR TAREAS SIN REVISAR DEL SESSION STORAGE
                    if (sessionStorage.getItem("tareasSinRevisar") == "") {
                        sessionStorage.removeItem("tareasSinRevisar");
                        sessionStorage.removeItem("recargado");
                        sessionStorage.removeItem("nuevaTarea");
                    }

                    //ACTUALIZAR TIEMPO DE SESION
                    $.ajax({
                        type: "GET",
                        url: "main_controller.php?ultSesion=true",
                        success: function(data) {
                            console.log(data);
                        }
                    })

                    // RECUPERACION AUTOMATICA DE MSJS POR TICKET / MARCAR COMO VISTOS EN CASO DE ESTAR ABIERTO EL CHAT
                    var regs = document.querySelectorAll("tbody tr");
                    for (var i = 0; i < regs.length; i++) {

                        var reg_id = regs[i].getAttribute("id");

                        // COMPROBAR SI LA VENTANA DEL TICKET ESTA ABIERTA PARA MARCAR EL MENSAJE COMO VISTO
                        if ($("#ver" + reg_id).hasClass("show")) {
                            var leido = "&leido=true";
                        } else {
                            var leido = null;
                        }

                        // REALIZAR QUERY
                        $("#chat" + reg_id).load(
                            `main_controller.php?recuperarMensajes=true&id_ticket=${reg_id}${leido}`);
                    }

                    // NOTIFICACION MSJS NO LEIDOS EN INTERCHAT
                    $.ajax({
                        url: "main_controller.php?msjsNoLeidosInterchat=true",
                        success: function(data) {
                            if (data != 'null') {
                                let datos = JSON.parse(data);
                                if (datos.length > 0 && !$("#chatWindow").hasClass("show")) {
                                    audio_mensajes();
                                    $("#userIcon").addClass("pulso");
                                    $("#msjCant").css("visibility", "unset").text(datos.length);
                                }
                            } else {
                                $("#userIcon").removeClass("pulso");
                                $("#msjCant").css("visibility", "hidden");
                            }
                        },

                        error: function(e) {
                            console.log("ERROR : \n", e);
                        }

                    })

                    // RECUPERACION AUTOMATICA DE MSJS INTERCHAT / MARCAR COMO VISTOS EN CASO DE ESTAR ABIERTO EL CHAT
                    if ($("#chatWindow").hasClass("show")) {

                        var id_chat = $("#enviarMsj").attr("data-id-chat");

                        // RECUPERAR MENSAJES DEL CHAT ENTRE EL USUARIO ACTUAL Y EL SELECCIONADO
                        $("#msjWindow").load(`main_controller.php?recuperarMsjInterChat=true&id_chat=${id_chat}&leido=true`)
                    
                    }

                    // TIMER DE SESION
                    if (segundos < 10) {
                        cero_s = "0";
                    } else if (segundos == 60) {

                        minutos++;
                        segundos = 0;
                        cero_s = "0";

                        if (minutos < 10) {
                            cero_m = "0";
                        } else if (minutos == 60) {
                            horas++;
                            minutos = 0;
                            cero_m = "0"
                            if (horas < 10) {
                                cero_h = "0";
                            } else {
                                cero_h = "";
                            }
                        } else {
                            cero_m = "";
                        }

                    } else {
                        cero_s = "";
                    }

                    // Mostrar tiempo transcurrido
                    let tiempoTranscurrido =
                        `${cero_h+""+horas+":"+cero_m+""+minutos+":"+cero_s+""+segundos}`;
                    $("#timer").text(tiempoTranscurrido);

                    // Guardar tiempo de sesion
                    let tmp = {
                        horas: horas,
                        minutos: minutos,
                        segundos: segundos,
                        cero_h: cero_h,
                        cero_m: cero_m,
                        cero_s: cero_s
                    }

                    let datos = JSON.stringify(tmp);
                    sessionStorage.setItem("tiempoSesion", datos);

                    // Incrementar los segundos
                    segundos++;

                }, 1000);

                // CARGAR CONTENIDO POR DEFECTO
                if (!sessionStorage.getItem("pagina_actual")) {
                    sessionStorage.setItem("tipoSesion", "admin");
                    $("#contenido").load("views/dashboardStats.php");
                } else {
                    var pagina = sessionStorage.getItem("pagina_actual");
                    $("#contenido").load(pagina)
                }

                // VER USUARIOS ACTIVOS / INTERCHAT
                $("#usuariosConectados").hide();
                $("#userIcon").click(function() {

                    // Ocultar el selector de archivos adjuntos
                    $("#seleccionarArchivo").hide();

                    if ($(this).attr("data-query") == "yes") {

                        /* 
                        data-query es para evitar una segunda consulta al bd al momento de cerrar 
                        el cuadro de usuarios conectados, yes -> consulta, no -> no consulta ;)
                        */

                        $("#usuariosConectados tbody").text(null);

                        $.ajax({
                            type: "GET",
                            url: "main_controller.php?usuariosActivos=true",
                            success: function(data) {
                                var usuarios = JSON.parse(data);

                                // CARGAR USUARIO ADMINISTRADOR (root)
                                if ("<?php echo $_SESSION['usuario'] ?>" != "root") {
                                    $("#usuariosConectados tbody").append(
                                        `<tr id="rootUser" class="usuario" data-nombre="Administrador" data-emisor="<?php echo $_SESSION['usuario'] ?>" data-receptor="root" data-toggle="modal" data-target="#chatWindow">
                                        <td style="padding:5px"><i class="fa fa-user-o" style="color:orange;margin-right:5px;"></i></td>
                                        <td colspan="2" style="padding-right:15px">Administrador del sistema</td>
                                    </tr>`)
                                }

                                // CARGAR USUARIOS INDIVIDUALES
                                var registros = usuarios.length;
                                for (var i = 0; i < registros; i++) {
                                    if (i == 0) {
                                        // CARGAR ESTATUS GENERAL
                                        $("#usuariosActivos").text(usuarios[i][
                                            "usuariosActivos"
                                        ])
                                        $("#usuariosInactivos").text(usuarios[i][
                                            "usuariosInactivos"
                                        ])
                                        $("#usuariosTotales").text(usuarios[i][
                                            "usuariosTotales"
                                        ])
                                    } else {

                                        // Si tiene mensajes para el usuario actual
                                        if (usuarios[i]["noLeidos"] == true) {
                                            var bg = "background-color:#24a072;color:#fff;border-top:1px solid lightgray"
                                        } else {
                                            var bg = null;
                                        }

                                        // Si esta online / offline
                                        if (usuarios[i]["estatus"] == 1) {
                                            var iconColor = "#0bf82b";
                                        } else {
                                            var iconColor = "gray";
                                        }

                                        // Fila con datos de usuario
                                        var usuario = `<tr style="${bg}" class="usuario" data-nombre="${usuarios[i]["nombre"]}" data-emisor="<?php echo $_SESSION['usuario'] ?>" data-receptor="${usuarios[i]["usuario"]}" data-toggle="modal" data-target="#chatWindow">
                                                    <td><i class="fa fa-user-o" style="color:${iconColor};margin-right:5px;"></i></td>
                                                    <td style="padding-right:10px">${usuarios[i]["nombre"]}</td>
                                                    <td style="color:lightgray"> | ${usuarios[i]["depto"]}</td>
                                                </tr>`;

                                        // Cargar el usuario de la sesión
                                        if (usuarios[i]["nombre"] == "<?php echo $_SESSION['nombre'] ?>") {
                                            $("#usuariosConectados tbody").append(
                                                `<tr>
                                                <td style="padding:5px"><i class="fa fa-user-o" style="color:#0bf82b;margin-right:5px;"></i></td>
                                                <td style="padding-right:15px">${usuarios[i]["nombre"]}</td>
                                                <td style="color:lightgray;padding:5px"> | ${usuarios[i]["depto"]}</td>
                                            </tr>`
                                            )

                                            // Comprobar si el usuario tiene msjs del ROOT
                                            if (usuarios[i]["noLeidosRt"]) {
                                                $("#rootUser").css({
                                                    "background-color": "#24a072",
                                                    "color": "#fff"
                                                })
                                            }

                                        } else {
                                            // Cargar el resto de los usuarios
                                            if (bg != null) {
                                                if ("<?php echo $_SESSION['usuario'] ?>" == "root") {
                                                    $("#usuariosConectados tbody").prepend(usuario)
                                                } else {
                                                    $("#usuariosConectados tbody tr:nth-child(1)").after(usuario)
                                                }
                                            } else {
                                                $("#usuariosConectados tbody").append(usuario)
                                            }

                                        }
                                    }
                                }

                                // INICAR CHAT CON UN USUARIO
                                $(".usuario").click(function() {
                                    console.log("Nuevo chat!")
                                    /* COMPROBAR SI YA HAY UN CHAT ACTIVO CON EL USUARIO SELECCIONADO 
                                    PARA RECUPERAR LOS MSJS Y/O GENERAR O NO UN NUEVO ID PARA EL CHAT */
                                    $.ajax({
                                        url: `main_controller.php?interChatComun=true&emisor=${$(this).attr("data-emisor")}&receptor=${$(this).attr("data-receptor")}`,
                                        success: function(data) {

                                            if (data == "") {
                                                var id_chat = Math.random().toString(16).slice(2);
                                            } else {
                                                var id_chat = data;
                                            }

                                            // AGREGAR EL ID DE CHAT AL BOTON DE ENVIAR MSJ
                                            $("#chatWindow #mensaje #enviarMsj").attr("data-id-chat", id_chat)

                                            // RECUPERAR MENSAJES DEL CHAT ENTRE EL USUARIO ACTUAL Y EL SELECCIONADO
                                            $("#msjWindow").load(`main_controller.php?recuperarMsjInterChat=true&id_chat=${id_chat}&leido=true`)

                                        },
                                        error: function(e) {
                                            $("#msjWindow").text("ERROR : \n", e)
                                        }
                                    })

                                    $("#userIcon").attr("data-query", "yes");
                                    $("#usuariosConectados").fadeOut();
                                    $("#chatWindow #chatUser").html("<h4 style='display:inline'>" + $(this).attr("data-nombre") + "</h4>")
                                    $("#chatWindow #mensaje #enviarMsj").attr("data-emisor", $(this).attr("data-emisor"))
                                    $("#chatWindow #mensaje #enviarMsj").attr("data-receptor", $(this).attr("data-receptor"))

                                    // HACER SCROLL HASTA EL ULTIMO MENSAJE
                                    setTimeout(function() {
                                        $("#chatWindow #msjWindow").scrollTop(9999999);
                                    }, 500);

                                    $("#textoMensaje").focus(function() {
                                        $("#chatWindow #msjWindow").scrollTop(9999999);
                                    })

                                    // PONER EN BLANCO VARIABLE $_FILES
                                    $(":file").val(null);

                                })

                            }
                        })

                        $(this).attr("data-query", "no")

                    } else {
                        $(this).attr("data-query", "yes");
                    }

                    $("#usuariosConectados").css("visibility", "visible");
                    $("#usuariosConectados").fadeToggle();
                })

                // ADJUNTAR ARCHIVO
                $("#adjuntarArchivo").click(function() {
                    $("#seleccionarArchivo").slideToggle();
                    console.log("Repeticiones Adj");
                })

                // ENVIAR MENSAJE
                $("#enviarMsj").click(function() {
                    console.log("Repeticiones Msj");
                    // VARIABLES
                    var id_chat = $(this).attr("data-id-chat");
                    var emisor = $(this).attr("data-emisor");
                    var receptor = $(this).attr("data-receptor");
                    var mensaje = $("#textoMensaje").val();
                    var archivo = $("#archivoSeleccionado")[0].files[0];

                    // CREAR EL OBJETO FORMDATA
                    var data = new FormData();

                    // ADJUNTAR ARCHIVO AL FORMDATA
                    data.append("id_chat", id_chat);
                    data.append("emisor", emisor);
                    data.append("receptor", receptor);
                    data.append("mensaje", mensaje);
                    data.append("archivo", archivo);

                    $.ajax({
                        url: "main_controller.php?enviarMsjInterChat=true&admin=true",
                        type: "POST",
                        enctype: 'multipart/form-data',
                        processData: false,
                        contentType: false,
                        data: data,
                        success: function(data) {
                            $("#textoMensaje").val(null);

                            // RECUPERAR MENSAJES NUEVAMENTE
                            $("#msjWindow").load(`main_controller.php?recuperarMsjInterChat=true&id_chat=${id_chat}&leido=true`);
                            $(":file").val(null);
                        },
                        error: function(e) {
                            console.log("ERROR : \n", e);
                        }
                    });

                    // HACER SCROLL HASTA EL ULTIMO MENSAJE
                    setTimeout(function() {
                        $("#chatWindow #msjWindow").scrollTop(9999999);
                    }, 500);


                });

                // OCULTAR USUARIOS
                $("#usuariosConectados").click(function() {
                    $("#usuariosConectados").fadeOut();
                    $("#userIcon").attr("data-query", "yes");
                })

                // BOTONES EN EL SIDEBAR
                $(".sidebtn").click(function() {
                    var page = $(this).attr("data-btn");
                    $.ajax({
                        type: "POST",
                        url: `views/${page}.php`,
                        success: function(data) {
                            $("#contenido").html(data);
                        }
                    });
                })

                // MOSTRAR AYUDA
                $("#topBar").hover(function() {
                    $("#help").fadeToggle();
                })

                // VACIAR EL LOCALSTORAGE AL CERRAR SESION
                $(".cerrarSesion").click(function() {
                    localStorage.clear();
                    sessionStorage.clear();
                });


            })
        </script>

    </footer>
</body>

</html>