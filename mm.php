<?php
include('main_functions.php');

// MENSAJE MASIVO PARA USUARIOS DEL SISTEMA
# Mensaje para ser visualizado por el chat de usuarios

// CONEXION DB
$conexion = new Connection('config/config.json');
$conn = $conexion->db_conn();

$stmt = $conn->prepare("SELECT usuario FROM usuarios");
$stmt->execute();

while($usuario = $stmt->fetch(PDO::FETCH_ASSOC)){

    $msj = 'Hola, ahora puedes enviar mensajes a otros usuarios de  la empresa registrados en el sistema, esto con la finalidad de centralizar las comunicaciones y disminuir el uso de software de terceros como whatsapp, skype y cualquier otro sistema ajeno a la empresa. Aparte de mensajes de texto también puedes adjuntar  archivos de tipo  txt, docx, xlsx, gif, pdf, jpg, png entre otros con un peso menor a 100 MB. 
    Cualquier duda comuníquese con el personal de sistemas.
    Att.: Railer Chalbaudt';

    $datos ['id_chat'] = uniqid();
    $datos ['emisor'] = 'root';
    $datos ['receptor'] = $usuario['usuario'];

    array_push($datos, NULL);
    array_push($datos, NULL);

    $mensaje = new Interchat($datos);
    $mensaje->info[1] = $msj;
    $mensaje->enviar_mensaje();
}

echo 'MENSAJE MASIVO ENVIADO';