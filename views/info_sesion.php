<?php
session_start();
// INFORMACIÓN DE LA SESIÓN
echo "ID DE SESION: ".@$_SESSION['id_sesion'].'<br>';
echo "NOMBRE: ".@$_SESSION['nombre'].'<br>';
echo "DEPARTAMENTO: ".@$_SESSION['depto'].'<br>';
echo "LOCACION: ".@$_SESSION['locacion'].'<br>';
echo "NOMBRE DE USUARIO: ".@$_SESSION['usuario'].'<br>';
echo "NIVEL DE SESION: ".@$_SESSION['nivel'].'<br>';
echo "ESTATUS DE SESION: ".@$_SESSION['sesion_estatus'].'<br>';
