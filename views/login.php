<?php
include_once('../main_functions.php');

// CARGAR CONFIG
$config = cargar_config(true);

?>

<div class="login-dark" style="height:100vh;">
    <form method="POST" action="<?php echo htmlspecialchars('main_controller.php?login=true') ?>" style="padding: 1em;padding-bottom: 1em;">
        <h2 class="sr-only">Login Form</h2>
        <div class="illustration"><img class="img-fluid" src="<?php echo $config[2]->logo ?>" style="height:20vh;">
            <h5 style="color: rgb(255,255,255);">Ingreso de usuario</h5>
        </div>
        <div class="form-group"><input class="form-control" type="text" name="usuario" placeholder="Nombre de usuario" autocomplete="off" required></div>
        <div class="form-group"><input class="form-control" type="password" name="clave" placeholder="Clave" autocomplete="off" required></div>
        <div class="form-group"><button class="btn btn-primary btn-block" type="submit">Iniciar sesión</button>
        </div>

        <!--
            <a class="forgot" href="#/">Olvidaste tu contraseña?</a>
            -->
        <a id="registrar" href="#/" class="forgot"><i class="fa fa-user-plus"></i>&nbsp;Registrarse</a>

    </form>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // VERIFICAR QUE EL USUARIO ESTE REGISTRADO
        $("input[name=usuario]").blur(function() {
            if ($("input[name=usuario]").val() != "" && $("input[name=usuario]").val() != 'root') {
                var usuario = $("input[name=usuario]").val();
                $.ajax({
                    type: "GET",
                    url: `main_controller.php?usuario=${usuario}&nombreUsuario=true`,
                    success: function(data) {
                        console.log(data);
                        if (data != 1) {
                            $("input[name=usuario]").val(null);
                            $("input[name=usuario]").css("background", "#720000");
                            $("input[name=usuario]").attr("placeholder", "Usuario no registrado");
                            $("input[name=usuario]").focus();
                        } else {
                            $("input[name=usuario]").css("background", "");
                        }
                    }
                })
            } else {
                $("input[name=usuario]").css("background", "");
                $("input[name=usuario]").attr("placeholder", "Nombre de usuario");
            }

        })

        // MOSTRAR FORMULARIO DE REGISTRO
        $("#registrar").click(function() {
            $(".container").load("views/registrar.php")
        })

    })
</script>