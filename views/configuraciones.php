<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// MOSTRAR DEPARTAMENTOS Y EMPRESAS PARA EL FORMULARIO DE REGISTRO
$stmt = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'empresa'");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();

// CONSULTAR USUARIOS REGISTRADOS
$stmt_1 = $conn->prepare("SELECT * FROM usuarios ORDER BY nombre ASC");
$stmt_1->setFetchMode(PDO::FETCH_ASSOC);
$stmt_1->execute();

?>
<style>
    #datosUsuario * {
        margin-bottom: 0.5em;
    }
</style>
<div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link active" role="tab" data-toggle="tab" href="#tab-1" style="color:#fff"><i class="fa fa-user-o" style="color:#fff"></i>&nbsp;Usuarios</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" role="tab" data-toggle="tab" href="#tab-2" style="color:#fff"><i class="fa fa-paperclip" style="color:#fff"></i>&nbsp;Miscelaneos</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" role="tab" data-toggle="tab" data-name="logs" href="#tab-3" style="color:#fff"><i class="fa fa-edit" style="color:#fff"></i>&nbsp;Logs</a></li>
        </li>
    </ul>
    <div class="tab-content">
        <!-- PESTAÑA 01 - USUARIOS -->
        <div class="tab-pane active" role="tabpanel" id="tab-1">
            <div style="background: #505050;padding: 0.5em;border-radius: 0 0 1em 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
                <i class="fa fa-user-o" style="font-size: 5vw;margin-right: 0.3em;"></i>
                <h1 class="d-inline-block">Usuarios</h1>
                <hr style="background: #969696;">
                <div id="formulario">
                    <form id="regForm" class="form-inline" style="background: #7c7c7c;border-radius: 10px;padding: 1em;">
                        <h3>Registro de usuario<br></h3>
                        <div class="table-responsive table-borderless">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><input id="nombre" class="form-control" type="text" name="nombre" placeholder="Nombre y Apellido"></td>
                                        <td><input id="usuario" class="form-control" type="text" name="usuario" placeholder="Nombre de usuario"></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select class="form-control" name="empresa" id="empresa">
                                                <option style="color:#aaa">Seleccione la empresa</option>
                                                <!-- EMPRESA -->
                                                <?php while ($empresa = $stmt->fetch()) {
                                                    echo "<option value='{$empresa["descripcion"]}' style='color:#555'>{$empresa['descripcion']}</option>";
                                                } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" name="depto" id="depto">
                                                <option style="color:#aaa">Departamento</option>
                                                <!-- DEPARTAMENTOS -->
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select id="nivel" class="form-control" name="nivel" required>
                                                <option style="color:#aaa" value="Nivel de usuario" selected>Nivel de
                                                    usuario</option>
                                                <option value="admin">Admin</option>
                                                <option value="gerente">Gerente</option>
                                                <option value="analista">Analista</option>
                                                <option value="usuario">Usuario comun</option>
                                            </select>
                                        </td>
                                        <td><input id="clave" class="form-control" type="password" name="clave" placeholder="Clave">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button id="registrarBtn" class="btn btn-primary btn-block" type="submit">&nbsp;Registrar
                            usuario</button>
                    </form>
                </div>

                <!-- TABLA DE USUARIOS REGISTRADOS -->
                <h3 style="margin: 1em 0;">Usuarios registrados<br></h3>
                <div class="text-light table-striped" style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em">
                    <table id="usReg" class="table table-bordered">
                        <thead>
                            <tr style="text-align: center;background: #505050;color: rgb(255,255,255);">
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Empresa</th>
                                <th>Departamento</th>
                                <th>Nivel</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($usuario = $stmt_1->fetch()) {
                                if ($usuario['usuario'] != "root") {
                            ?>
                                    <tr style="text-align: center;">
                                        <td><?php echo $usuario['id_usuario'] ?></td>
                                        <td><?php echo $usuario['nombre'] ?></td>
                                        <td><?php echo $usuario['usuario'] ?></td>
                                        <td><?php echo $usuario['empresa'] ?></td>
                                        <td><?php echo $usuario['depto'] ?></td>
                                        <td><?php echo $usuario['nivel'] ?></td>
                                        <td>
                                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                                <div class="btn-group" role="group">
                                                    <a class="btn btn-outline-primary btn-sm editarUsuario" data-user-id="<?php echo $usuario['id_usuario'] ?>" role="button" data-toggle="modal" data-bs-tooltip="" title="Editar datos de usuario" href="#/" data-target="#actualizarDatos">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <?php
                                                    if ($usuario['usuario'] == "root") {
                                                        $ocultar = 'style="display:none"';
                                                    } else {
                                                        $ocultar = NULL;
                                                    }
                                                    ?>
                                                    <a class="btn btn-outline-danger btn-sm eliminarUsuario" <?php echo $ocultar ?> data-user-id="<?php echo $usuario['id_usuario'] ?>" role="button" data-toggle="modal" data-bs-tooltip="" title="Eliminar usuario" href="#/" data-target="#eliminarUsuario">
                                                        <i class="fa fa-trash-o"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                            <?php }
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- PESTAÑA 02 - MISCELANEOS -->
        <div class="tab-pane" role="tabpanel" id="tab-2">
            <div style="background: #505050;padding: 0.5em;border-radius: 0 0 1em 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
                <i class="fa fa-paperclip" style="font-size: 5vw;margin-right: 0.3em;"></i>
                <h1 class="d-inline-block">Miscelaneos</h1>
                <hr style="background: #969696;">
                <div id="misceForms" class="d-flex flex-wrap">
                    <!-- CREAR EMPRESA -->
                    <form id="crearEmpresa" class="form-block mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%">
                        <h3>Crear empresa</h3>
                        <input id="empresa_2" class="form-control" type="text" name="empresa" data-tabla="miscelaneos" data-tipo="empresa" placeholder="Nombre empresa">
                        <span id="aviso" style="color:lightgreen"></span>
                        <br>
                        <button class="btn btn-primary btn-inline crearEmpresa" data-frm="crearEmpresa">Confirmar empresa</button>
                    </form>

                    <!-- CREAR DEPARTAMENTO -->
                    <form id="crearDepto" class="form-block mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%">
                        <h3>Crear departamento</h3>
                        <select class="form-control" name="empresa" id="empresaDepto" data-frm="crearDepto" data-next="departamento">
                            <option style="color:#aaa" value="">Empresa</option>
                            <!-- EMPRESA -->
                            <?php
                            $stmt = $conn->query("SELECT descripcion FROM miscelaneos WHERE tipo = 'empresa'");
                            while ($empresa = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$empresa["descripcion"]}' style='color:#555'>{$empresa['descripcion']}</option>";
                            } ?>
                        </select>
                        <!-- DEPTO -->
                        <input id="departamento" class="form-control mt-2" type="text" name="departamento" data-frm="crearDepto" data-tabla="miscelaneos" data-tipo="depto" placeholder="Nombre depto" disabled>
                        <span id="avisoDpt" style="color:lightgreen"></span>
                        <br>
                        <button class="btn btn-primary btn-inline crearDepto" data-frm="crearDepto">Confirmar departamento</button>
                    </form>

                    <!-- CREAR CATEGORIA -->
                    <form id="crearCat" class="form-block mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%;">
                        <h3>Crear categoría</h3>
                        <select class="form-control" name="empresa" id="empresaCat" data-frm="crearCat" data-next="deptoCat">
                            <option style="color:#aaa" value="">Empresa</option>
                            <!-- EMPRESA -->
                            <?php
                            $stmt_e = $conn->query("SELECT descripcion FROM miscelaneos WHERE tipo = 'empresa'");
                            while ($empresa = $stmt_e->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$empresa["descripcion"]}' style='color:#555'>{$empresa['descripcion']}</option>";
                            } ?>
                        </select>
                        <select class="form-control mt-2" name="depto" id="deptoCat" data-frm="crearCat" data-next="categoria" disabled>
                            <option style="color:#aaa" value="">Departamento</option>
                            <!-- DEPTO -->
                        </select>
                        <!-- CAT -->
                        <input id="categoria" class="form-control mt-2" type="text" name="categoria" data-frm="crearCat" data-tabla="miscelaneos" data-tipo="cat" placeholder="Descripción categoría" disabled>
                        <span id="avisoCat" style="color:lightgreen"></span>
                        <br>
                        <button class="btn btn-primary btn-inline crearCat" data-frm="crearCat">Confirmar categoría</button>
                    </form>

                    <!-- RESPALDAR BD -->
                    <form id="respaldarBd" class="form-block d-flex flex-column mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%;">
                        <h3>Respaldar Base de Datos<br></h3>
                        <span id=" avisoBD" style="color:lightgreen; height:2vh"></span>
                        <br>
                        <button class="btn btn-primary btn-inline respaldarBD">Respaldar BD</button>
                        <button class="btn btn-primary btn-inline verRespaldos" data-toggle="modal" data-target="#listaRespaldos" style="margin-top: 10px;">Lista de respaldos</button>
                    </form>
                </div>

                <br>

                <div style="background: #505050;border-radius: 10px;padding: 1em;">
                    <button id="desconectarUsuarios" class="btn btn-danger btn-inline">Desconectar todos los
                        usuarios</button>
                    <span id="avisoDelUsr" style="color:lightgreen; margin-left: 1em;"></span>
                </div>
            </div>
        </div>
        <!-- PESTAÑA 03 - LOGS -->
        <div class="tab-pane" role="tabpanel" id="tab-3">
            <div style="background: #505050;padding: 0.5em;border-radius: 0 0 1em 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
                <i class="fa fa-edit" style="font-size: 5vw;margin-right: 0.3em;"></i>
                <h1 class="d-inline-block">Registro de actividad</h1>
                <hr style="background: #969696;">
                <div id="logs" class="table-striped" style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
                    <!-- LOGS DEL SYSTEMA -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pagina = 'configuraciones.php';
