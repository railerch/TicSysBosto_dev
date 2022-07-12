# FUNCIONES PRINCIPALES DEL SISTEMA

## INICIO
1. Registro de usuario
2. Inicio de sesión
    * Comprobación automatica de nombre de usuario.

## PANEL DE CONTROL DEL TÉCNICO
1. Visualización de estadisticas
Aqui se pueden ver los totales de tickets abiertos, en espera, pre-cerrados y cerrados, el historico de tickets creados y resueltos por mes .

2. Creación de tickets para usuarios
Este modulo esta pensado para generar los tickets que los usuarios no puedan generar por falta de conexión o problemas técnicos con sus equipos, de este modo en caso de que un usuario reporte un incidente se podra generar un ticket en su nombre para que cualquiera de los técnicos pueda tomarlo y resolverlo despues.

3. Tickets activos 
    * Chat con el usuario.
    * Envio de archivos adjuntos.
    * Alerta de usuario para cierre de ticket una ves resuelto el mismo.
    * Boton de cierre del ticket que se activara una vez el mismo es cerrado por el usuario.
    * Boton de eliminación del ticket en caso de no haber iniciado el chat.
    * Boton para poner el ticket en espera (en caso de ser el técnico a cargo).

4. Tickets en espera
    * Chat con el usuario.
    * Envio de archivos adjuntos.
    * Alerta de usuario para cierre de ticket una ves resuelto el mismo.

5. Tickets cerrados
    * Visualización del ticket y su solución.

6. Tickets eliminados
    * Ver tickets eliminados por los usuarios o cualquier técnico.

7. Tareas
    * Este modulo tiene dos caras:
        1. En la sesión del usuario ROOT se pueden crear tareas con valoraciones, un puntaje que se ira sumando al average del técnico que tome dicha tarea para resolverla en caso de que esta no haya sido asignada a alguien en especifico y sea una _Tarea libre_, adicionalmente se pueden adjuntar archivos en las tareas creadas.
        2. El la sesión de técnico se pueden ver las tareas generadas por el ROOT, una tarea se puede tomar o liberar haciendo doble clic en la misma. Se puede cambiar el estatus de la tarea segun sea el caso.

8. Reportes
    * Reporte de tickets por técnico
    * Reporte de tareas por técnico
    * Reportes de tickets por locación
    * Reporte general: este ultimo contiene estadisticas de todos los reportes anteriores

9. Configuraciones
    * Creación y modificación de usuarios
    * Creacion de locaciones, departamentos
    * Respaldo de la base de datos y descarga
    * Desconectar a todos los usuarios (en caso de errores de socket)

10. Cierre de sesión

## PANEL DE CONTROL DE GERENTE
1. Visualización de estadisticas
Aqui se pueden ver los totales de tickets abiertos, en espera, pre-cerrados y cerrados, el historico de tickets creados y resueltos por mes .

2. Creación de tickets para usuarios
Este modulo esta pensado para generar los tickets que los usuarios no puedan generar por falta de conexión o problemas técnicos con sus equipos, de este modo en caso de que un usuario reporte un incidente se podra generar un ticket en su nombre para que cualquiera de los técnicos pueda tomarlo y resolverlo despues.

3. Tickets activos 
    * Chat con el usuario.
    * Envio de archivos adjuntos.
    * Alerta de usuario para cierre de ticket una ves resuelto el mismo.
    * Boton de cierre del ticket que se activara una vez el mismo es cerrado por el usuario.
    * Boton de eliminación del ticket en caso de no haber iniciado el chat.
    * Boton para poner el ticket en espera (en caso de ser el técnico a cargo).

4. Tickets en espera
    * Chat con el usuario.
    * Envio de archivos adjuntos.
    * Alerta de usuario para cierre de ticket una ves resuelto el mismo.

5. Tickets cerrados
    * Visualización del ticket y su solución.

