<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if (!$conn) {
    Log::registrar_log($conexion->error);
}

// MOSTRAR DEPARTAMENTOS, EMPRESAS E INCIDENCIAS REGULARES
$stmt_1 = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'empresa' ORDER BY descripcion ASC");
$stmt_1->setFetchMode(PDO::FETCH_ASSOC);
$stmt_1->execute();
?>
<style>
    .ocultar {
        display: none;
    }
</style>

<div style="background: #505050;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;padding: 0.5em;">
    <i class="fa fa-ticket" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Crear Ticket</h1>
    <hr>
    <form id="ticketForm">
        <div class="form-group">
            <h4>Datos del ticket</h4>
            <div class="d-inline-flex flex-wrap" style="width:100%">
                <select id="empresa" class="form-control" name="empresa" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Empresa que emite</option>
                    <?php while ($loc = $stmt_1->fetch()) { ?>
                        <option style='color:#555' value="<?php echo $loc['descripcion'] ?>"><?php echo $loc['descripcion'] ?>
                        </option>
                    <?php } ?>
                </select>
                <select id="deptoEmisor" class="form-control" name="deptoEmisor" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Depto emisor</option>
                </select>
                <select id="nombre" class="form-control" name="nombre" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                    <option style="color:#aaa" selected>Usuario emisor</option>
                </select>
                <?php if ($_SESSION['nivel'] == 'admin') { ?>
                    <select id="area" class="form-control" name="area" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;">
                        <option style="color:#aaa" selected>Depto receptor</option>
                    </select>
                <?php } ?>
                <select id="categoria" class="form-control" name="categoria" style="min-width:200px;max-width:20%;margin: 0 0.5em 0.5em 0;" <?php echo @$deshabilitar ?>>
                    <option style="color:#aaa" selected>Categoría</option>
                </select>
                <select id="prioridad" class="form-control" name="prioridad" style="min-width:200px;max-width:15%;margin: 0 0.5em 0.5em 0;">
                    <option selected>Prioridad</option>
                    <option value="baja">Baja</option>
                    <option value="media">Media</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <h4>Asunto</h4>
            <input id="asunto" class="form-control mb-2" type="text" name="asunto" placeholder="Breve encabezado de la solicitud" maxlength="50">
            <h4>Descripción</h4>
            <textarea id="descripcion" class="form-control" name="descripcion" style="min-height: 5em;max-height: 5em;" placeholder="Describa su solicitud, de ser necesario habilite ANYDESK y envie los datos de conexión" maxlength="250" required></textarea>
        </div>
        <div class="form-group d-md-flex justify-content-md-end">
            <button class="btn btn-primary" type="submit">Crear ticket</button>
        </div>
    </form>
</div>

<?php
avisos(@$_SESSION['avisos']);
ocultar_aviso();
?>

