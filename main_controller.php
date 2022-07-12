<?php
#---------------------------------------------------------
// ARCHIVO DE EJECUCIONES GENERALES SEGUN EL GET OBTENIDO
#---------------------------------------------------------
include('main_functions.php');

//---------------------------------------------------------------------------------------------
//////////////////////////// CONEXION DB
//---------------------------------------------------------------------------------------------
$conexion = new Connection('config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

//---------------------------------------------------------------------------------------------
//////////////////////////// SESION
//---------------------------------------------------------------------------------------------

// INICIAR SESIÓN
if (@$_GET['login']) {
    $sesion = new Sesion($_POST);
    $sesion->iniciar_sesion();

    if ($_POST['usuario'] == 'root' && $sesion->exception) {
        $_SESSION['avisos'] = $sesion->exception;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else if ($_POST['usuario'] != 'root' && $sesion->exception) {
        $_SESSION['avisos'] = 'Error al momento de iniciar sesión, intente nuevamente!';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else if (!$sesion->estatus) {
        $_SESSION['avisos'] = 'Datos invalidos';
        header('Location: index.php');
    }

    exit();
}

// CERRAR SESION
if (@$_GET['logout']) {

    $sesion = new Sesion();
    $sesion->cerrar_sesion();

    if ($sesion->estatus) {
        $_SESSION['avisos'] = 'Sesión finalizada!';
        header('Location: index.php');
    } else if ($_SESSION['usuario'] != 'root' && $sesion->exception) {
        $_SESSION['avisos'] = 'Error al momento de cerrar sesión, intente nuevamente!';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        $_SESSION['avisos'] = 'Error al momento de cerrar sesión, intente nuevamente!';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    exit();
}

// ACTUALIZAR TIEMPO DE SESION
if (@$_GET['ultSesion'] && $_SESSION['usuario'] != 'root') {
    $sesion = new Sesion();
    $sesion->actualizar_tiempo_sesion($_SESSION['usuario']);

    if ($sesion->exception) {
        // Se recibe por consola en el dashboard (Javascript)
        echo $sesion->exception;
    }

    exit();
}

//---------------------------------------------------------------------------------------------
//////////////////////////// USUARIOS
//---------------------------------------------------------------------------------------------

// COMPROBAR DISPONIBILIDAD DE NOMBRE DE USUARIO ANTES DE REGISTRAR
if (@$_GET['nombreUsuario']) {
    $nombreUsuario = new Usuario();
    $res = $nombreUsuario->comprobar_nombre_usuario($_GET);

    if (!$nombreUsuario->exception) {
        echo $res;
    } else {
        echo $nombreUsuario->exception;
    }

    exit();
}

// REGISTRO DE USUARIO 
if (@$_GET['registrarUsuario']) {

    $usuario = new Usuario($_POST);
    $usuario->registrar_usuario();

    if ($usuario->estatus) {
        $_SESSION['avisos'] = "Usuario registrado exitosamente!";

        if ($_SESSION['nivel'] != 'tecnico') {
            header("Location: index.php");
            echo '<meta http-equiv="refresh" content="3;url=index.php">';
        }
    } else if ($usuario->exception) {
        $_SESSION['avisos'] = "Error al momento del registro, intente nuevamente";
        echo $usuario->exception;
        echo '<meta http-equiv="refresh" content="3;url=index.php">';
    }
    
    exit();
}

// ACTUALIZAR CLAVE 
if (@$_GET['actUsuario']) {

    if ($_POST['clave'] == '' && $_POST['locacion'] == 'NULL') {
        $_SESSION['avisos'] = "Los campos no pueden estar vacios";
        exit();
    }

    $usuario = new Usuario($_POST);
    $usuario->actualizar_usuario();

    if ($usuario->estatus) {
        $_SESSION['avisos'] = "Datos actualizados exitosamente!";
    } else if ($usuario->exception) {
        echo $usuario->exception;
        $_SESSION['avisos'] = "Error al actualizar la clave, intente nuevamente";
    }

    exit();
}

// CONSULTAR DATOS DE USUARIO PARA EDITAR
if (@$_GET['consultarUsuario']) {
    $usuario = new Usuario($_GET);
    $datos = $usuario->consultar_usuario();

    $data = [
        'nombre' => $datos['nombre'],
        'usuario' => $datos['usuario'],
        'clave' => $datos['clave']
    ];

    echo json_encode($data);

    exit();
}

// ACTUALIZAR DATOS DE USUARIO 
if (@$_GET['actualizarDatos']) {
    $usuario = new Usuario($_POST);
    $usuario->actualizar_usuario();

    if ($usuario->estatus) {
        $_SESSION['avisos'] = "Datos de usuario actualizados satisfactiriamente";
    } else if ($usuario->exception) {
        $_SESSION['avisos'] = "Error al actualizar datos del usuario, intente nuevamente";
    }

    exit();
}

// ELIMINAR CUENTA DE USUARIO 
if (@$_GET['eliminarCuenta']) {
    $cuenta = new Usuario($_GET);
    $cuenta->eliminar_usuario();


    if ($cuenta->estatus) {
        $_SESSION['avisos'] = "Usuario eliminado correctamente!";
        if ($_SESSION['nivel'] != 'tecnico') {
            header("Location: index.php");
        }
    } else if ($cuenta->exception) {
        $_SESSION['avisos'] = "Error al momento de eliminar el usuario, intente nuevamente!";
    } else {
        if ($_SESSION['nivel'] != 'tecnico') {
            // CERRAR LAS SESIÓN
            header("Location: logout.php");
        }
    }

    exit();
}

// VER USUARIOS ACTIVOS
if (@$_GET['usuariosActivos']) {

    $usuariosActivos = new Usuario();
    $activos = $usuariosActivos->usuarios_activos();

    if ($usuariosActivos->estatus) {
        echo $activos;
    }

    exit();
}

//---------------------------------------------------------------------------------------------
//////////////////////////// TICKETS
//---------------------------------------------------------------------------------------------

// CREAR TICKET
if (@$_GET['crearTicket']) {

    $ticket = new Ticket($_POST);
    $ticket->registrar_ticket();

    if ($ticket->estatus) {
        $_SESSION['avisos'] = 'Ticket creado exitosamente!';
    } else if ($ticket->exception) {
        $_SESSION['avisos'] = 'Error al momento del crear el ticket, intente nuevamente';
    }

    exit();
}

// CERRAR TICKET  
if (@$_GET['cerrarTicket']) {

    $ticket = new ticket($_GET);
    $ticket->cerrar_ticket();

    if ($ticket->estatus) {
        $_SESSION['avisos'] = "Ticket cerrado correctamente";
    } else if ($ticket->exception) {
        $_SESSION['avisos'] = "Error al momento del cerrar el ticket, intente nuevamente";
    }


    exit();
}

// AGREGAR BITACORA AL TICKET CERRADO
if (@$_GET['agregarBitacora']) {
    $bitacora = new Bitacora($_GET);
    $bitacora->registrar_bitacora();

    if ($bitacora->estatus) {
        $_SESSION['avisos'] = "Ticket cerrado correctamente";
    } else if (!$bitacora->precierre) {
        $_SESSION['avisos'] = 'Error al cerrar el ticket!';
    } else if ($bitacora->exception) {
        echo $bitacora->exception;
    }

    exit();
}

// CAMBIAR ESTATUS TICKET
if (@$_GET['estatusTicket']) {
    $ticket = new Ticket($_GET);
    $ticket->cambiar_estatus_ticket($_GET['estatus']);

    if ($ticket->estatus == 'espera') {
        $_SESSION['avisos'] = "Ticket #{$_GET['id_ticket']} puesto en espera";
    } else if ($ticket->estatus == 'abierto') {
        $_SESSION['avisos'] = "Ticket #{$_GET['id_ticket']} movido a la bandeja de entrada";
    }

    exit();
}

// ELIMINAR TICKET
if (@$_GET['eliminarTicket']) {

    $id = $_GET['id'];
    
    if ($_GET['papelera']) {
        $ticket = new Ticket($_GET);
        $ticket->enviar_ticket_papelera();

        if ($ticket->exception) {
            $_SESSION['avisos'] = "Error al momento de eliminar el ticket, intente nuevamente!";
        } else if ($ticket->estatus && $_SESSION['nivel'] == 'tecnico') {
            $_SESSION['avisos'] = "Ticket enviado a la papelera";
        } else {
            $_SESSION['avisos'] = "Ticket eliminado";
        }

        exit();
    } else {
        $ticket = new Ticket($_GET);
        $ticket->eliminar_ticket();

        if ($ticket->exception) {
            $_SESSION['avisos'] = "Error al momento de eliminar el ticket, intente nuevamente!";
        } else if ($ticket->estatus) {
            $_SESSION['avisos'] = "Ticket eliminado correctamente";
        }
    }

    exit();
}

// ASIGNAR TECNICO A UN TICKET
if (@$_GET['asignarTicket']) {

    $ticket = new Ticket($_GET);
    $ticket->asignar_ticket($_SESSION['nombre']);

    if ($ticket->estatus) {
        $_SESSION['avisos'] = "Ticket asignado a {$_SESSION['nombre']}";
    } else {
        $_SESSION['avisos'] = 'Error al momento de asignar el ticket, intente nuevamente!';
    }

    exit();
}

// INDICAR TECNICO ENCARGADO DE TICKET
if (@$_GET['actualizarTecnico']) {

    $ticket = new Ticket($_GET);
    echo $ticket->tecnico_asignado();

    exit();
}

// CONTAR TICKETS PARA ACTIVAR ALERTAS
if (@$_GET['contarTickets']) {

    echo Ticket::contar_tickets($_SESSION['depto']);

    exit();
}

//---------------------------------------------------------------------------------------------
//////////////////////////// MENSAJES TICKETS
//---------------------------------------------------------------------------------------------

// VERIFICAR SI UN TICKET TIENE CHAT ACTIVO
if (@$_GET['verificarChat']) {

    $mensaje = new Mensaje();
    $mensaje->comprobar_actividad_chat($_GET['id_ticket']);

    if ($mensaje->estatus) {
        echo $mensaje->info;
    } else {
        echo $mensaje->exception;
    }

    exit();
}

// ENVIAR MENSAJE DE TICKET/CHAT 
if (@$_GET['enviarMensaje']) {

    # Agregar un nuevo index para procesar un archivo adjunto en caso de que exista
    array_push($_POST, NULL);
    array_push($_POST, NULL);

    $mensaje = new Mensaje($_POST);

    # Procesar archivo adjunto en caso de que exista
    if ($_FILES) {
        $mensaje->adjuntar_archivo($_FILES);
    }

    # Estructurar el mensaje segun si tiene o no archivo adjunto
    if ($mensaje->info[0] != NULL && $_POST['mensaje'] != '') {
        $mensaje->info[1] = $_POST['mensaje'] . '|' . $mensaje->info[0];
    } else if ($mensaje->info[0] == NULL && $_POST['mensaje'] != '') {
        $mensaje->info[1] = $_POST['mensaje'];
    } else if ($mensaje->info[0] != NULL && $_POST['mensaje'] == '') {
        $mensaje->info[1] = $mensaje->info[0];
    } else if ($mensaje->info[0] == NULL && $_POST['mensaje'] == '') {
        exit();
    }

    # Enviar el mensaje
    $mensaje->enviar_mensaje();

    exit();
}

// RECUPERAR MENSAJES DE CHAT
if (@$_GET['recuperarMensajes']) {
    $id_ticket = $_GET['id_ticket'];
    $chat = new Mensaje($_GET);
    $mensajes = $chat->recuperar_mensajes($id_ticket);

    if ($mensajes != NULL) {
        // Desde main_functions.php
        procesar_mensajes($mensajes, $id_ticket);
    };

    exit();
}

// COMPROBAR MENSAJES NO LEIDOS PARA TICKETS DE TECNICO
if (@$_GET['actualizarMsjTecnico']) {
    $user  = $_SESSION['nombre'];
    $remit = $_SESSION['usuario'];
    $col   = "tecnico";

    echo comprobar_no_leidos($col, $user, $remit);
}

if (@$_GET['actualizarMsjUsuario']) {
    $user   = $_SESSION['nombre'];
    $remit  = $_SESSION['usuario'];
    $col    = "persona";

    echo comprobar_no_leidos($col, $user, $remit);
}

//---------------------------------------------------------------------------------------------
//////////////////////////// MENSAJES CHATS INTERUSUARIO
//---------------------------------------------------------------------------------------------

// COMPROBAR SI YA HAY UN CHAT ACTIVO CON EL USUARIO SELECCIONADO
if (@$_GET['interChatComun']) {

    $id_chat = new Interchat();
    echo $id_chat->id_chat_comun($_GET['emisor'], $_GET['receptor']);

    exit();
}

// ENVIAR MENSAJE INTERCHAT
if (@$_GET['enviarMsjInterChat']) {

    # Agregar un nuevo index para procesar un archivo adjunto en caso de que exista
    array_push($_POST, NULL);
    array_push($_POST, NULL);

    $mensaje = new Interchat($_POST);

    # Procesar archivo adjunto en caso de que exista
    if ($_FILES) {
        $mensaje->adjuntar_archivo($_FILES);
    }

    # Estructurar el mensaje segun si tiene o no archivo adjunto
    if ($mensaje->info[0] != NULL && $_POST['mensaje'] != '') {
        $mensaje->info[1] = $_POST['mensaje'] . '|' . $mensaje->info[0];
    } else if ($mensaje->info[0] == NULL && $_POST['mensaje'] != '') {
        $mensaje->info[1] = $_POST['mensaje'];
    } else if ($mensaje->info[0] != NULL && $_POST['mensaje'] == '') {
        $mensaje->info[1] = $mensaje->info[0];
    } else if ($mensaje->info[0] == NULL && $_POST['mensaje'] == '') {
        exit();
    }

    # Enviar el mensaje
    $mensaje->enviar_mensaje();

    exit();
}

// RECUPERAR MENSAJES INTERCHAT
if (@$_GET['recuperarMsjInterChat']) {
    $id_chat = $_GET['id_chat'];
    $chat = new Interchat($_GET);
    $mensajes = $chat->recuperar_mensajes($id_chat);

    if ($mensajes != NULL) {
        // Desde main_functions.php
        procesar_mensajes_interchat($mensajes, $id_chat);
    };

    exit();
}

// COMPROBAR MENSAJES NO LEIDOS INTERCHAT
if (@$_GET['msjsNoLeidosInterchat']) {
    $temp = new Interchat();
    echo json_encode($temp->msjs_no_leidos($_SESSION['usuario']));
}

//---------------------------------------------------------------------------------------------
//////////////////////////// TAREAS
//---------------------------------------------------------------------------------------------

// REGISTRAR TAREA
if (@$_GET['registrarTarea']) {

    # Agregar un nuevo index para procesar un archivo adjunto en caso de que exista
    array_push($_POST, NULL);

    $tarea = new Tarea($_POST);

    # Procesar archivo adjunto
    if ($_FILES) {
        $tarea->adjuntar_archivo($_FILES);
    }

    # Procesar la tarea
    $tarea->registrar_Tarea();

    # Generar aviso al usuario
    if ($tarea->estatus) {
        $_SESSION['avisos'] = "Tarea creada exitosamente!";

        # Crear log
        Log::registrar_log('Nueva tarea registrada');
    } else {
        $_SESSION['avisos'] = "Error: no se pudo crear la tarea, intente nuevamente";
        echo $tarea->exception;
    }

    echo '<pre>';
    var_dump($_POST);
    var_dump($_FILES);

    exit();
}

// ACTUALIZAR TAREA
if (@$_GET['actualizarTarea']) {

    array_push($_POST, $_GET['id_tarea']);
    $tarea = new Tarea($_POST);
    $tarea->actualizar_tarea();

    if ($tarea->estatus) {
        $_SESSION['avisos'] = "Tarea actualizada exitosamente!";

        # Crear log
        $id_tarea = substr($_GET['id_tarea'], 3);
        Log::registrar_log("Tarea #{$id_tarea} actualizada");
    } else {
        $_SESSION['avisos'] = "Error: tarea no actualizada, intente nuevamente";
        echo $tarea->exception;
    }

    exit();
}

// TOMAR TAREA
if (@$_GET['asignarTarea']) {

    $tarea = new Tarea();
    $res = $tarea->tomar_tarea($_SESSION['nombre'], $_GET['id_tarea']);

    if ($tarea->estatus) {

        if ($res == 'tareaTomada') {

            $_SESSION['avisos'] = "Tarea asignada a {$_SESSION['nombre']}";

            #Crear log
            Log::registrar_log("Tarea #{$_GET['id_tarea']} atendida");
        } else if ($res == 'tareaLiberada') {

            $_SESSION['avisos'] = "La tarea ha sido liberada";

            #Crear log
            Log::registrar_log("Tarea #{$_GET['id_tarea']} liberada");
        }
    } else {
        $_SESSION['avisos'] = "Error: asignación no ejecutada, intente nuevamente";
        echo $tarea->exception;
    }

    exit();
}

// ELIMINAR TAREA
if (@$_GET['eliminarTarea']) {

    $tarea = new Tarea();
    $tarea->eliminar_Tarea($_GET['id_tarea']);

    if ($tarea->estatus) {

        $_SESSION['avisos'] = "Tarea eliminada exitosamente!";

        # Crear log
        Log::registrar_log("Tarea #{$_GET['id_tarea']} eliminada");
    } else {
        $_SESSION['avisos'] = "Error: tarea no eliminada, intente nuevamente";
        echo $tarea->exception;
    }

    exit();
}

// ACTUALIZAR ESTATUS DE TAREA
if (@$_GET['estatusTarea']) {

    $tarea = new Tarea();
    $tarea->cambiar_estatus_Tarea($_GET['id_tarea'], $_GET['estatus']);

    if ($tarea->estatus) {
        $_SESSION['avisos'] = "Estatus de tarea actualizado exitosamente!";

        # Crear log
        $id_tarea = substr($_GET['id_tarea'], 3);
        Log::registrar_log("Tarea #{$id_tarea} estatus_act: {$_GET['estatus']}");
    } else {
        $_SESSION['avisos'] = "Error: estatus de tarea no actualizado, intente nuevamente";
        echo $tarea->exception;
    }

    exit();
}

// REVISAR POR NUEVAS TAREAS
if (@$_GET['revisarTareas']) {

    $tarea = new Tarea();
    echo $tarea->ultima_tarea();

    exit();
}

//---------------------------------------------------------------------------------------------
//////////////////////////// MISCELANEOS
//---------------------------------------------------------------------------------------------

// COMPROBAR EXISTENCIA DE LOCACION/DEPTO/AREA
if (@$_GET['comprobarMiscelaneo']) {

    try {

        $tipo = $_GET['tipo'];
        $val  = strtolower(trim(strval($_GET['val'])));
        $stmt = $conn->prepare("SELECT * FROM miscelaneos WHERE descripcion = '$val' AND tipo = '$tipo'");
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $miscelaneo = $stmt->fetch();

        if (@strtolower(trim($miscelaneo['descripcion'])) === $val) {
            echo true;
        }
    } catch (PDOException $e) {

        echo 'ERROR: ' . $e;
    }

    exit();
}

// CREAR LOCACION
if (@$_GET['crearLocacion']) {

    try {

        $locacion = trim($_POST['locacion']);
        $stmt_loc = $conn->prepare("INSERT INTO miscelaneos (id, descripcion, tipo) VALUES (NULL, '$locacion', 'locacion')");
        $stmt_loc->execute();

        echo 'Locación creada exitosamente!';

        // CREAR LOG
        Log::registrar_log("Nueva locación registrada: {$locacion} ");
    } catch (PDOException $e) {

        echo 'ERROR: ' . $e;
    }

    exit();
}

// CREAR DEPARTAMENTO
if (@$_GET['crearDepto']) {

    try {

        $stmt_dpt = $conn->prepare("INSERT INTO miscelaneos (descripcion, tipo) VALUES (?, ?)");

        $desc = trim($_POST['departamento']);
        $tipo = 'depto';
        $stmt_dpt->bindParam(1, $desc);
        $stmt_dpt->bindParam(2, $tipo);

        $stmt_dpt->execute();

        echo 'Departamento creado exitosamente!';

        // CREAR LOG
        Log::registrar_log("Nuevo departamento/area registrados: {$desc}/{$desc} ");
    } catch (PDOException $e) {

        echo 'ERROR: ' . $e;
    }

    exit();
}

// RESPALDAR BD
if (@$_GET['respaldarBD']) {

    // ESTABLECER FECHA PARA LA BUSQUEDA
    $fecha = date("d_m_Y");

    // REVISAR SI YA SE HA CREADO EL ARCHIVO DE RESPALDO
    `bd_bkp.bat`;
    echo 'Se ha creado un nuevo respaldo';
    $fecha = date('Y_m_d-H_i_s');
    rename('database/BKP/Tickets_db_BKP.sql', "database/BKP/Tickets_db_BKP_{$fecha}.sql");

    // CREAR LOG
    Log::registrar_log("Se ha respaldado la BD");

    exit();
}

// VER LISTA DE ARCHIVOS DE RESPALDO
if (@$_GET['verRespaldos']) {

    $dir = 'database/BKP/';
    $scan = scandir($dir);
    $bkp = rsort($scan);
    $total = count($scan) - 2;

    echo '<ul>';
    for ($i = 0; $i < $total; $i++) {
        if ($i == 0) {
            $last = '<span style="color:green"><<< Reciente</span>';
        } else {
            $last = NULL;
        }
        echo "<li><a href='assets/db/BKP/{$scan[$i]}' download>{$scan[$i]} {$last}</a></li>";
    };
    echo '</ul>';

    // CREAR LOG
    Log::registrar_log("Listar respaldos de BD");

    exit();
}

// DESCONECTAR A TODOS LOS USUARIOS
if (@$_GET['desconectarUsuarios']) {

    $tecnico = $_SESSION['nivel'];

    // CONSULTAR SI HAY USUARIOS CONECTADOS
    $stmt_con = $conn->prepare("SELECT COUNT(estatus) AS conectados FROM usuarios WHERE estatus = 1 AND nivel <> '$tecnico'");
    $stmt_con->execute();
    $res = $stmt_con->fetch(PDO::FETCH_ASSOC);

    if ($res['conectados'] > 0) {
        $stmt = $conn->prepare("UPDATE usuarios SET estatus = 0 WHERE estatus = 1 AND nivel <> '$tecnico'");
        $stmt->execute();
        echo 'Todos los usuarios han sido desconectados.';

        // CREAR LOG
        Log::registrar_log("Los usuarios han sido desconectados");
    } else {

        echo 'No hay usuarios conectados!';
    }

    exit();
}

// ALERTA DE CIERRA DE TICKET
if (@$_GET['activarAlerta']) {
    $id_ticket = $_GET['id_ticket'];
    try {
        $stmt = $conn->prepare("UPDATE tickets SET comentarios = 'Alerta de cierre activada' WHERE id_ticket = '$id_ticket'");
        $stmt->execute();
        echo 'OK';
    } catch (PDOException $e) {
        echo 'ERROR: ' . $e;
    }

    exit();
}
