<?php
include('main_functions.php');

// TOKEN PARA HABILITAR EL ACCESO AL LOGIN Y REGISTRO
$_SESSION['temp_token'] = md5(uniqid());

// CONEXION DB
$conexion = new Connection('config/config.json');
$conn = $conexion->db_conn();

// CARGAR CONFIG
$config = cargar_config();

?>

<!DOCTYPE html>
<html>

<head>
    <?php include('views/headTags.php') ?>
    <style>
        @keyframes fondoIndex {
            0% {
                background: url(<?php echo $config[2]->fondo ?>) center center;
            }

            50% {
                background: url(<?php echo $config[2]->fondo ?>) bottom center;
            }

            100% {
                background: url(<?php echo $config[2]->fondo ?>) center center;
            }
        }

        #fondoIndex {
            position: absolute;
            width: 100%;
            height: 100%;
            filter: blur(2px);
            animation-name: fondoIndex;
            animation-duration: 30s;
            animation-iteration-count: infinite;
        }

        #logo {
            width: 45%;
            margin-bottom: 5vh;
        }
    </style>
</head>

<body class="d-flex d-lg-flex justify-content-center align-items-center justify-content-lg-center align-items-lg-center" style="background: rgb(5,0,0);height: 100vh;">
    <div id="fondoIndex"></div>
    <div class="d-flex d-lg-flex justify-content-lg-center align-items-lg-center">
        <div class="container">
            <!-- CONTENIDO -->
            <?php include('views/indexStart.php') ?>
        </div>
    </div>
    <footer>
        <?php include('views/footerScripts.php') ?>

        <?php
        avisos(@$_SESSION['avisos']);
        ocultar_aviso();
        include('views/footerScripts.php')
        ?>

    </footer>

    <script type="text/javascript">
        $(document).ready(function() {
            // LIMPIAR DATOS DE SESION ALMACENADOS EN EL NAVEGADOR
            sessionStorage.clear();

            // MOSTRAR FORMULARIO PARA INICIO DE SESION
            $("#entrar").click(function() {
                $(".container").load("views/login.php")
            })
        })
    </script>

</body>

</html>