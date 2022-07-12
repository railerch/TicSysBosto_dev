<?php
// UTILIDADES GENERALES DE LA APLICACION
#----------------------------------------
session_start();
error_reporting(-1);
date_default_timezone_set('America/Caracas');
#----------------------------------------

// AUTOCARGAR CLASESS
spl_autoload_register(function ($class) {
    include("classes/{$class}.php");
});

// CONFIGURACIONES
function cargar_config(bool $goParent = NULL)
{
    $up = isset($goParent) ? "../" : NULL;
    $temp = file_get_contents("{$up}config/config.json");
    $config = json_decode($temp);
    return $config;
}

// AVISOS AL USUARIO
function avisos($aviso)
{

    $mensaje = isset($aviso) ? $aviso : NULL;

    if ($mensaje != NULL) {
        echo "<div id='avisos' style='width:100vw;position:fixed;top:45%;left:0;padding:0.5em;background-color:#000;color:#07def3;text-align:center'><h3>{$mensaje}</h3></div>";

        // REINICIAR LA VARIABLE DE AVISOS
        $_SESSION['avisos'] = NULL;
    }
}

// OCULTAR AVISO
function ocultar_aviso()
{
    echo '
    <script type="text/javascript">
        setTimeout(function(){
            $("#avisos").css("display", "none");
        },2500)
    </script>
    ';
}

// VALIDAR SELECCIONES CON JAVASCRIPT
# NOTA: insertar en el evento click del boton submit
function validar_selecciones(string $selector, string $valor)
{
    echo '  
            if(!localStorage.getItem("' . $selector . '")){
                if ($("#' . $selector . '").val() == "' . $valor . '") {
                    event.preventDefault();
                    $("#' . $selector . '").css({"background" : "#720000", "color" : "#fff"});
                    $("#' . $selector . ' option").css("color", "white");
                } else {
                    $("#' . $selector . '").css("background", "");
                    $("#' . $selector . '").css("color", "unset");
                    $("#' . $selector . ' option").css("color", "initial");
                    localStorage.setItem("' . $selector . '","ok")
                    var cont = localStorage.getItem("inputOK");
                    if(cont){
                        cont++;
                        localStorage.setItem("inputOK",cont)
                        cont = 0;
                    }else{
                        localStorage.setItem("inputOK",1)
                    }
                }
            }
    ';
}

function validar_nombre_usuario(){

}

// COMPROBAR ESTATUS DE SESION
function estatus_de_sesion()
{
    global $conn;

    if ($_SESSION['usuario'] != 'root') {

        $stmt_sesion = $conn->prepare("SELECT nivel, estatus FROM usuarios WHERE usuario = '{$_SESSION['usuario']}'");
        $stmt_sesion->execute();
        $estatus = $stmt_sesion->fetch(PDO::FETCH_ASSOC);

        switch ($estatus['nivel']) {
            case 'tecnico':
                $token = $_SESSION['tec_token'];
                break;
            case 'gerente':
                $token = $_SESSION['grt_token'];
                break;
            case 'usuario':
                $token = $_SESSION['usr_token'];
                break;
        }

        if ($estatus['estatus'] == 0 || $_GET['token'] == NULL || $_GET['token'] != $token) {
            header('Location: main_controller.php?logout=true');
        }
    } else {

        if ($_SESSION['sesion_estatus'] == 0 || $_GET['token'] == NULL || $_GET['token'] != $_SESSION['tec_token']) {
            header('Location: main_controller.php?logout=true');
        }
    }
}

// ACTIVAR APP A PANTALLA COMPLETA
function pantalla_completa()
{
    echo '
    <script type="text/javascript">
        document.addEventListener("mouseover", function() {
            var elem = document.querySelector("html");
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            }
        })
    </script>
    ';
}

