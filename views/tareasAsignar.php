<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// CONSULTAR TÉCNICOS REGISTRADOS
$stmt_tec = $conn->prepare("SELECT * FROM usuarios WHERE nivel = 'tecnico' ORDER BY nombre ASC");
$stmt_tec->setFetchMode(PDO::FETCH_ASSOC);
$stmt_tec->execute();

// CONSULTAR TAREAS REGISTRADAS
$_SESSION['ultimaTarea'] = NULL;
$stmt_tsk = $conn->prepare("SELECT * FROM tareas ORDER BY fecha ASC");
$stmt_tsk->setFetchMode(PDO::FETCH_ASSOC);
$stmt_tsk->execute();

?>
<style>
#datosUsuario * {
    margin-bottom: 0.5em;
}

#tasksList tbody tr td {
    height: 50px !important;
    vertical-align: middle;
}
</style>
<div
    style="background: #5b5b5b;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-list-ol" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Tareas</h1>
    <hr style="background: #969696;">
    <div id="formulario">
        <form id="taskForm" style="background: #353535;border-radius: 10px;padding: 1em;">
            <h3>Datos de la tarea<br></h3>
            <fieldset>
                <div class="form-group form-inline">
                    <select class="form-control" name="tecnico" id="tecnico" style="min-width:30%">
                        <option style="color:#aaa" value="">Seleccione el técnico</option>
                        <option value="Sin asignar">Tarea libre</option>
                        <!-- SELECCION DE TÉCNICO -->
                        <?php while($tecnico = $stmt_tec->fetch()){
                                echo "<option value='{$tecnico["nombre"]}' style='color:#555'>{$tecnico['nombre']}</option>";
                                }?>
                    </select>
                    <select class="form-control" name="valoracion" id="valoracion">
                        <option style="color:#aaa" value="">Valor de la tarea</option>
                        <?php for($i = 0; $i <= 100; $i+=1) {?>
                        <option value="<?php echo $i?>"><?php echo $i?>Pts</option>
                        <?php }?>
                    </select>
                </div>

                <div class="form-group">
                    <textarea id="descripcion" class="form-control" name="descripcion" cols="30" rows="10" placeholder="Descripción de la tarea" style="min-width:80%; max-width:80%; min-height:10em; max-height:10em;"></textarea>
                    <input type="hidden" name="MAX_FILE_SIZE" value="3000000">
                    <input id="archivoAdjunto" class="btn btn-light" type="file" name="archivoAdjunto" accept=".jpg, .jpeg, .png, .gif, .bmp, .doc, .docx, .pdf, .xlsx, .xls, .txt, .sql, .js, .py, .php" style="margin-top: 10px;">
                </div>

                <button id="registrarBtn" class="btn btn-primary btn-block" type="submit">&nbsp;Crear tarea</button>
            </fieldset>
        </form>
    </div>
    <h3 style="margin: 1em 0;">Tareas asignadas<br></h3>
    <div class="text-light table-striped"
        style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em">
        <table id="tasksList" class="table">
            <thead>
                <tr style="text-align: center;background: #353535;color: rgb(255,255,255);">
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 25%;">Tarea</th>
                    <th style="width: 25%;">Adjunto</th>
                    <th style="width: 15%;">Técnico</th>
                    <th style="width: 5%;">Pts</th>
                    <th style="width: 10%;">Estatus</th>
                    <th style="width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($tarea = $stmt_tsk->fetch()){?>
                    <tr id="tsk<?php echo $tarea['id_tarea']?>" style="text-align: center;">
                        <td data-cell="id_tarea"><?php echo $tarea['id_tarea']?></td>
                        <td data-cell="fecha"><?php echo $tarea['fecha']?></td>
                        <td data-cell="descripcion">
                            <div style="max-height: 50px !important; overflow-y: hidden;text-align:left;">
                                <?php echo $tarea['descripcion']?>
                            </div>
                        </td>
                        <?php if($tarea['adjunto']){
                            $archivo = explode('/', $tarea['adjunto']);
                        ?>
                            <td data-cell="adjunto" data-url="<?php echo $tarea['adjunto']?>"><i class="fa fa-paperclip"></i> <?php echo $archivo[1]?></td>
                        <?php }else{ ?>
                            <td data-cell="adjunto" data-url="">---</td>
                        <?php }?>
                        <td data-cell="tecnico"><?php echo $tarea['tecnico']?></td>
                        <td data-cell="valoracion"><?php echo $tarea['valoracion']?></td>
                        <td data-cell="estatus"><?php echo $tarea['estatus']?></td>
                        <td>
                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                <div class="btn-group" role="group">
                                    <a class="btn btn-outline-primary btn-sm verTarea"
                                        data-task-id="tsk<?php echo $tarea['id_tarea']?>" role="button" data-toggle="modal"
                                        data-bs-tooltip="" title="Ver tarea" href="#/" data-target="#verTarea">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if($tarea['estatus'] != 'Verificada') {?>

                                        <?php if($tarea['estatus'] == 'Finalizada') {?>
                                            <a class="btn btn-outline-success btn-sm estatusBtn"
                                                data-task-id="tsk<?php echo $tarea['id_tarea']?>" data-task-status="Verificada"
                                                role="button" title="Verificar tarea" href="#/">
                                                <i class="fa fa-check"></i>
                                            </a>
                                        <?php }else{?>
                                            <?php if ($tarea['estatus'] == 'Pendiente' || $tarea['estatus'] == 'En espera') {?>
                                                <a class="btn btn-outline-warning btn-sm editarTarea"
                                                    data-task-id="tsk<?php echo $tarea['id_tarea']?>" role="button" data-toggle="modal"
                                                    data-bs-tooltip="" title="Editar tarea" href="#/" data-target="#editarTarea">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            
                                                <a class="btn btn-outline-danger btn-sm eliminarTarea"
                                                    data-task-id="<?php echo $tarea['id_tarea']?>" role="button" data-toggle="modal"
                                                    data-bs-tooltip="" title="Eliminar tarea" href="#/" data-target="#eliminarTarea">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                            <?php }?>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
</div>

<!-- VENTANA MODAL VER TAREA -->
<div class="modal fade" role="dialog" tabindex="-1" id="verTarea">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #333;"><strong>TAREA:
                        #<span data-tag="id_tarea"></span></strong>
                    <br>
                    <strong>Valoración: </strong><span data-tag="valoracion"></span>%
                    <br>

                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body" style="color: #333;">

                <ul class="list-unstyled">
                    <li><strong>Técnico: </strong><span data-tag="tecnico"></span></li>
                    <li>
                        <strong>Tarea asignada: </strong>
                        <span data-tag="descripcion"></span>
                    </li>
                    <li>
                        <strong>Archivo adjunto:</strong>
                        <span data-tag="archivoAdjunto"></span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer"><button class="btn btn-primary" type="button" data-dismiss="modal"
                    data-cancel="ok">CERRAR</button>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA MODAL EDITAR TAREA -->
<div class="modal fade" role="dialog" tabindex="-1" id="editarTarea">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="upTaskForm">

                <div class="modal-header">
                    <h5 class="modal-title" style="color: #333;"><strong>TAREA:
                            #<span data-tag="id_tarea"></span></strong>
                        <br>
                        <select class="form-control" name="valoracion" data-tag="valoracion">
                            <option style="color:#aaa" value="">Valor de la tarea</option>
                            <?php for($i = 0; $i <= 100; $i+=1) {?>
                            <option value="<?php echo $i?>"><?php echo $i?>Pts</option>
                            <?php }?>
                        </select>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>

                <div class="modal-body" style="color: #333;">
                    <strong>Técnico: </strong><span data-tag="tecnico"></span>
                    <textarea class="form-control" name="descripcion" data-tag="descripcion" cols="30" rows="10"
                        placeholder="Descripción de la tarea"
                        style="min-width:100%; max-width:100%; min-height:10em; max-height:10em;">
                        </textarea>
                </div>

                <div class="modal-footer"><button id="actualizarBtn" class="btn btn-primary" type="button"
                        data-dismiss="modal" data-cancel="ok">ACTUALIZAR</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- VENTANA MODAL ELIMINAR TAREA -->
<div class="modal fade" role="dialog" tabindex="-1" id="eliminarTarea" style="color: #212529;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="fa fa-trash-o"></i>&nbsp;Eliminar tarea<br></h4><button type="button"
                    class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <p>Esta a punto de eliminar la tarea seleccionada, desea continuar?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" type="button" data-dismiss="modal">NO</button>
                <button class="btn btn-danger eliminarConfirmacion" data-task-id="" type="button"
                    data-dismiss="modal">SI</button></a>
            </div>
        </div>
    </div>
</div>

<?php
    avisos(@$_SESSION['avisos']);
    ocultar_aviso();
?>

<script type="text/javascript">
// IDIOMA ESPAÑOL PARA DATATABLES
$(document).ready(function() {
    $("#tasksList").DataTable({
        "language": {
            "url": "config/dataTableSpanish.json"
        },
        "order": [[1, "desc"]]
    });
})
</script>

<script type="text/javascript">
$(document).ready(function() {
    // ESTABLECER LA PAGINA ACTUAL
    sessionStorage.setItem("pagina_actual", "views/tareasAsignar.php");

    // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
    localStorage.clear();

    // REGISTRAR TAREA
    $("#registrarBtn").click(function(event) {

        event.preventDefault();
        
        // VALIDAR SELECCIONES
        <?php echo validar_selecciones("tecnico", "") ?>
        <?php echo validar_selecciones("valoracion", "") ?>
        <?php echo validar_selecciones("descripcion", "") ?>

        // DATOS PARA ENVIAR
        var tecnico         = $("#tecnico").val();
        var valoracion      = $("#valoracion").val();
        var descripcion     = $("#descripcion").val();
        var archivoAdjunto  = $("#archivoAdjunto")[0].files[0];

        var datos = new FormData();
        datos.append("tecnico", tecnico);
        datos.append("valoracion", valoracion);
        datos.append("descripcion", descripcion);
        datos.append("archivo", archivoAdjunto);

        if (localStorage.getItem("inputOK") == 3) {
            $.ajax({
                type: "POST",
                url: "main_controller.php?registrarTarea=true",
                processData: false,
                contentType: false,
                data: datos,
                // data: $("#taskForm").serialize(),
                success: function(data) {
                    console.log(data);
                    $("#contenido").load("views/tareasAsignar.php");
                    localStorage.clear();
                }
            });
        }

    })

    // VER TAREA
    $(".verTarea").click(function() {
        var id_tarea = $(this).attr("data-task-id");
        $("#verTarea span[data-tag=id_tarea]").text($(`#${id_tarea} td[data-cell=id_tarea]`).text());
        $("#verTarea span[data-tag=valoracion]").text($(`#${id_tarea} td[data-cell=valoracion]`).text());
        $("#verTarea span[data-tag=tecnico]").text($(`#${id_tarea} td[data-cell=tecnico]`).text());
        $("#verTarea span[data-tag=descripcion]").text($(`#${id_tarea} td[data-cell=descripcion]`).text());
        $("#verTarea span[data-tag=archivoAdjunto]").html("<a href=" + $(`#${id_tarea} td[data-cell=adjunto]`).attr("data-url") + " target='_blank'>" + $(`#${id_tarea} td[data-cell=adjunto]`).text() + "</a>");
        
    })

    // VERIFICAR TAREA
    $(".estatusBtn").click(function() {
        var id_tarea = $(this).attr("data-task-id");
        var estatus = $(this).attr("data-task-status");

        $.ajax({
            type: "GET",
            url: `main_controller.php?estatusTarea=true&id_tarea=${id_tarea}&estatus=${estatus}`,
            success: function(data) {
                console.log(data);
                $("#contenido").load("views/tareasAsignar.php");
            }
        })
    })

    // EDITAR TAREA
    $(".editarTarea").click(function() {
        var id_tarea = $(this).attr("data-task-id");
        $("#editarTarea span[data-tag=id_tarea]").text($(`#${id_tarea} td[data-cell=id_tarea]`).text());
        $("#editarTarea select[data-tag=valoracion]").val($(`#${id_tarea} td[data-cell=valoracion]`)
            .text());
        $("#editarTarea span[data-tag=tecnico]").text($(`#${id_tarea} td[data-cell=tecnico]`).text());
        $("#editarTarea textarea[data-tag=descripcion]").val($(`#${id_tarea} td[data-cell=descripcion]`)
            .text().trim());

        // ACTUALIZAR TAREA
        $("#actualizarBtn").click(function() {
            setTimeout(function() {
                $.ajax({
                    type: "POST",
                    url: `main_controller.php?actualizarTarea=true&id_tarea=${id_tarea}`,
                    data: $("#upTaskForm").serialize(),
                    success: function(data) {
                        console.log(data);
                        $("#contenido").load("views/tareasAsignar.php");
                        localStorage.clear();
                    }
                });
            }, 200)

        })
    })

    // ELIMINAR TAREA
    $(".eliminarTarea").click(function() {

        var id_tarea = $(this).attr("data-task-id");

        $(".eliminarConfirmacion").click(function() {
            $.ajax({
                type: "GET",
                url: `main_controller.php?eliminarTarea=true&id_tarea=${id_tarea}`,
                success: function(data) {
                    console.log(data);
                    setTimeout(function() {
                        $("#contenido").load("views/tareasAsignar.php");
                    }, 500);
                }
            })
        })

    })

})
</script>