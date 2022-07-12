<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

// MOSTRAR DEPARTAMENTOS Y LOCACIONES
$stmt = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'locacion'");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

$stmt_2 = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'depto' ORDER BY descripcion ASC");
$stmt_2->setFetchMode(PDO::FETCH_ASSOC);
$stmt_2->execute();

// CARGAR CONFIG
$config = cargar_config(true);

?>

<div class="login-dark" style="height: 100vh;">
    <form method="POST" action="<?php echo htmlspecialchars("main_controller.php?registrarUsuario=true") ?>" style="padding: 1em;padding-bottom: 1em;">
        <h2 class="sr-only">Login Form</h2>
        <div class="illustration" style="padding-top: 0;padding-bottom: 0;"><img class="img-fluid" src="<?php echo $config[2]->logo ?>" style="height:20vh;">
            <h5 style="color: rgb(255,255,255);">Registro de usuario<br></h5>
        </div>
        <div class="form-group">
            <select class="form-control" name="locacion" id="locacion">
                <option style="color:#555" selected>Seleccione su ubicación</option>
                <!-- UBICACIONES -->
                <?php while ($locacion = $stmt->fetch()) {
                    echo "<option value='{$locacion["descripcion"]}' style='color:#555'>{$locacion['descripcion']}</option>";
                } ?>
            </select>
        </div>
        <!-- NOMBRE -->
        <div class="form-group">
            <input class="form-control" type="text" name="nombre" placeholder="Nombre y Apellido" autocomplete="off" required>
        </div>
        <!-- DEPARTAMENTOS -->
        <div class="form-group">
            <select class="form-control" name="depto" id="depto">
                <option style="color:#555" selected>Seleccione su departamento</option>
                <?php while ($depto = $stmt_2->fetch()) {
                    echo "<option value='{$depto["descripcion"]}' style='color:#555'>{$depto['descripcion']}</option>";
                } ?>
            </select>
        </div>
        <!-- NOMBRE USUARIO -->
        <div class="form-group">
            <input class="form-control" type="text" name="usuario" placeholder="Nombre de usuario" autocomplete="off" required>
        </div>
        <!-- CLAVE -->
        <div class="form-group">
            <input class="form-control" type="password" name="clave" placeholder="Clave" autocomplete="off" required>
        </div>
        <!-- CONFIRMAR CLAVE -->
        <div class="form-group">
            <input class="form-control" type="password" name="confirmacion" placeholder="Confirmar clave" autocomplete="off" required>
        </div>
        <!-- BOTONES -->
        <div class="form-group">
            <button class="btn btn-primary btn-block" type="submit">Registrarse</button>
        </div><a id="login" href="#/" class="forgot"><i class="fa fa-sign-in"></i>&nbsp;Iniciar sesión<br></a>
    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        // VACIAR EL LOCAL STORAGE
        localStorage.clear();

        // VALIDAR EL NOMBRE DE USUARIO 
        $("input[name='usuario']").blur(function() {

            // QUE SEA SOLO LETRAS Aa-Zz
            var usuario = $("input[name='usuario']").val()
            var patt = /[0-9_. -]/;
            var pattRoot = /^root/i;
            var pattAdmin = /^admin/i;

            if (patt.test(usuario)) {
                $("input[name='usuario']").val("")
                $("input[name='usuario']").css({
                    "background": "#720000",
                    "color": "#fff"
                });
                $("input[name='usuario']").attr("placeholder", "Solo letras A-z")
            } else if (pattRoot.test(usuario) || pattAdmin.test(usuario)) {
                $("input[name='usuario']").val("")
                $("input[name='usuario']").css({
                    "background": "#720000",
                    "color": "#fff"
                });
                $("input[name='usuario']").attr("placeholder", "Usuario no permitido")
            } else {
                // COMPROBAR DISPONIBILIDAD DEL NOMBRE
                if (usuario != "") {
                    $.ajax({
                        url: `main_controller.php?nombreUsuario=true&usuario=${usuario}`,
                        success: function(data) {
                            if (data == 1) {
                                $("input[name=usuario]").val(null);
                                $("input[name=usuario]").css("background", "#720000");
                                $("input[name=usuario]").attr("placeholder", "Usuario no disponible");
                                $("input[name=usuario]").focus();
                            } else {
                                $("input[name='usuario']").css("background", "");
                                $("input[name='usuario']").css("color", "#fff");
                            }
                        }
                    })
                } else {
                    $("input[name='usuario']").css("background", "");
                    $("input[name='usuario']").css("color", "#fff");
                    $("input[name='usuario']").attr("placeholder", "Nombre de usuario")
                }
            }
        })

        // VALIDAR CLAVE CON CONFIRMACION
        $("input[name=confirmacion]").blur(function() {
            if ($("input[name=clave]").val() != $("input[name=confirmacion]").val()) {
                $("input[name=confirmacion]").val(null);
                $("input[name=confirmacion]").attr("placeholder", "Las claves no coinciden");
            }
        })

        // VALIDAR SELECCIONES
        $("button[type=submit]").click(function() {
            <?php echo validar_selecciones("locacion", "Seleccione su ubicación") ?>
            <?php echo validar_selecciones("depto", "Seleccione su departamento") ?>
        })

        // VOLVER AL LOGIN
        $("#login").click(function() {
            $(".container").load("views/login.php")
        })

    })
</script>