6. Tickets eliminados
    * Ver tickets eliminados por los usuarios o cualquier técnico.

7. Reportes
    * Reporte de tickets totales
    * Reporte de tickets por locacion

8. Datos de usuario
    * El usuario puede cambiar su clave de acceso y su ubicacion (oficina o sucursal) incluso puede eliminar su cuenta.

## PANEL DE CONTROL DE USUARIO
1. Crear ticket
    * El usuario podra crear cuantos tickets desee mientras no tenga _5 tickets_ con estatus activo, una vez alcanzado este limite no podra generar nuevos tickets, para esto debe cerrar los que ya hayan sido resueltos.
    * cada vez que intente crear un ticket el sistema le indicara la cantidad de tickets activos hasta ese momento.

2. Tickets activos
    * El usuario solo puede tener un maximo de _5_ tickets activos (con estatus abierto).
    * Chat con el técnico
    * Archivos adjuntos
    * Boton para cerrar el ticket
    * Boton para eliminar el ticket

3. Datos de usuario
    * El usuario puede cambiar su clave de acceso y su ubicacion (oficina o sucursal) incluso puede eliminar su cuenta.

4. Cierre de sesión

## NOTAS GENERALES
* El usuario principal del sistema es el _root_ este no esta registrado en la bd sino en los archivos de configuración
* Los nombres de usuario no pueden contener numeros (los filtros internos del codigo comparan IDs de tickets (numericos) con IDs de chats (cadenas))
* Cada _Dashboard_ cuenta con:
    * Una cabecera con el tiempo de la sesion, un icono de usuario, el nombre del usuario, el depto al que pertenece y el logo de la empresa
    * Un panel de botones en el lateral izquierdo para accesar a cada modulo del sistema.
    * En el caso del dashboard de técnico al pasar el mouse sobre el icono de usuario se despliega la lista de usuarios activos, desconectados y total registrados.

* Los niveles de acceso:
    * ROOT: es el usuario principal con acceso a todas las caracteristicas del sistema.
    * TÉCNICO: son los encargados de resolver los tickets generados por los usuarios.
    * USUARIO: es el mas basico, son aquellos que generan los tickets que deben resolver los técnicos o gerentes.
    * GERENTE: es un usuario especial que se crea para un departamento que tambien requiera llevar en registro de problemas reportados mediente el sistema de tickets, este usuario tiene un _Dashboard_ independiente al del técnico pero con menos privilegios.
* Si el usuario con nivel de técnico no pertenece al depto de sistemas el mismo vera un error al iniciar sesión indicandole que su nivel de usuario no corresponde con el departamento al que pertenece.
* Las estadisticas de técnicos y gerentes son independientes, es decir, cada depto solo vera sus propias estadisticas.

## NOTAS SOBRE LOS TICKETS
* Los tickets nuevos se muestran en color verde.
* Un ticket solo puede ser eliminado si no se ha iniciado el chat.
* Un técnico debe hacer doble clic en un ticket entrante para tomarlo y procesarlo.
* Un técnico puede tomar el ticket de otro haciendo doble clic en el mismo, esto en caso de que el técnico a cargo no pueda resolver el ticket en cuestion.
* Si no es el técnico a cargo del ticket no podra interactuar en el chat del mismo.
* Se pueden adjuntar los siguientes tipos de archivo: jpg, png, bmp, gif, txt, xlsx, docx, pdf, sql.
* Un técnico no puede cerrar un ticket a menos que el usuario lo haya confirmado como resuelto y haga
el precierre con el boton correspondiente.
* Un ticket no puede ser puesto en espera a menos que tenga un técnico a cargo.

## NOTAS SOBRE LAS TAREAS
* Las tareas nuevas se muestran en color morado.
* Cada tarea se puede tomar o liberar haciendo doble clic en la misma.
* Las tareas tienen estatus que se pueden modificar segun el progreso de la misma.
* Una tarea no puede ser modificada por el root una vez este en proceso.
