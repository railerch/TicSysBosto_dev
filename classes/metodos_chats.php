<?php
/**
 * INTERFACE compartida para los chats de tickets e interusuarios
*/

interface metodos_chats{
    public function enviar_mensaje();
    public function adjuntar_archivo(array $archivo);
    public function recuperar_mensajes($id);
}