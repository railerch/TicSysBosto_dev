# PENDIENTE POR HACER
5. Agregar chat individual entre usuarios
    * En la clase 'USUARIO' metodo 'Usuarios_activos' evitar marcar como true la variable que indica si el usuario de ese ciclo tiene mensajes no leidos del root, esto si no es el usuario de la sesion (Es algo que no afecta pero que no deberia ser)

7. Crear el panel de personalizacion del sistema para modificar el JSON de configuraciones

8. Manejar los avisos y redirecciones en el controlador y no dentro de las clases
9. Refactorizar 2da vuelta

10. Crear clase "reporte"

11. Activar notificaciones para nuevos tickets, msjs y tareas que se hayan creado con el usuario estando offline de modo que al iniciar sesion se le indique que tiene nuevo, para esto seria bueno agregar un campo adicional en la tabla de usuarios con la fecha de la ultima sesion para asi al iniciar buscar todo lo que se haya creado entre esa fecha y la actual.

12. Evitar que un usuario con sesion activa pueda iniciar otra sesion en paralelo en el mismo equipo o uno diferente