include('ventanasModal.php');
?>

<?php
avisos(@$_SESSION['avisos']);
ocultar_aviso();
?>

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        // IDIOMA ESPAÑOL PARA DATATABLES
        $("#usReg, #sysLogs").DataTable({
            "language": {
                "url": "config/dataTableSpanish.json"
            },
            "order": [
                [1, "desc"]
            ]
        });

        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/configuraciones.php");

        // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
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
                                $("input[name='usuario']").css("color", "unset");
                            }
                        }
                    })
                } else {
                    $("input[name='usuario']").css("background", "");
                    $("input[name='usuario']").css("color", "unset");
                    $("input[name='usuario']").attr("placeholder", "Nombre de usuario")
                }
            }
        })

        // HABILITAR CAMPOS EN MISCELANEOS LUEGO DE HACER LAS SELECCIONES OBLIGATORIAS
        $("select").change(function() {
            let empresa = $(this).val();
            let frm = $(this).attr("data-frm");
            let el = $(this).attr("data-next");
            let depto = document.getElementById(el);
            if (empresa) {
                $(`#${frm} #${el}`).attr("disabled", false);

                // cargar deptos en caso de estar creando una categoria
                if (frm == "crearCat") {
                    fetch(`main_controller.php?empresaDeptos=true&empresa=${empresa}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.length > 0) {
                                depto.innerHTML = "<option style='color:#aaa' selected>Departamento</option>";
                                data.forEach(dep => {
                                    let opt = document.createElement("option");
                                    opt.setAttribute("value", dep);
                                    opt.innerText = dep;
                                    depto.append(opt);
                                })
                            } else {
                                depto.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                                console.warn("AVISO: sin registros activos.")
                            }
                        })
                }
            } else {
                $(`#${frm} #${el}`).val("");
                $(`#${frm} #${el}`).attr("disabled", true);
                if (frm == "crearCat") {
                    depto.innerHTML = "<option style='color:#aaa' selected>Departamento</option>";
                    $(`#${frm} #categoria`).attr("disabled", true);
                }
            }
        })

        // COMPROBAR DISPONIBILIDAD DE MISCELANEOS
        $("input[data-tabla=miscelaneos]").keyup(function() {
            let placeholder = $(this).attr("placeholder");
            let tipo = $(this).attr("data-tipo");
            let frm = $(this).attr("data-frm");
            let val = "";

            switch (tipo) {
                case "empresa":
                    val = $(this).val();
                    break;
                case "depto":
                    let empresaD = $(`#${frm} #empresaDepto`).val();
                    let depto = $(this).val();
                    val = `${empresaD}-${depto}`;
                    break;
                case "cat":
                    let empresaC = $(`#${frm} #empresaCat`).val();
                    let deptoC = $(`#${frm} #deptoCat`).val();
                    let cat = $(this).val();
                    val = `${empresaC}-${deptoC}-${cat}`;
                    break;
            }

            // Restablecer campo
            function restablecer_campo() {
                $(`input[data-tipo=${tipo}]`).attr("placeholder", `${placeholder}`);
                $(`input[data-tipo=${tipo}]`).css("color", "#333");
                $(`input[data-tipo=${tipo}]`).css("background", "#fff");
            }

            // Consultar
            if (val) {
                $.ajax({
                    url: `main_controller.php?comprobarMiscelaneo=true&tipo=${tipo}&val=${val}`,
                    success: function(data) {
                        if (data) {
                            $(`input[data-tipo=${tipo}]`).val(null);
                            $(`input[data-tipo=${tipo}]`).css("background", "#720000");
                            $(`input[data-tipo=${tipo}]`).css("color", "#fff");
                            $(`input[data-tipo=${tipo}]`).attr("placeholder", `${val} ya esta creado!`);
                            $(`input[data-tipo=${tipo}]`).focus();
                            setTimeout(function() {
                                restablecer_campo();
                            }, 3000)
                        } else {
                            $(`input[data-tipo=${tipo}]`).css("color", "#333");
                            $(`input[data-tipo=${tipo}]`).css("background", "#fff");
                        }
                    }
                })
            } else if (val == "") {
                restablecer_campo();
            }

            $(this).css("color", "#333");
            $(`input[data-tipo=${tipo}]`).css("background", "#fff");

        })

        // REGISTRO DE USUARIO: CONSULTAR DEPTOS SEGUN LA EMPRESA SELECCIONADA
        $("#empresa").change(function() {
            let empresa = $(this).val();
            let depto = document.getElementById("depto");
            fetch(`main_controller.php?empresaDeptos=true&empresa=${empresa}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length) {
                        depto.innerHTML = "<option style='color:#aaa' selected>Departamento</option>";
                        data.forEach(dep => {
                            let opt = document.createElement("option");
                            opt.setAttribute("value", dep);
                            opt.innerText = dep;
                            depto.append(opt);
                        })
                    } else {
                        depto.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                        console.warn("AVISO: sin registros activos.")
                    }
                })
        });

        // REGISTRAR USUARIO
        $("#registrarBtn").click(function(event) {

            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("nombre", "") ?>
            <?php echo validar_selecciones("usuario", "") ?>
            <?php echo validar_selecciones("empresa", "Seleccione la ubicación") ?>
            <?php echo validar_selecciones("depto", "Seleccione el departamento") ?>
            <?php echo validar_selecciones("nivel", "Nivel de usuario") ?>
            <?php echo validar_selecciones("clave", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 6) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?registrarUsuario=true",
                    data: $("#regForm").serialize(),
                    success: function(data) {
                        console.log(data);
                        $("#contenido").load("views/configuraciones.php");
                        localStorage.clear();
                    }
                });
            }

        })

        // EDITAR DATOS DE USUARIO
        $(".editarUsuario").click(function() {

            // REINICIAR DATOS DEL FORMULARIO
            $("#datosUsuario input").val(null);

            var id = $(this).attr("data-user-id");
            $("#datosUsuario input[name=id]").attr("value", id);
            $.ajax({
                type: "GET",
                url: "main_controller.php?consultarUsuario=true&id_usuario=" + id,
                success: function(data) {
                    var datos = JSON.parse(data);
                    var nombre = datos["nombre"];
                    var usuario = datos["usuario"];
                    var clave = datos["clave"];

                    // VALORES POR DEFECTO EN VENTANA MODAL PARA EDITAR DATOS DE USUARIO
                    $("#datosUsuario input[name=nombre]").val(nombre);
                    $("#datosUsuario input[name=usuario]").val(usuario);
                    $("#datosUsuario input[name=clave]").val(clave);

                }
            })

            // CARGAR DEPTOS AL CAMBIAR LA EMPRESA
            $("#editUsrEmpresa").change(function() {
                let empresa = $(this).val();
                fetch(`main_controller.php?empresaDeptos=true&empresa=${empresa}`)
                    .then(res => res.json())
                    .then(res => {
                        console.log(res);
                    });
            });
            // ACTUALIZAR DATOS DE USUARIO
            $(".actualizarBtn").click(function() {
                $.ajax({
                    type: "POST",
                    url: `main_controller.php?id=${id}&actualizarDatos=true`,
                    data: $("#datosUsuario").serialize(),
                    success: function(data) {
                        console.log(data);
                        setTimeout(function() {
                            $("#contenido").load("views/configuraciones.php");
                        }, 300)
                    }
                })
            })
        })

        // DESCONECTAR A TODOS LOS USUARIOS
        $("#desconectarUsuarios").click(function() {
            $.ajax({
                type: "GET",
                url: "main_controller.php?desconectarUsuarios=true",
                success: function(data) {
                    console.log(data);
                    $("#avisoDelUsr").text(data);
                    setTimeout(function() {
                        $("#avisoDelUsr").text("");
                    }, 3000)
                },
                error: function(e) {
                    console.log("ERROR: \n", e)
                }
            })
        })

        // ELIMINAR CUENTA
        $(".eliminarUsuario").click(function() {
            var id = $(this).attr("data-user-id");
            $(".eliminarConfirmacion").attr("data-user-id", id);

            $(".eliminarConfirmacion").click(function() {
                var id = $(this).attr("data-user-id");
                $.ajax({
                    type: "GET",
                    url: `main_controller.php?eliminarCuenta=true&id=${id}`,
                    success: function(data) {
                        console.log(data);
                        setTimeout(function() {
                            $("#contenido").load("views/configuraciones.php");
                        }, 500);
                    }
                })
            })

        })

        // CREAR EMPRESA
        $(".crearEmpresa").click(function() {

            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("empresa_2", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 1) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearEmpresa=true",
                    data: $("#crearEmpresa").serialize(),
                    success: function(data) {
                        console.log(data);

                        $("#empresa_2").val("");

                        $("#aviso").text(data);

                        $("#crearEmpresa input").css("color", "#333");

                        setTimeout(function() {
                            $("#aviso").text("");
                        }, 3000)

                        localStorage.clear();
                    }
                })
            }

        })

        // CREAR DEPARTAMENTO
        $(".crearDepto").click(function() {

            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("departamento", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 1) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearDepto=true",
                    data: $("#crearDepto").serialize(),
                    success: function(data) {
                        console.log(data);

                        $("#empresaDepto").val("");
                        $("#departamento").val("");
                        $("#departamento").attr("disabled", true);

                        $("#avisoDpt").text(data);

                        $("#crearDepto input").css("color", "#333");

                        setTimeout(function() {
                            $("#avisoDpt").text("");
                        }, 3000)

                        localStorage.clear();
                    }
                })
            }

        })

        // CREAR CATEGORIA
        $(".crearCat").click(function() {

            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("categoria", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 1) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearCat=true",
                    data: $("#crearCat").serialize(),
                    success: function(data) {
                        console.log(data);

                        $("#empresaCat").val("");
                        $("#deptoCat, #categoria").val("").attr("disabled", true);

                        $("#avisoCat").text(data);

                        $("#crearCat input").css("color", "#333");

                        setTimeout(function() {
                            $("#avisoCat").text("");
                        }, 3000)

                        localStorage.clear();
                    }
                })
            }

        })

        // CREAR AREA DE REQUERIMIENTO
        $(".crearArea").click(function() {

            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("area", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 1) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearArea=true",
                    data: $("#crearArea").serialize(),
                    success: function(data) {
                        console.log(data);

                        $("#area").val("");

                        $("#avisoArea").text(data);

                        $("#crearArea input").css("color", "#333");

                        setTimeout(function() {
                            $("#avisoArea").text("");
                        }, 3000)

                        localStorage.clear();
                    }
                })
            }

        })

        // RESPALDAR BD
        $(".respaldarBD").click(function() {

            event.preventDefault();

            $.ajax({
                type: "GET",
                url: "main_controller.php?respaldarBD=true",
                success: function(data) {
                    console.log(data);

                    $("#avisoBD").text(data);

                    setTimeout(function() {
                        $("#avisoBD").text("");
                    }, 3000)
                }
            })
        })

        // VER LISTA DE RESPALDOS
        $(".verRespaldos").click(function() {
            event.preventDefault();

            $.ajax({
                type: "GET",
                url: "main_controller.php?verRespaldos=true",
                success: function(data) {
                    if (data == "<ul></ul>") {
                        $("#listaRespaldos .modal-body").html("<p>Sin respaldos que mostrar</p>");
                    } else {
                        $("#listaRespaldos .modal-body").html(data);
                    }
                }
            })
        })

        // VER LOGS DEL SISTEMA
        $("li a[data-name=logs]").click(function() {
            $("#logs").load("views/log.php");
        })

    })
</script>