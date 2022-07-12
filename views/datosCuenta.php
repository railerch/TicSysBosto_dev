<?php
include('../main_functions.php');

// CONEXION DB
$conexion = new Connection('../config/config.json');
$conn = $conexion->db_conn();

if(!$conn){
    Log::registrar_log($conexion->error);
}

// DATOS DE CUENTA DEL USUARIO
$usuario = $_SESSION['usuario'];
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = '$usuario'");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
$datos = $stmt->fetch();

// CONSULTAR DEPARTAMENTOS Y LOCACIONES PARA LA VENTANA MODAL
$stmt_loc = $conn->prepare("SELECT * FROM miscelaneos WHERE tipo = 'locacion'");
$stmt_loc->setFetchMode(PDO::FETCH_ASSOC);
$stmt_loc->execute();
?>

<div
    style="background: #5b5b5b;padding: 0.5em;border-radius: 1em;box-shadow: 0px 0px 10px rgb(0,0,0);border-width: 1px;border-style: none;border-top-style: none;border-right-style: none;border-bottom-style: none;color: #d7d7d7;">
    <i class="fa fa-user-o" style="font-size: 5vw;margin-right: 0.3em;"></i>
    <h1 class="d-inline-block">Datos de cuenta</h1>
    <hr style="background: #969696;">
    <div class="table-striped"
        style="background: #ffffff;margin-bottom: 1em;width: 100%;margin-top: 1em;padding:0.5em; overflow:scroll">
        <table class="table">
            <thead>
                <tr style="text-align: center;background: #353535;color: rgb(255,255,255);">
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Ubicación</th>
                    <th>Departamento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr style="text-align: center;">
                    <td><?php echo $datos['id_usuario']?></td>
                    <td><?php echo $datos['nombre']?></td>
                    <td><?php echo $datos['usuario']?></td>
                    <td><?php echo $datos['locacion']?></td>
                    <td><?php echo $datos['depto']?></td>
                    <td>
                        <div class="btn-toolbar d-flex flex-row justify-content-center">
                            <div class="btn-group" role="group"><a class="btn btn-outline-primary btn-sm" role="button"
                                    data-toggle="modal" data-bs-tooltip="" title="Editar usuario" href=""
                                    data-target="#edicionUsuario"><i class="fa fa-edit"></i></a><button
                                    class="btn btn-outline-danger btn-sm" data-toggle="modal" data-bs-tooltip=""
                                    type="button" title="Eliminar usuario" data-target="#eliminar"><i
                                        class="fa fa-trash-o"></i></button></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- VENTANA MODAL CAMBIAR CLAVE -->
    <div class="modal fade" role="dialog" tabindex="-1" id="edicionUsuario">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" style="color: #353535;"><i class="fa fa-user-o"></i>&nbsp;Cambiar datos de cuenta</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <form id="datosUsuario" class="justify-content-around" id="edicionUsuario" style="margin: 1em;">
                        <fieldset>
                            <input type="hidden" name="id" value="<?php echo $datos['id_usuario'] ?>">
                            <label for="clave" style="color: #333;">Definir nueva clave</label>
                            <input class="form-control" type="password" name="clave" placeholder="Definir una nueva clave" value="<?php echo $datos['clave'] ?>">
                            <br>
                            <label for="locacion" style="color: #333;">Definir nueva ubicación</label>
                            <select class="form-control" name="locacion">
                                <option style="color:#555" value="NULL">Seleccione nueva ubicación</option>
                                <!-- UBICACIONES -->
                                <?php while($locacion = $stmt_loc->fetch()){
                                    echo "<option value='{$locacion["descripcion"]}' style='color:#555'>{$locacion['descripcion']}</option>";
                                }?>
                            </select>
                            <button id="actualizar" class="btn btn-primary float-right" type="submit" data-dismiss="modal">ACTUALIZAR</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" role="dialog" tabindex="-1" id="eliminar" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-trash-o"></i>&nbsp;Eliminar cuenta<br></h4><button
                        type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <p>Esta a punto de eliminar su cuenta de usuario, desea continuar?</p>
                </div>
                <div class="modal-footer">
                <button class="btn btn-light" type="button" data-dismiss="modal">NO</button>
                <button class="btn btn-danger eliminarConfirmacion" type="button" data-usuario-id="<?php echo $datos['id_usuario']?>" data-dismiss="modal">SI</button></a>
            </div>
        </div>
    </div>
</div>

<?php 
    avisos(@$_SESSION['avisos']);
    ocultar_aviso();
?>

<script type="text/javascript">
    
    $(document).ready(function() {
        // ESTABLECER LA PAGINA ACTUAL
        sessionStorage.setItem("pagina_actual", "views/datosCuenta.php");

        // ACTUALIZAR CLAVE DE USUARIO
        $("#actualizar").click(function() {
            $.ajax({
                type:"POST",
                url: "main_controller.php?actUsuario=true",
                data: $("#datosUsuario").serialize(),
                success: function(data){
                    console.log(data);
                    setTimeout(function(){
                        $("#contenido").load("views/datosCuenta.php");
                    },500)
                }
            })
        })
    })

    // ELIMINAR CUENTA
    $(".eliminarConfirmacion").click(function() {
        var id = $(this).attr("data-usuario-id");
        $.ajax({
            type:"GET",
            url: `main_controller.php?id=${id}&eliminarCuenta=true`,
            success: function(data){
                setTimeout(function(){
                    location.reload();
                },500);
            }
        })
    })
</script>