<!-- FUNCIONES JS -->
<script src="assets/js/main_fn.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
        localStorage.clear();

        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/ticketTechCrear.php");

        // FILTRAR DEPTOS SEGUN LA EMPRESA SELECCIONADA
        $("#empresa").change(function() {
            let tipoSesion = sessionStorage.getItem("tipoSesion");
            let empresa = $(this).val();
            let deptoRt = document.getElementById("deptoEmisor");
            let deptoRx = null;

            // Permitir cambiar depto receptor si el nivel es admin
            if (tipoSesion == "admin") {
                deptoRx = document.getElementById("area");
            }

            let usuarios = document.getElementById("nombre");
            let categoria = document.getElementById("categoria");
            fetch(`main_controller.php?empresaDeptos=true&empresa=${empresa}`)
                .then(res => res.json())
                .then(data => {
                    if (data != "") {
                        deptoRt.innerHTML = "<option style='color:#aaa' selected>Depto emisor</option>";
                        usuarios.innerHTML = "<option style='color:#aaa' selected>Usuario emisor</option>";
                        if (tipoSesion == "admin") {
                            deptoRx.innerHTML = "<option style='color:#aaa' selected>Depto receptor</option>";
                            categoria.innerHTML = "<option style='color:#aaa' selected>Categoria</option>";
                        }
                        data.forEach(dep => {
                            let optA = document.createElement("option");
                            optA.setAttribute("value", dep);
                            optA.innerText = dep;
                            deptoRt.append(optA);
                            // Permitir cambiar depto receptor si el nivel es admin
                            if (tipoSesion == "admin") {
                                let optB = document.createElement("option");
                                optB.setAttribute("value", dep);
                                optB.innerText = dep;
                                deptoRx.append(optB);
                            }
                        })

                    } else {
                        deptoRt.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                        // Permitir cambiar depto receptor si el nivel es admin
                        if (tipoSesion == "admin") {
                            deptoRx.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                            categoria.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                        }
                        usuarios.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                        console.warn("AVISO: sin registros activos.")
                    }

                })
        })

        // FILTRAR USUARIOS EMISORES SEGUN EL DEPTO
        $("#deptoEmisor").change(function() {
            let depto = $(this).val();
            let empresa = document.getElementById("empresa").value;
            let usuarios = document.getElementById("nombre");
            fetch(`main_controller.php?deptoUsuarios=true&empresa=${empresa}&depto=${depto}`)
                .then(res => res.json())
                .then(data => {
                    if (data != "") {
                        usuarios.innerHTML = "<option style='color:#aaa' selected>Usuario emisor</option>";
                        data.forEach(dep => {
                            let opt = document.createElement("option");
                            opt.setAttribute("value", dep);
                            opt.innerText = dep;
                            usuarios.append(opt);
                        })

                    } else {
                        usuarios.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                        console.warn("AVISO: sin registros activos.")
                    }

                })
        })

        // FILTRAR CATEGORIAS SEGUN EL DEPTO SELECCIONADO
        let tipoSesion = sessionStorage.getItem("tipoSesion");

        if (tipoSesion == "admin") {
            $("#area").change(function() {
                let empresa = $("#empresa").val();
                let depto = $(this).val();
                let categoria = document.getElementById("categoria");
                fetch(`main_controller.php?deptoCats=true&depto=${depto}&empresa=${empresa}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data != "") {
                            categoria.innerHTML = "<option style='color:#aaa' selected>Categoría</option>";
                            data.forEach(cat => {
                                let opt = document.createElement("option");
                                opt.setAttribute("value", cat);
                                opt.innerText = cat;
                                categoria.append(opt);
                            })

                        } else {
                            categoria.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                            console.warn("AVISO: sin registros activos.")
                        }
                    })
            })
        } else {
            let depto = sessionStorage.getItem("depto");

            fetch(`main_controller.php?deptoCats=true&depto=${depto}`)
                .then(res => res.json())
                .then(data => {
                    if (data != "") {
                        categoria.innerHTML = "<option style='color:#aaa' selected>Categoría</option>";
                        data.forEach(cat => {
                            let opt = document.createElement("option");
                            opt.setAttribute("value", cat);
                            opt.innerText = cat;
                            categoria.append(opt);
                        })

                    } else {
                        categoria.innerHTML = "<option style='color:#aaa' selected>Sin registros</option>";
                        console.warn("AVISO: sin registros activos.")
                    }

                })
        }

        // CREAR TICKET
        $("button[type=submit]").click(function() {
            event.preventDefault();

            // VALIDAR CAMPOS Y SELECCIONES
            <?php
            echo validar_selecciones("empresa", "Empresa");
            echo validar_selecciones("nombre", "Usuario");
            echo validar_selecciones("area", "Departamento al que va dirigida la solicitud");
            echo validar_selecciones("asunto", "");
            echo validar_selecciones("prioridad", "Prioridad");
            echo validar_selecciones("descripcion", "");

            ?>

            // ENVIAR DATOS
            if (localStorage.getItem("inputOK") == 6) {
                $.ajax({
                    type: "POST",
                    url: "main_controller.php?crearTicket=true",
                    data: $("#ticketForm").serialize(),
                    success: function(data) {
                        $("#contenido").load("views/ticketTechCrear.php");
                        console.log(data);
                    }
                })
            }
        })

    })
</script>