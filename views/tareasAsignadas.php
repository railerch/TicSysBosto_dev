<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// CONSULTAR TAREAS ASIGNADAS
$_SESSION['ultimaTarea'] = NULL;
$tecnico  = $_SESSION['nombre'];
$stmt_tsk = $conn->prepare("SELECT * FROM tareas WHERE tecnico IN ('$tecnico', 'Sin asignar')");
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
    <h1 class="d-inline-block">Tareas asignadas</h1>
    <hr style="background: #969696;">

    <div class="text-light table-striped"
        style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em">
        <table id="tasksList" class="table">
            <thead>
                <tr style="text-align: center;background: #353535;color: rgb(255,255,255);">
                    <th style="width: 5%;">ID</th>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 25%;">Tarea</th>
                    <th style="width: 25%;">Adjunto</th>
                    <th style="width: 5%;">Pts</th>
                    <th style="width: 10%;">Estatus</th>
                    <th style="width: 10%;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($tarea = $stmt_tsk->fetch()){
                    if($tarea['tecnico'] == 'Sin asignar') {
                        $tareaLibre = "background-color: lightgreen;";
                        $pointer = 'cursor:pointer;';
                        $title   = "Doble clic para tomar la tarea";
                        $eventDisable = 'style="pointer-events: none;"';
                    }else{
                        $tareaLibre = $pointer = $title = $eventDisable = NULL;
                    }
                    ?>
                    
                    <tr id="tsk<?php echo $tarea['id_tarea']?>" class="taskRow" style="text-align: center; <?php echo $tareaLibre, $pointer ?>" title="<?php echo $title ?>">
                        <td data-cell="id_tarea"><?php echo $tarea['id_tarea']?></td>
                        <td data-cell="fecha"><?php echo $tarea['fecha']?></td>
                        <td data-cell="descripcion">
                            <div style="max-height: 50px !important; overflow-y: hidden;text-align:left">
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
                        <td data-cell="valoracion"><?php echo $tarea['valoracion']?></td>
                        <td data-cell="estatus"><?php echo $tarea['estatus']?></td>
                        <td>
                            <div class="btn-toolbar d-flex flex-row justify-content-center">
                                <div class="btn-group" role="group">
                                    <a class="btn btn-outline-primary btn-sm verTarea"
                                        data-task-id="tsk<?php echo $tarea['id_tarea']?>" role="button" data-toggle="modal" title="Ver tarea" href="#/" data-target="#verTarea">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if($tarea['estatus'] != 'Finalizada' && $tarea['estatus'] != 'Verificada') { ?>
                                        <a class="btn btn-outline-warning btn-sm estatusBtn"
                                            data-task-id="tsk<?php echo $tarea['id_tarea']?>" data-task-status="En espera" role="button" title="Poner en espera" href="#/" <?php echo $eventDisable ?>>
                                            <i class="fa fa-hand-paper-o"></i>
                                        </a>
                                        <a class="btn btn-outline-info btn-sm estatusBtn"
                                            data-task-id="tsk<?php echo $tarea['id_tarea']?>" data-task-status="Procesando" role="button" title="Procesando" href="#/" <?php echo $eventDisable ?>>
                                            <i class="fa fa-gears"></i>
                                        </a>
                                        <a class="btn btn-outline-success btn-sm estatusBtn"
                                            data-task-id="tsk<?php echo $tarea['id_tarea']?>" data-task-status="Finalizada" role="button" title="Finalizar tarea" href="#/" <?php echo $eventDisable ?>>
                                            <i class="fa fa-check"></i>
                                        </a>
                                    <?php }?>
                                </div>
                            </div>
                        </td>
                    </tr>

                <?php } ?>
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
    sessionStorage.setItem("pagina_actual", "views/tareasAsignadas.php");

    // LIMPIAR LOCALSTORAGE PARA EVITAR ERRORES AL VALIDAR SELECCIONES
    localStorage.clear();

    // ALERTA NUEVAS TAREAS
    setInterval(function(){
        if(sessionStorage.getItem("recargarPagina")){
            $("#contenido").load("views/tareasAsignadas.php");
            sessionStorage.removeItem("recargarPagina");
        }

        if(sessionStorage.getItem("tareasSinRevisar")){
            var temp = sessionStorage.getItem("tareasSinRevisar");
            var tareasSinRevisar = temp.split("/");
            var tareas = tareasSinRevisar.length;

            for(var i = 0;i< tareas; i++){
                $(`tr[id=tsk${tareasSinRevisar[i]}]`).css("background-color", "#d478ff");
            }
        }
        
    }, 300)
    
    
    // AUTOASIGNACION DE TAREA MEDIANTE DOBLE CLIC
    $(".taskRow").dblclick(function() {
        var id = $(this).attr("id").substr(3);
        $.ajax({
            type: "GET",
            url: `main_controller.php?asignarTarea=true&id_tarea=${id}`,
            success: function() {
                $("#contenido").load("views/tareasAsignadas.php");
            }
        })
    });

    // VER TAREA
    $(".verTarea").click(function() {
        var id_tarea = $(this).attr("data-task-id");
        $("#verTarea span[data-tag=id_tarea]").text($(`#${id_tarea} td[data-cell=id_tarea]`).text());
        $("#verTarea span[data-tag=valoracion]").text($(`#${id_tarea} td[data-cell=valoracion]`).text());
        $("#verTarea span[data-tag=tecnico]").text($(`#${id_tarea} td[data-cell=tecnico]`).text());
        $("#verTarea span[data-tag=descripcion]").text($(`#${id_tarea} td[data-cell=descripcion]`).text());
        $("#verTarea span[data-tag=archivoAdjunto]").html("<a href=" + $(`#${id_tarea} td[data-cell=adjunto]`).attr("data-url") + " target='_blank'>" + $(`#${id_tarea} td[data-cell=adjunto]`).text() + "</a>");

        // ELIMINAR ALERTAS DE NUEVAS TAREAS
        if(sessionStorage.getItem("tareasSinRevisar")){
            var temp = sessionStorage.getItem("tareasSinRevisar");
            if(temp.includes("/")){
                var tarea = id_tarea.substring(3);
                $(`#${id_tarea}`).css("background-color", "unset");
                $("a[data-btn=tareasAsignadas] i").removeClass("pulso");
                var mod = temp.replace(tarea+"/", "");
                sessionStorage.setItem("tareasSinRevisar", mod);
            }else{
                $(`#${id_tarea}`).css("background-color", "unset");
                $("a[data-btn=tareasAsignadas] i").removeClass("pulso");
                sessionStorage.removeItem("tareasSinRevisar");
            }
            
        }
        
    })

    // ACTUALIZAR ESTATUS DE TAREA
    $(".estatusBtn").click(function(){
        var id_tarea = $(this).attr("data-task-id");
        var estatus  = $(this).attr("data-task-status");

        $.ajax({
            type: "GET",
            url: `main_controller.php?estatusTarea=true&id_tarea=${id_tarea}&estatus=${estatus}`,
            success: function(data){
                console.log(data);
                $("#contenido").load("views/tareasAsignadas.php");
            }
        })
    })

})
</script>