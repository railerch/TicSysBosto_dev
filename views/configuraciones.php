<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

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
        <li class="nav-item" role="presentation"><a class="nav-link active" role="tab" data-toggle="tab" href="#tab-1" style="color:#fff"><i class="fa fa-user" style="color:#fff"></i>&nbsp;Usuarios</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" role="tab" data-toggle="tab" href="#tab-2" style="color:#fff"><i class="fa fa-archive" style="color:#fff"></i>&nbsp;Miscelaneos</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link" role="tab" data-toggle="tab" data-name="logs" href="#tab-3" style="color:#fff"><i class="fa fa-edit" style="color:#fff"></i>&nbsp;Logs</a></li>
        </li>
    </ul>
    <div class="tab-content">
        <!-- PESTAÑA 01 - USUARIOS -->
        <div class="tab-pane active" role="tabpanel" id="tab-1">
            <div style="background: #505050;padding: 0.5em;border-radius: 0 0 1em 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
                <i class="fa fa-user" style="font-size: 5vw;margin-right: 0.3em;"></i>
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
                                            <select class="form-control" name="empresa" id="regUsrEmpresa">
                                                <option style="color:#aaa" value="NULL">Seleccione la empresa</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" name="depto" id="regUsrDepto">
                                                <option style="color:#aaa" value="NULL">Departamento</option>
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
                <i class="fa fa-archive" style="font-size: 5vw;margin-right: 0.3em;"></i>
                <h1 class="d-inline-block">Miscelaneos</h1>
                <hr style="background: #969696;">
                <div id="misceForms" class="d-flex flex-wrap">
                    <!-- CREAR EMPRESA -->
                    <form id="crearEmpresa" class="form-block mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%">
                        <h3>Crear empresa</h3>
                        <input id="empresa_2" class="form-control" type="text" name="empresa" data-tabla="miscelaneos" data-tipo="empresa" placeholder="Nombre empresa">
                        <span id="avisoEmp"></span>
                        <br>
                        <button class="btn btn-primary btn-inline crearEmpresa" data-frm="crearEmpresa"><i class="fa fa-check"></i> Registrar</button>
                        <a class="btn btn-secondary btn-inline verEmpresas" href="#/" data-toggle="modal" data-target="#verEmpresas"><i class="fa fa-eye"></i> Empresas</a>
                    </form>

                    <!-- CREAR DEPARTAMENTO -->
                    <form id="crearDepto" class="form-block mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%">
                        <h3>Crear departamento</h3>
                        <select class="form-control" name="empresa" id="empresaDepto" data-frm="crearDepto" data-next="departamento">
                            <option style="color:#aaa" value="">Empresa</option>
                        </select>
                        <!-- DEPTO -->
                        <input id="departamento" class="form-control mt-2" type="text" name="departamento" data-frm="crearDepto" data-tabla="miscelaneos" data-tipo="depto" placeholder="Nombre depto" disabled>
                        <span id="avisoDpt"></span>
                        <br>
                        <button class="btn btn-primary btn-inline crearDepto" data-frm="crearDepto"><i class="fa fa-check"></i> Registrar</button>
                        <a class="btn btn-secondary btn-inline verDeptos" href="#/" data-toggle="modal" data-target="#verDeptos"><i class="fa fa-eye"></i> Departamentos</a>
                    </form>

                    <!-- CREAR CATEGORIA -->
                    <form id="crearCat" class="form-block mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%;">
                        <h3>Crear categoría</h3>
                        <select class="form-control" name="empresa" id="empresaCat" data-frm="crearCat" data-next="deptoCat">
                            <option style="color:#aaa" value="">Empresa</option>
                        </select>
                        <!-- DEPTO -->
                        <select class="form-control mt-2" name="depto" id="deptoCat" data-frm="crearCat" data-next="categoria" disabled>
                            <option style="color:#aaa" value="">Departamento</option>
                            <!-- DEPTO -->
                        </select>
                        <!-- CAT -->
                        <input id="categoria" class="form-control mt-2" type="text" name="categoria" data-frm="crearCat" data-tabla="miscelaneos" data-tipo="cat" placeholder="Descripción categoría" disabled>
                        <span id="avisoCat"></span>
                        <br>
                        <button class="btn btn-primary btn-inline crearCat" data-frm="crearCat"><i class="fa fa-check"></i> Registrar</button>
                        <a class="btn btn-secondary btn-inline verCats" href="#/" data-toggle="modal" data-target="#verCats"><i class="fa fa-eye"></i> Categorias</a>
                    </form>

                    <!-- RESPALDAR BD -->
                    <form id="respaldarBd" class="form-block d-flex flex-column mt-2" style="background: #7c7c7c;border-radius: 10px;padding: 1em; min-width: 30%;">
                        <h3>Respaldar Base de Datos<br></h3>
                        <span id="avisoBD" style="height:2vh"></span>
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

        // ======================================================================== REGISTRO USUARIO
        // VALIDAR EL NOMBRE DE USUARIO
        $("input[name='usuario']").keyup(function() {

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
                                $("input[name=usuario]").blur(function() {
                                    $("input[name='usuario']").css("background", "");
                                    $("input[name='usuario']").css("color", "unset");
                                    $("input[name='usuario']").attr("placeholder", "Nombre de usuario")
                                })
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

        // CARGAR EMPRESAS EN REGISTRO DE USUARIO
        let datosPhp = ["regUsrEmpresa", "Empresa", "empresasRegistradas"]
        opciones_select(...datosPhp)

        // CARGAR DEPTOS EN REGISTRO DE USUARIO SEGUN LA EMPRESA
        $("#regUsrEmpresa").change(function() {
            let empresa = $("#regUsrEmpresa").val();
            let datosPhp = ["regUsrDepto", "Departamento", "empresaDeptos", empresa]
            opciones_select(...datosPhp)
        })

        // REGISTRAR USUARIO
        $("#registrarBtn").click(function(event) {

            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("nombre", "") ?>
            <?php echo validar_selecciones("usuario", "") ?>
            <?php echo validar_selecciones("regUsrEmpresa", "NULL") ?>
            <?php echo validar_selecciones("regUsrDepto", "NULL") ?>
            <?php echo validar_selecciones("nivel", "Nivel de usuario") ?>
            <?php echo validar_selecciones("clave", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 6) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?registrarUsuario=true",
                    data: $("#regForm").serialize(),
                    success: function(data) {
                        $("#contenido").load("views/configuraciones.php");
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

                    // CARGAR EMPRESAS EN EDICION DE USUARIO
                    let datosPhp = ["editUsrEmpresa", "Seleccione la empresa", "empresasRegistradas"]
                    opciones_select(...datosPhp)

                    // CARGAR DEPTOS EN EDICION DE USUARIO SEGUN LA EMPRESA
                    $("#editUsrEmpresa").change(function() {
                        let empresa = $("#editUsrEmpresa").val();
                        let datosPhp = ["editUsrDepto", "Seleccione el depto", "empresaDeptos", empresa];
                        opciones_select(...datosPhp);
                    })

                    // VALORES POR DEFECTO EN VENTANA MODAL PARA EDITAR DATOS DE USUARIO
                    $("#editUsrNombre").val(datos["nombre"]);
                    $("#editUsrUsuario").val(datos["usuario"]);
                    $("#editUsrNivel").val(datos["nivel"]);
                    $("#editUsrClave").val(datos["clave"]);

                }
            })

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
                        setTimeout(function() {
                            $("#contenido").load("views/configuraciones.php");
                        }, 500);
                    }
                })
            })

        })

        // ======================================================================== MISCELANEOS
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
                            $(`input[data-tipo=${tipo}]`).attr("placeholder", "Definición no disponible!");
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

        // CONSULTA INTEGRAL PARA LISTAR EMPRESAS/DEPTOS/CATS
        function listar_empresas(idTabla, tipo) {
            fetch(`main_controller.php?empDepCat=true`)
                .then(res => res.json())
                .then(res => {
                    let tabla = document.querySelector(`#${idTabla} tbody`);
                    tabla.innerHTML = "";
                    res.forEach(tmp => {
                        if (tmp.tipo == `${tipo}`) {
                            let tr = document.createElement("tr");
                            tr.setAttribute("id", tmp.id);

                            let tdId = document.createElement("td");
                            let txtId = document.createTextNode(tmp.id);
                            tdId.appendChild(txtId);
                            tr.append(tdId);

                            switch (tipo) {
                                case "empresa":
                                    let tdEmpresa1 = document.createElement("td");
                                    let txtEmpresa1 = document.createTextNode(tmp.empresa);
                                    tdEmpresa1.appendChild(txtEmpresa1);
                                    tr.append(tdEmpresa1);
                                    break;

                                case "depto":
                                    let tdEmpresa2 = document.createElement("td");
                                    let txtEmpresa2 = document.createTextNode(tmp.empresa);
                                    tdEmpresa2.appendChild(txtEmpresa2);
                                    tr.append(tdEmpresa2);

                                    let tdDepto1 = document.createElement("td");
                                    let txtDepto1 = document.createTextNode(tmp.depto);
                                    tdDepto1.appendChild(txtDepto1);
                                    tr.append(tdDepto1);
                                    break;
                                case "cat":
                                    let tdEmpresa3 = document.createElement("td");
                                    let txtEmpresa3 = document.createTextNode(tmp.empresa);
                                    tdEmpresa3.appendChild(txtEmpresa3);
                                    tr.append(tdEmpresa3);

                                    let tdDepto2 = document.createElement("td");
                                    let txtDepto2 = document.createTextNode(tmp.depto);
                                    tdDepto2.appendChild(txtDepto2);
                                    tr.append(tdDepto2);

                                    let tdCat = document.createElement("td");
                                    let txtCat = document.createTextNode(tmp.cat);
                                    tdCat.appendChild(txtCat);
                                    tr.append(tdCat);
                                    break;
                            }

                            let tdAcciones = document.createElement("td");
                            let txtAcciones = document.createTextNode("Eliminar");
                            tdAcciones.setAttribute("class", "btn btn-danger btn-sm");
                            tdAcciones.setAttribute("data-id", tmp.id);
                            tdAcciones.style.width = "100%";
                            tdAcciones.style.textAlign = "center";
                            tdAcciones.style.cursor = "pointer";
                            tdAcciones.appendChild(txtAcciones);
                            tr.append(tdAcciones);

                            // Activar btn eliminar
                            tdAcciones.addEventListener("click", function() {
                                let id = this.getAttribute("data-id");
                                fetch(`main_controller.php?eliminarMiscelaneo=true&id=${id}`)
                                    .then(res => res.json())
                                    .then(res => {
                                        if (res == 1) {
                                            document.getElementById(id).remove();
                                            console.log("Registro eliminado de miscelaneos")
                                        };
                                    });
                            })

                            // Cargar fila en tabla
                            tabla.append(tr);
                        }
                    })
                })
        }
        $(".verEmpresas").click(function() {
            listar_empresas("empresasRegistradas", "empresa")
        })
        $(".verDeptos").click(function() {
            listar_empresas("empresaDeptos", "depto")
        })
        $(".verCats").click(function() {
            listar_empresas("deptoCats", "cat")
        })

        // CARGAR EMPRESAS EN CREAR DEPTO
        let datosPhpD = ["empresaDepto", "Seleccione la empresa", "empresasRegistradas"]
        opciones_select(...datosPhpD)

        // CARGAR EMPRESAS EN CREAR CAT
        let datosPhpC = ["empresaCat", "Seleccione la empresa", "empresasRegistradas"]
        opciones_select(...datosPhpC)

        // HABILITAR CAMPOS EN MISCELANEOS LUEGO DE HACER LAS SELECCIONES OBLIGATORIAS
        $("select").change(function() {
            let empresa = $(this).val();
            let frm = $(this).attr("data-frm");
            let el = $(this).attr("data-next");
            let depto = document.getElementById(el);
            if (empresa) {
                $(`#${frm} #${el}`).attr("disabled", false);

                // Cargar deptos en caso de estar creando una categoria
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
                    depto.setAttribute("disabled", true);
                    $(`#${frm} #categoria`).val("");
                    $(`#${frm} #categoria`).attr("disabled", true);
                }
            }
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

                        $("#avisoEmp").css("color", "lightgreen");
                        $("#avisoEmp").text(data);

                        $("#crearEmpresa input").css("color", "#333");

                        setTimeout(function() {
                            $("#avisoEmp").text("");
                        }, 3000)

                        localStorage.clear();

                        // Refrescar empresas en registro de usuarios
                        let datosPhpU = ["regUsrEmpresa", "Empresa", "empresasRegistradas"]
                        opciones_select(...datosPhpU)

                        // Refrescar empresas en departamentos
                        let datosPhpE = ["empresaDepto", "Empresa", "empresasRegistradas"]
                        opciones_select(...datosPhpE)

                        // Refrescar empresas en categorias
                        let datosPhpC = ["empresaCat", "Empresa", "empresasRegistradas"]
                        opciones_select(...datosPhpC)

                    }
                })
            } else {
                $("#avisoEmp").css("color", "crimson");
                $("#avisoEmp").text("Error al crear la empresa");

                setTimeout(function() {
                    $("#avisoEmp").text("");
                }, 3000)
            }

        })

        // CREAR DEPARTAMENTO
        $(".crearDepto").click(function() {
            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("empresaDepto", "") ?>
            <?php echo validar_selecciones("departamento", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 2) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearDepto=true",
                    data: $("#crearDepto").serialize(),
                    success: function(data) {
                        console.log(data);

                        $("#empresaDepto").val("");
                        $("#departamento").val("");
                        $("#departamento").attr("disabled", true);

                        $("#avisoDpt").css("color", "lightgreen");
                        $("#avisoDpt").text(data);

                        $("#crearDepto input").css("color", "#333");

                        setTimeout(function() {
                            $("#avisoDpt").text("");
                        }, 3000)

                        localStorage.clear();
                    }
                })
            } else {
                $("#avisoDpt").css("color", "crimson");
                $("#avisoDpt").text("Error al crear el departamento");

                setTimeout(function() {
                    $("#avisoDpt").text("");
                }, 3000)
            }
        })

        // CREAR CATEGORIA
        $(".crearCat").click(function() {

            // VALIDAR SELECCIONES
            <?php echo validar_selecciones("empresaCat", "") ?>
            <?php echo validar_selecciones("deptoCat", "") ?>
            <?php echo validar_selecciones("categoria", "") ?>

            event.preventDefault();

            if (localStorage.getItem("inputOK") == 3) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearCat=true",
                    data: $("#crearCat").serialize(),
                    success: function(data) {

                        $("#empresaCat").val("");
                        $("#deptoCat").html("<option style='color:#aaa' value=''>Departamento</option>");
                        $("#deptoCat, #categoria").val("").attr("disabled", true);

                        $("#avisoCat").css("color", "lightgreen");
                        $("#avisoCat").text(data);

                        $("#crearCat input").css("color", "#333");

                        setTimeout(function() {
                            $("#avisoCat").text("");
                        }, 3000)

                        localStorage.clear();
                    }
                })
            } else {
                $("#avisoCat").css("color", "crimson");
                $("#avisoCat").text("Error al crear la categoria");

                setTimeout(function() {
                    $("#avisoCat").text("");
                }, 3000)
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

                    $("#avisoBD").css("color", "lightgreen");
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

        // ======================================================================== LOGS
        // VER LOGS DEL SISTEMA
        $("li a[data-name=logs]").click(function() {
            $("#logs").load("views/log.php");
        })
    })
</script>