/********************** TICKETS **********************/
// COMPROBAR MENSAJES NO LEIDOS DE TICKETS
function comprobar_no_leidos(string $col, string $user, string $remit)
{
    global $conn;
    // CONSULTAR TICKETS EXISTENTES
    $stmt = $conn->prepare("SELECT id_ticket AS id FROM tickets WHERE $col = '$user' AND estatus <> 'cerrado'");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();


    // TICKETS DE USUARIO
    while ($id_ticket = $stmt->fetch()) {
        $tickets[] = $id_ticket['id'];
    }

    if (isset($tickets)) {
        // CONSULTAR CHATS Y MENSAJES NO LEIDOS DE CADA TICKET
        $tic = $stat = NULL;
        foreach ($tickets as $ticket) {

            // ESTATUS DEL TICKET DE LOS MENSAJES EN CURSO
            $stmt_stm = $conn->prepare("SELECT estatus FROM tickets WHERE id_ticket = '$ticket'");
            $stmt_stm->setFetchMode(PDO::FETCH_ASSOC);
            $stmt_stm->execute();
            $temp_stm = $stmt_stm->fetch();

            switch ($temp_stm['estatus']) {
                case "abierto":
                    $stat = "A";
                    break;
                case "espera":
                    $stat = "E";
                    break;
                case "precierre":
                    $stat = "P";
                    break;
            }

            // CADENA CON EL ID DE LOS TICKETS QUE TENGAN MSJS NO LEIDOS
            $stmt = $conn->prepare("SELECT count(leido) AS no_leidos FROM chats WHERE id_ticket = '$ticket' AND leido = '0' AND remitente <> '$remit'");
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute();
            $temp = $stmt->fetch();

            if ($temp['no_leidos'] > 0) {
                @$tic .= "1" . $stat . $ticket . "/";
            } else {
                @$tic .= "0" . $stat . $ticket . "/";
            }
        }

        // CADENA CON ID's DE TICKETS
        return @$tic;
    }
}

// RECUPERAR MENSAJES DE CHATS TICKETS
function procesar_mensajes($mensajes, $id_ticket)
{
    global $conn;

    foreach ($mensajes as $msj) {

        // CHEQUEAR SI EL MENSAJE DEL USUARIO DE LA SESION ACTUAL YA FUE LEIDO
        if ($msj['remitente'] == $_SESSION['usuario']) {
            if ($msj['leido'] == 1) {
                $leido = "<i class='fa fa-check' style='color:orange'></i>";
            } else {
                $leido = "<i class='fa fa-clock-o' style='color:orange'></i>";
            }
        } else {
            $leido = NULL;
        }

        // MARCAR COMO LEIDOS LOS MENSAJES DEL REMITENTE SI SE ABRIO LA VENTANA DE CHAT
        if (@$_GET['leido']) {
            $usuario = $_SESSION['usuario'];
            $stmt = $conn->prepare("UPDATE chats SET leido = '1' WHERE id_ticket = '$id_ticket' AND remitente <> '$usuario'");
            $stmt->execute();
        }

        // ESTILOS PARA MENSAJES

        $stmt_t = $conn->prepare("SELECT usuario, tecnico FROM tickets WHERE id_ticket = '$id_ticket' ");
        $stmt_t->execute();
        $usr = $stmt_t->fetch(PDO::FETCH_ASSOC);
        $usuario_ticket = @$usr['usuario'];

        if (!in_array($_SESSION['usuario'], $usr)) {
            if (($msj['remitente'] == $usuario_ticket)) {
                $estilos = "float:left;text-align:left;padding:0.5em; background-color:lightgray; width:75%; border-radius:0.5em; margin: 0.2em 0";
            } else {
                $estilos = "float:right;text-align:right;padding:0.5em; background-color:lightgreen; width:75%; border-radius:0.5em; margin: 0.2em 0";
            }
        } else {
            if (($msj['remitente'] == $_SESSION['usuario'])) {
                $estilos = "float:right;text-align:right;padding:0.5em; background-color:lightgreen; width:75%; border-radius:0.5em; margin: 0.2em 0";
            } else {
                $estilos = "float:left;text-align:left;padding:0.5em; background-color:lightgray; width:75%; border-radius:0.5em; margin: 0.2em 0";
            }
        }

        if (strpos($msj['mensaje'], '|')) {
            // SI HAY MENSAJE CON ARCHIVO ADJUNTO

            $temp = explode('|', $msj['mensaje']);

            $mensaje = "<p>{$temp[0]}</p>";


            if (strpos($temp[1], '/')) {
                // SI ES UNA URI DE ARCHIVO

                // Comprobar el tipo de archivo
                $tempF = explode('/', $temp[1]);
                $type  = explode('.', $tempF[1]);

                switch (end($type)) {
                    case 'doc':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'docx':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'xls':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'xlsx':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'pdf':
                        $icono = 'assets/img/pdf.png';
                        break;
                    case 'txt':
                        $icono = 'assets/img/texto.png';
                        break;
                }

                // Mostrar imagen o icono si es un documento o imagen
                if ($type[1] == 'jpg' || $type[1] == 'jpeg' || $type[1] == 'png' || $type[1] == 'gif' || $type[1] == 'bmp') {
                    $adjunto = "<a href='{$temp[1]}' target='_blank'><img src='{$temp[1]}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                } else {
                    $adjunto = "<a href='{$temp[1]}' target='_blank'><img src='{$icono}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                }
            } else {
                // SI ES UN ERROR POR ARCHIVO INVALIDO
                $adjunto = "<p style='color:red'>{$temp[1]}</p>";
            }
        } else {
            // SI SOLO HAY UN MENSAJE NORMAL, ARCHIVO ADJUNTO O DE ARCHIVO INVALIDO

            if (strpos($msj['mensaje'], '/')) {
                // SI ES UNA URI
                $mensaje = NULL;

                // Comprobar el tipo de archivo
                $tempF  = explode('/', $msj['mensaje']);
                $type   = explode('.', $tempF[1]);
                $i      = count($type) - 1;

                switch ($type[$i]) {
                    case 'doc':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'docx':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'xls':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'xlsx':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'pdf':
                        $icono = 'assets/img/pdf.png';
                        break;
                    case 'txt':
                        $icono = 'assets/img/texto.png';
                        break;
                    default:
                        $icono = 'assets/img/default.png';
                        break;
                }

                // Mostrar imagen o icono si es un documento o imagen
                if ($type[$i] == 'jpg' || $type[$i] == 'jpeg' || $type[$i] == 'png' || $type[$i] == 'gif' || $type[$i] == 'bmp') {
                    $adjunto = "<a href='{$msj['mensaje']}' target='_blank'><img src='{$msj['mensaje']}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                } else {
                    $adjunto = "<a href='{$msj['mensaje']}' target='_blank'><img src='{$icono}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                }
            } else {
                // SI ES UN MENSAJE NORMAL O MENSAJE DE ARCHIVO INVALIDO

                if (strpos($msj['mensaje'], 'RROR:')) {
                    $style = "style='color:red'";
                } else {
                    $style = NULL;
                }

                $mensaje = "<p {$style}>{$msj['mensaje']}</p>";
                $adjunto = NULL;
            }
        }

        // MENSAJES
        echo "
        <br>
        <div style='{$estilos}; font-size:16px'>
            <b style='color:#999'>{$msj['fecha']} {$leido} <br></b>
            {$mensaje}{$adjunto}
        </div>
        ";
    };
}

// CONSULTAR SI LOS TICKETS TIENEN MSJS DE CHAT
function consultarMsjs($id_ticket)
{
    global $conn;
    $stmt_msj = $conn->prepare("SELECT count(id) AS total FROM chats WHERE id_ticket = '$id_ticket'");
    $stmt_msj->setFetchMode(PDO::FETCH_ASSOC);
    $stmt_msj->execute();
    $chat = $stmt_msj->fetch();
    return $chat['total'];
}

// MOSTRAR TICKETS SEGUN EL DEPARTAMENTO
function filtrar_depto()
{
    if ($_SESSION['nivel'] == 'gerente') {
        if ($_SESSION['depto'] != 'Sistemas') {
            return "WHERE area = '{$_SESSION['depto']}'";
        } else {
            echo '<span style="background-color:red; padding:10px; border-radius:5px;">ERROR: el nivel de usuario GERENTE no corresponde con el departamento asignado.</span>';
            exit();
        }
    } else if ($_SESSION['nivel'] == 'tecnico') {
        if ($_SESSION['depto'] == 'Sistemas') {
            return "WHERE area = '{$_SESSION['depto']}'";
        } else {
            echo '<span style="background-color:red; padding:10px; border-radius:5px;">ERROR: el nivel de usuario TÉCNICO no corresponde con el departamento asignado.</span>';
            exit();
        }
    }
}

/********************** INTERCHATS **********************/

// RECUPERAR MENSAJES DE INTERCHATS
function procesar_mensajes_interchat($mensajes, $id_chat)
{
    global $conn;

    foreach ($mensajes as $msj) {

        // CHEQUEAR SI EL MENSAJE DEL USUARIO DE LA SESION ACTUAL YA FUE LEIDO
        if ($msj['emisor'] == $_SESSION['usuario']) {
            if ($msj['leido'] == 1) {
                $leido = "<i class='fa fa-check' style='color:orange'></i>";
            } else {
                $leido = "<i class='fa fa-clock-o' style='color:orange'></i>";
            }
        } else {
            $leido = NULL;
        }

        // MARCAR COMO LEIDOS LOS MENSAJES DEL REMITENTE SI SE ABRIO LA VENTANA DE CHAT
        if (@$_GET['leido']) {
            $usuario = $_SESSION['usuario'];
            $stmt = $conn->prepare("UPDATE interchat SET leido = '1' WHERE id_chat = '$id_chat' AND emisor <> '$usuario'");
            $stmt->execute();
        }

        // ESTILOS PARA MENSAJES
        if ($msj['emisor'] == $_SESSION['usuario']) {
            $estilos = "float:right;text-align:right;padding:0.5em; background-color:lightgreen; width:75%; border-radius:0.5em; margin: 0.2em 0";
        } else {
            $estilos = "float:left;text-align:left;padding:0.5em; background-color:lightgray; width:75%; border-radius:0.5em; margin: 0.2em 0";
        }

        if (strpos($msj['mensaje'], '|')) {
            // SI HAY MENSAJE CON ARCHIVO ADJUNTO

            $temp = explode('|', $msj['mensaje']);

            $mensaje = "<p>{$temp[0]}</p>";


            if (strpos($temp[1], '/')) {
                // SI ES UNA URI DE ARCHIVO

                // Comprobar el tipo de archivo
                $tempF = explode('/', $temp[1]);
                $type  = explode('.', $tempF[1]);

                switch (end($type)) {
                    case 'doc':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'docx':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'xls':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'xlsx':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'pdf':
                        $icono = 'assets/img/pdf.png';
                        break;
                    case 'txt':
                        $icono = 'assets/img/texto.png';
                        break;
                }

                // Mostrar imagen o icono si es un documento o imagen
                if ($type[1] == 'jpg' || $type[1] == 'jpeg' || $type[1] == 'png' || $type[1] == 'gif' || $type[1] == 'bmp') {
                    $adjunto = "<a href='{$temp[1]}' target='_blank'><img src='{$temp[1]}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                } else {
                    $adjunto = "<a href='{$temp[1]}' target='_blank'><img src='{$icono}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                }
            } else {
                // SI ES UN ERROR POR ARCHIVO INVALIDO
                $adjunto = "<p style='color:red'>{$temp[1]}</p>";
            }
        } else {
            // SI SOLO HAY UN MENSAJE NORMAL, ARCHIVO ADJUNTO O DE ARCHIVO INVALIDO

            if (strpos($msj['mensaje'], '/')) {
                // SI ES UNA URI
                $mensaje = NULL;

                // Comprobar el tipo de archivo
                $tempF  = explode('/', $msj['mensaje']);
                $type   = explode('.', $tempF[1]);
                $i      = count($type) - 1;

                switch ($type[$i]) {
                    case 'doc':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'docx':
                        $icono = 'assets/img/word.png';
                        break;
                    case 'xls':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'xlsx':
                        $icono = 'assets/img/excel.png';
                        break;
                    case 'pdf':
                        $icono = 'assets/img/pdf.png';
                        break;
                    case 'txt':
                        $icono = 'assets/img/texto.png';
                        break;
                    default:
                        $icono = 'assets/img/default.png';
                        break;
                }

                // Mostrar imagen o icono si es un documento o imagen
                if ($type[$i] == 'jpg' || $type[$i] == 'jpeg' || $type[$i] == 'png' || $type[$i] == 'gif' || $type[$i] == 'bmp') {
                    $adjunto = "<a href='{$msj['mensaje']}' target='_blank'><img src='{$msj['mensaje']}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                } else {
                    $adjunto = "<a href='{$msj['mensaje']}' target='_blank'><img src='{$icono}' style='max-height:75px;width: auto;'><br>$tempF[1]</a>";
                }
            } else {
                // SI ES UN MENSAJE NORMAL O MENSAJE DE ARCHIVO INVALIDO

                if (strpos($msj['mensaje'], 'RROR:')) {
                    $style = "style='color:red'";
                } else {
                    $style = NULL;
                }

                $mensaje = "<p {$style}>{$msj['mensaje']}</p>";
                $adjunto = NULL;
            }
        }

        // MENSAJES
        echo "
        <br>
        <div style='{$estilos}; font-size:16px'>
            <b style='color:#999'>{$msj['fecha']} {$leido} <br></b>
            {$mensaje}{$adjunto}
        </div>
        ";
    };
}

/*********************************************************/
