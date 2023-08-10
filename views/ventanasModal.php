<?php if ($pagina == 'ticketsAbiertos.php' || $pagina == 'ticketsEspera.php') { ?>
    <!-- VER TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="ver<?php echo $ticket['id_ticket'] ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #333;"><strong>TICKET:
                            #<?php echo $ticket['id_ticket'] ?></strong>
                        <br>
                        <?php echo $ticket['nombre'] ?>
                        <br>
                        <?php echo $ticket['empresa'] . ' - ' . $ticket['depto'] ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="color: #333;">
                    <ul class="list-unstyled">
                        <li><strong>Categoria: </strong><?php echo $ticket['categoria'] ?></li>
                        <li><strong>Asunto: </strong><?php echo $ticket['asunto'] ?></li>

                        <!-- Mostrar el estatus y el monto sugerido en caso de estar presente (cuando el ticket es para el depto finanzas) -->
                        <?php if ($ticket['depto_receptor'] == 'Finanzas' && floatval($ticket['monto']) > 0) {
                            // Indicar estatus de la solicitud
                            if ($ticket['autorizado'] == 'no') {
                                $autorizadoTxt = '<span style="color: red">No autorizado</span>';
                            } else if ($ticket['autorizado'] == 'si') {
                                $autorizadoTxt = '<span style="color: green">Autorizado</span>';
                            }
                        ?>
                            <li><strong>Monto: </strong>$<?php echo $ticket['monto'] ?></li>
                            <li><strong>Estatus: </strong><?php echo $autorizadoTxt . ' ' . @$botones ?></li>
                        <?php } ?>
                        <!-- ========================================================= -->

                        <li><strong>Descripción: </strong><?php echo $ticket['descripcion'] ?></li>
                    </ul>

                    <!-- Aviso de estatus de solicitud -->
                    <?php if ($ticket['depto_receptor'] == 'Finanzas' && ($ticket['autorizado'] == 0 || $ticket['autorizado'] > 1)) { ?>
                        <hr class="mb-0">
                        <small class="text-danger">
                            <b>IMPORTANTE:</b> Evite procesar esta solicitud si el estatus de la misma es 'No autorizado'.
                        </small>
                        <hr>
                    <?php } else { ?>
                        <hr>
                    <?php } ?>

                    <p style="margin: 0;color: #fff;background: #505050;padding: 2px;padding-left: 0.5em;padding-right: 0.5em;padding-top: 0.2em;padding-bottom: 0.2em;">
                        <i class="fa fa-wechat"></i>&nbsp;
                        <strong>Chat directo</strong> [ Analista: <span id="analista<?php echo $ticket['id_ticket'] ?>"></span> ]
                        <br>
                        <!-- ALERTA DE USUARIO PARA QUE CIERRE EL TICKET -->
                        <?php
                        if ($ticket['estatus'] != 'precierre') {
                            if (isset($ticket['comentarios']) && $ticket['comentarios'] == 'Alerta de cierre activada') {
                                # MOSTRAR / OCULTAR ALERTA DE CIERRE
                                $display_alertaInactiva = 'none';
                                $display_alertaActiva = 'unset';
                            } else {
                                $display_alertaInactiva = 'unset';
                                $display_alertaActiva = 'none';
                            }
                        }
                        ?>
                        <a class="activarAlerta" data-ticket="<?php echo $ticket['id_ticket'] ?>" href="#/" style="color:unset; display:<?php echo $display_alertaInactiva ?>">
                            <small style="color:orange"><i class="fa fa-send"></i> Enviar alerta de cierre</small>
                        </a>
                        <small class="alertaActiva" style="color:green; display:<?php echo $display_alertaActiva ?>"><i class="fa fa-check"></i> Alerta de cierre enviada!</span></small>
                    </p>

                    <div id="chat<?php echo $ticket['id_ticket'] ?>" class="chatWindow" style="padding: 0 0.5em; min-width: 100%;max-width: 100%;min-height: 200px;max-height: 300px;margin-bottom: 0.5em;border: 1px solid #ccc;overflow-y: scroll;background-color:gray; word-wrap:break-word">
                        <!-- CONVERSACION -->
                    </div>

                    <!-- NO SE PODRA ESCRIBIR EN EL CHAT SI EL TICKET NO TIENE TECNICO ASIGNADO O SI EL QUE LO VISUALICE NO ES EL ASIGNADO -->
                    <?php if ($ticket['estatus'] != "cerrado" && $_SESSION['nombre'] === $ticket['analista']) { ?>
                        <div id="mensaje" class="md-form text-nowrap">

                            <!-- MENSAJES -->
                            <div class="input-group">
                                <input id="inputMensaje" class="form-control" data-msj="<?php echo $ticket['id_ticket'] ?>" data-tic="<?php echo $ticket['id_ticket'] ?>" data-usr="<?php echo $_SESSION['usuario'] ?>" data-loc="<?php echo $ticket['empresa'] ?>" data-tec="<?php echo $_SESSION['usuario'] ?>" name="mensaje" type="text" placeholder="Escribir mensaje..." autocomplete="off">

                                <div class="input-group-append">
                                    <button class="adjuntarArchivo btn btn-dark" type="button" title="Adjuntar archivo" data-tic="<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-plus"></i>
                                    </button>

                                    <button class="enviarMensaje btn btn-primary" data-tic="<?php echo $ticket['id_ticket'] ?>" data-usr="<?php echo $ticket['usuario'] ?>" data-loc="<?php echo $ticket['empresa'] ?>" data-tec="<?php echo $_SESSION['usuario'] ?>" data-toggle="tooltip" data-bs-tooltip="" type="button" title="Enviar mensaje">
                                        ENVIAR
                                    </button>
                                </div>
                            </div>
                            <div class="input-group archivoAdjunto<?php echo $ticket['id_ticket'] ?>" style="margin-top: 10px">
                                <input type="hidden" name="MAX_FILE_SIZE" value="25000000">
                                <input id="archivo<?php echo $ticket['id_ticket'] ?>" class="btn btn-dark" type="file" name="archivo" accept=".jpg, .jpeg, .png, .gif, .bmp, .doc, .docx, .pdf, .xlsx, .xls, .txt">
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </div>
    </div>

    <!-- CERRAR TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="cerrar<?php echo $ticket['id_ticket'] ?>" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-check"></i>&nbsp;Cerrar ticket<br></h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <textarea id="bitacora<?php echo $ticket['id_ticket'] ?>" class="form-control" name="bitacora" style="min-width:100%; max-width:100%; min-height:150px; max-height:15px;" placeholder="Indique una breve descripción de la solucion..."></textarea>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button" data-dismiss="modal">CANCELAR</button>
                    <button class="btn btn-danger cerrarConfirmacion" type="button" data-cerrar-id="<?php echo $ticket['id_ticket'] ?>" data-analista="<?php echo $ticket['analista'] ?>" data-nombre-usuario="<?php echo $ticket['nombre'] ?>" data-dismiss="modal">CERRAR
                        TICKET</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ELIMINAR TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="eliminar<?php echo $ticket['id_ticket'] ?>" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-trash"></i>&nbsp;Eliminar ticket<br></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <p>Esta a punto de eliminar el ticket seleccionado, desea continuar?</p>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button" data-dismiss="modal" data-cancel="ok">NO</button>
                    <button class="btn btn-danger eliminarConfirmacion" type="button" data-eliminar-id="<?php echo $ticket['id_ticket'] ?>" data-dismiss="modal">SI</button>
                </div>
            </div>
        </div>
    </div>
<?php } else if ($pagina == 'ticketsCerrados.php') { ?>
    <!-- VER TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="ver<?php echo $ticket['id_ticket'] ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #333;"><strong>TICKET:
                            #<?php echo $ticket['id_ticket'] ?></strong>
                        <br>
                        <?php echo $ticket['nombre'] ?> (<?php echo $ticket['empresa'] ?>)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="color: #333;">
                    <ul class="list-unstyled">
                        <li><strong>Categoria: </strong><?php echo $ticket['categoria'] ?></li>
                        <li><strong>Asunto: </strong><?php echo $ticket['asunto'] ?></li>

                        <!-- Mostrar el estatus y el monto sugerido en caso de estar presente (cuando el ticket es para el depto finanzas) -->
                        <?php if ($ticket['depto_receptor'] == 'Finanzas' && floatval($ticket['monto']) > 0) {
                            // Indicar estatus de la solicitud
                            if ($ticket['autorizado'] == 'no') {
                                $autorizadoTxt = '<span style="color: red">No autorizado</span>';
                            } else if ($ticket['autorizado'] == 'si') {
                                $autorizadoTxt = '<span style="color: green">Autorizado</span>';
                            }
                        ?>
                            <li><strong>Monto: </strong>$<?php echo $ticket['monto'] ?></li>
                            <li><strong>Estatus: </strong><?php echo $autorizadoTxt . ' ' . @$botones ?></li>
                        <?php } ?>
                        <!-- ========================================================= -->

                        <li><strong>Descripción: </strong><?php echo $ticket['descripcion'] ?></li>
                    </ul>

                    <hr>

                    <p style="margin: 0;color: #fff;background: #505050;padding: 2px;padding-left: 0.5em;padding-right: 0.5em;padding-top: 0.2em;padding-bottom: 0.2em;">
                        <i class="fa fa-wechat"></i>&nbsp;
                        <strong>Chat directo</strong> [ Analista: <span id="analista<?php echo $ticket['id_ticket'] ?>"></span> ]
                    </p>

                    <div id="chat<?php echo $ticket['id_ticket'] ?>" class="chatWindow" style="padding: 0 0.5em; min-width: 100%;max-width: 100%;min-height: 200px;max-height: 300px;margin-bottom: 0.5em;border: 1px solid #ccc;overflow-y: scroll;background-color:gray; word-wrap:break-word">
                        <!-- CONVERSACION -->
                    </div>

                    <h4>Solución</h4>
                    <?php
                    $id_ticket = $ticket['id_ticket'];
                    $bitacora  = 'El usuario dio por finalizado el soporte';
                    $stmt_sol = $conn->prepare("SELECT solucion FROM bitacora WHERE id_ticket = '$id_ticket' AND solucion <> '$bitacora'");
                    $stmt_sol->execute();
                    $solucion = $stmt_sol->fetch(PDO::FETCH_ASSOC);
                    if ($solucion) {
                        echo "<p>{$solucion['solucion']}</p>";
                    } else {
                        echo "Solución no especificada por el analista";
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
<?php } else if ($pagina == 'ticketsEliminados.php') { ?>
    <!-- VER TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="ver<?php echo $ticket['id_ticket'] ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #333;"><strong>TICKET:
                            #<?php echo $ticket['id_ticket'] ?></strong>
                        <br>
                        <?php echo $ticket['nombre'] ?>
                        <br>
                        <?php echo $ticket['empresa'] . ' - ' . $ticket['depto'] ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="color: #333;">
                    <ul class="list-unstyled">
                        <li><strong>Categoria: </strong><?php echo $ticket['categoria'] ?></li>
                        <li><strong>Asunto: </strong><?php echo $ticket['asunto'] ?></li>

                        <!-- Mostrar el estatus y el monto sugerido en caso de estar presente (cuando el ticket es para el depto finanzas) -->
                        <?php if ($ticket['depto_receptor'] == 'Finanzas' && floatval($ticket['monto']) > 0) {
                            // Indicar estatus de la solicitud
                            if ($ticket['autorizado'] == 'no') {
                                $autorizadoTxt = '<span style="color: red">No autorizado</span>';
                            } else if ($ticket['autorizado'] == 'si') {
                                $autorizadoTxt = '<span style="color: green">Autorizado</span>';
                            }
                        ?>
                            <li><strong>Monto: </strong>$<?php echo $ticket['monto'] ?></li>
                            <li><strong>Estatus: </strong><?php echo $autorizadoTxt . ' ' . @$botones ?></li>
                        <?php } ?>
                        <!-- ========================================================= -->

                        <li><strong>Descripción: </strong><?php echo $ticket['descripcion'] ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- ELIMINAR TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="eliminar<?php echo $ticket['id_ticket'] ?>" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-trash"></i>&nbsp;Eliminar ticket<br></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div id="" class="modal-body">
                    <p>Esta a punto de eliminar el ticket seleccionado, desea continuar?</p>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button" data-dismiss="modal" data-cancel="ok">NO</button>
                    <button class="btn btn-danger eliminarConfirmacion" type="button" data-eliminar-id="<?php echo $ticket['id_ticket'] ?>" data-dismiss="modal">SI</button>
                </div>
            </div>
        </div>
    </div>
<?php } else if ($pagina == 'ticketsUsuario.php') { ?>
    <!-- VER TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="ver<?php echo $ticket['id_ticket'] ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: #333;"><strong>TICKET:
                            #<?php echo $ticket['id_ticket'] ?></strong>
                        <br>
                        <?php echo $ticket['nombre'] ?>
                        <br>
                        <?php echo $ticket['empresa'] . ' - ' . $ticket['depto'] ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="color: #333;">
                    <ul class="list-unstyled">
                        <li><strong>Categoria: </strong><?php echo $ticket['categoria'] ?></li>
                        <li><strong>Asunto: </strong><?php echo $ticket['asunto'] ?></li>

                        <!-- Mostrar el monto sugerido en caso de estar presente (cuando el ticket es para el depto finanzas) -->
                        <?php if ($ticket['depto_receptor'] == 'Finanzas' && floatval($ticket['monto']) > 0) {
                            // Mostrar botones de control en caso de ser el gerente/admin del departamento emisor del ticket
                            if (($_SESSION['nivel'] == 'admin' || $_SESSION['nivel'] == 'gerente') && $_SESSION['depto'] /* Depto del usuario emisor*/ == $ticket['depto'] /* Depto emisor*/ && $ticket['estatus'] != 'cerrado') {
                                $botones = "<span><i class='btn btn-outline-success btn-sm fa fa-check autorizacion-btn' data-autorizar='si' data-ticket={$ticket['id_ticket']} title='Autorizar'></i> <i class='btn btn-outline-danger btn-sm fa fa-close autorizacion-btn' data-autorizar='no' data-ticket={$ticket['id_ticket']} title='No autorizar'></i></span>";
                            } else {
                                $botones = NULL;
                            }

                            // Indicar estatus de la solicitud
                            if ($ticket['autorizado'] == 'no') {
                                $autorizadoTxt = '<span style="color: red">No autorizado</span>';
                            } else if ($ticket['autorizado'] == 'si') {
                                $autorizadoTxt = '<span style="color: green">Autorizado</span>';
                            }
                        ?>
                            <li><strong>Monto: </strong>$<?php echo $ticket['monto'] ?></li>
                            <li><strong>Estatus: </strong><?php echo $autorizadoTxt . ' ' . @$botones ?></li>
                        <?php } ?>
                        <!-- ========================================================= -->

                        <li><strong>Descripción: </strong><?php echo $ticket['descripcion'] ?></li>
                    </ul>

                    <hr>

                    <p style="margin: 0;color: #fff;background: #505050;padding: 2px;padding-left: 0.5em;padding-right: 0.5em;padding-top: 0.2em;padding-bottom: 0.2em;">
                        <i class="fa fa-wechat"></i>&nbsp;
                        <?php
                        if ($ticket['estatus'] == "cerrado") {
                            echo "<strong style='color:orange'>Historial de chat</strong> [ Analista: <span id='analista{$ticket['id_ticket']}'></span> ]";
                        } else {
                            echo "<strong>Chat directo</strong> [ Analista: <span id='analista{$ticket['id_ticket']}'></span> ]";
                        }
                        ?><br>
                    </p>

                    <div id="chat<?php echo $ticket['id_ticket'] ?>" class="chatWindow" style="padding: 0 0.5em; min-width: 100%;max-width: 100%;min-height: 200px;max-height: 300px;margin-bottom: 0.5em;border: 1px solid #ccc;overflow-y: scroll;background-color:gray; word-wrap:break-word">
                        <!-- CONVERSACION -->
                    </div>

                    <!-- NO SE MOSTRARA EL CHAT SI EL TICKET ESTA EN PRE-CIERRE O CERRADO -->
                    <?php if ($ticket['estatus'] != "precierre" && $ticket['estatus'] != "cerrado") { ?>
                        <div id="mensaje" class="md-form text-nowrap">

                            <!-- MENSAJES -->
                            <div class="input-group">
                                <input id="inputMensaje" class="form-control" data-msj="<?php echo $ticket['id_ticket'] ?>" data-tic="<?php echo $ticket['id_ticket'] ?>" data-usr="<?php echo $ticket['usuario'] ?>" data-loc="<?php echo $ticket['empresa'] ?>" data-tec="<?php echo $ticket['analista'] ?>" name="mensaje" type="text" placeholder="Escribir mensaje..." autocomplete="off">

                                <div class="input-group-append">
                                    <button class="adjuntarArchivo btn btn-dark" type="button" title="Adjuntar archivo" data-tic="<?php echo $ticket['id_ticket'] ?>">
                                        <i class="fa fa-plus"></i>
                                    </button>

                                    <button class="enviarMensaje btn btn-primary" data-tic="<?php echo $ticket['id_ticket'] ?>" data-usr="<?php echo $ticket['usuario'] ?>" data-loc="<?php echo $ticket['empresa'] ?>" data-tec="<?php echo $ticket['analista'] ?>" data-toggle="tooltip" data-bs-tooltip="" type="button" title="Enviar mensaje">
                                        ENVIAR
                                    </button>
                                </div>
                            </div>
                            <div class="input-group archivoAdjunto<?php echo $ticket['id_ticket'] ?>" style="margin-top: 10px">
                                <input type="hidden" name="MAX_FILE_SIZE" value="25000000">
                                <input id="archivo<?php echo $ticket['id_ticket'] ?>" class="btn btn-dark" type="file" name="archivo" accept=".jpg, .jpeg, .png, .gif, .bmp, .doc, .docx, .pdf, .xlsx, .xls, .txt">
                            </div>

                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- CERRAR TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="cerrar<?php echo $ticket['id_ticket'] ?>" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-check"></i>&nbsp;Cerrar ticket<br></h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <p>Esta a punto de cerrar el ticket seleccionado, <b>confirma que el analista a cargo
                            soluciono su problema</b>?</p>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button" data-dismiss="modal">NO</button>
                    <button class="btn btn-danger cerrarConfirmacion" type="button" data-cerrar-id="<?php echo $ticket['id_ticket'] ?>" data-analista="<?php echo $ticket['analista'] ?>" data-nombre-usuario="<?php echo $_SESSION['nombre'] ?>" data-empresa="<?php echo $ticket['empresa'] ?>" data-usuario="<?php echo $ticket['usuario'] ?>" data-dismiss="modal">SI</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ELIMINAR TICKET -->
    <div class="modal fade" role="dialog" tabindex="-1" id="eliminar<?php echo $ticket['id_ticket'] ?>" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-trash"></i>&nbsp;Eliminar ticket<br></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div id="" class="modal-body">
                    <p>Esta a punto de eliminar el ticket seleccionado, desea continuar?</p>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button" data-dismiss="modal" data-cancel="ok">NO</button>
                    <button class="btn btn-danger eliminarConfirmacion" type="button" data-eliminar-id="<?php echo $ticket['id_ticket'] ?>" data-dismiss="modal">SI</button>
                </div>
            </div>
        </div>
    </div>
<?php } else if (preg_match('/dashboard[a-z]*.php/i', $pagina)) { ?>
    <!-- CHAT INDIVIDUAL -->
    <div class="modal fade" role="dialog" tabindex="-1" id="chatWindow">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" style="color: #333;">
                        <strong><span>Chat directo</span></strong>
                    </h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="color: #333;">
                    <p style="margin: 0;color: #fff;background: #505050;padding: 2px;padding-left: 0.5em;padding-right: 0.5em;padding-top: 0.2em;padding-bottom: 0.2em;text-align:left">
                        <i class="fa fa-wechat"></i>&nbsp;
                        <span id="chatUser" style='color:#fff'>
                            <!-- USUARIO -->
                        </span>
                    </p>

                    <div id="msjWindow" style="padding: 0 0.5em; min-width: 100%;max-width: 100%;min-height: 200px;max-height: 300px; margin-bottom: 0.5em;border: 1px solid #ccc;overflow-y: scroll;background-color:gray; word-wrap:break-word">
                        <!-- CONVERSACION -->
                    </div>

                    <!-- INPUT DE CHAT -->
                    <div id="mensaje" class="md-form text-nowrap">

                        <div class="input-group">

                            <!-- MENSAJE -->
                            <input id="textoMensaje" class="form-control" type="text" name="mensaje" type="text" placeholder="Escribir mensaje..." autocomplete="off">

                            <!-- BOTONES DE MENSAJE -->
                            <div class="input-group-append">
                                <button id="adjuntarArchivo" class="btn btn-dark" type="button" data-toggle="tooltip" data-bs-tooltip="" type="button" title="Adjuntar archivo">
                                    <i class="fa fa-plus"></i>
                                </button>

                                <button id="enviarMsj" class="btn btn-primary" data-id-chat="" data-emisor="" data-receptor="" data-toggle="tooltip" data-bs-tooltip="" type="button" title="Enviar mensaje">
                                    ENVIAR
                                </button>
                            </div>

                        </div>
                        <!-- ARCHIVO ADJUNTOS -->
                        <div id="seleccionarArchivo" class="input-group" style="margin-top: 10px">
                            <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
                            <input id="archivoSeleccionado" class="btn btn-dark" type="file" name="archivo" accept=".jpg, .jpeg, .png, .gif, .bmp, .doc, .docx, .pdf, .xlsx, .xls, .txt">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
<?php } else if ($pagina == 'configuraciones.php') { ?>
    <!-- VENTANA MODAL ACTUALIZAR DATOS DE USUARIO -->
    <div class="modal fade" role="dialog" tabindex="-1" id="actualizarDatos">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" style="color: #333;"><i class="fa fa-user"></i>&nbsp;Editar datos de
                        usuario</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <form id="datosUsuario" class="justify-content-around" style="margin: 1em;">
                        <fieldset>
                            <input type="hidden" name="id" value="">
                            <input id="editUsrNombre" class="form-control" type="text" name="nombre" placeholder="Nombre completo" disabled>
                            <input id="editUsrUsuario" class="form-control" type="text" name="usuario" placeholder="Usuario" disabled>
                            <select id="editUsrNivel" class="form-control" name="nivel">
                                <option style="color:#aaa" value="">Nivel de usuario</option>
                                <option value="admin">Admin</option>
                                <option value="tecnico">Técnico</option>
                                <option value="gerente">Gerente</option>
                                <option value="analista">Analista</option>
                                <option value="usuario">Usuario comun</option>
                            </select>
                            <select id="editUsrEmpresa" class="form-control" name="empresa">
                                <option style="color:#555" value="" selected>Seleccione la empresa</option>
                                <!-- EMPRESAS -->
                            </select>
                            <select id="editUsrDepto" class="form-control" name="depto">
                                <option style="color:#555" value="" selected>Seleccione el depto</option>
                                <!-- DEPARTAMENTOS -->
                            </select>
                            <input id="editUsrClave" class="form-control" type="password" name="clave" placeholder="Clave">
                            <button class="btn btn-primary float-right actualizarBtn" data-user-id="" type="submit" data-dismiss="modal">ACTUALIZAR</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- VENTANA MODAL VER EMPRESAS -->
    <div class="modal fade" role="dialog" tabindex="-1" id="verEmpresas">
        <div class="modal-dialog" role="document" style="max-width:unset;margin:unset;">
            <div class="modal-content" style="width:30vw;margin:25vh auto;">
                <div class="modal-header">
                    <h4 class="modal-title" style="color: #333;"><i class="fa fa-building"></i> Empresas</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="max-height:250px;overflow-y:scroll">
                    <table id="empresasRegistradas" class="table table-striped table-hover">
                        <thead style="background: #505050;color:rgb(255,255,255);text-align:center">
                            <tr>
                                <th>id</th>
                                <th>Empresa</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- REGISTROS -->
                        </tbody>
                    </table>
                </div>
                <hr>
            </div>
        </div>
    </div>

    <!-- VENTANA MODAL VER DEPARTAMENTOS -->
    <div class="modal fade" role="dialog" tabindex="-1" id="verDeptos">
        <div class="modal-dialog" role="document" style="max-width:unset;margin:unset;">
            <div class="modal-content" style="width:35vw;margin:25vh auto;">
                <div class="modal-header">
                    <h4 class="modal-title" style="color: #333;"><i class="fa fa-briefcase"></i> Departamentos</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="max-height:250px;overflow-y:scroll">
                    <table id="empresaDeptos" class="table table-striped table-hover">
                        <thead style="background: #505050;color:rgb(255,255,255);text-align:center">
                            <tr>
                                <th>id</th>
                                <th>Empresa</th>
                                <th>Depto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- REGISTROS -->
                        </tbody>
                    </table>
                </div>
                <hr>
            </div>
        </div>
    </div>

    <!-- VENTANA MODAL VER CATEGORIAS -->
    <div class="modal fade" role="dialog" tabindex="-1" id="verCats">
        <div class="modal-dialog" role="document" style="max-width:unset;margin:unset;">
            <div class="modal-content" style="width:40vw;margin:25vh auto;">
                <div class="modal-header">
                    <h4 class="modal-title" style="color: #333;"><i class="fa fa-tags"></i> Categorias</h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="max-height:250px;overflow-y:scroll">
                    <table id="deptoCats" class="table table-striped table-hover">
                        <thead style="background: #505050;color:rgb(255,255,255);text-align:center">
                            <tr>
                                <th>id</th>
                                <th>Empresa</th>
                                <th>Departamento</th>
                                <th>Categoria</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- REGISTROS -->
                        </tbody>
                    </table>
                </div>
                <hr>
            </div>
        </div>
    </div>

    <!-- VENTANA MODAL ELIMINAR USUARIO -->
    <div class="modal fade" role="dialog" tabindex="-1" id="eliminarUsuario" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-trash"></i>&nbsp;Eliminar cuenta<br></h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <p>Esta a punto de eliminar su cuenta de usuario, desea continuar?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-dismiss="modal">NO</button>
                    <button class="btn btn-danger eliminarConfirmacion" data-user-id="" type="button" data-dismiss="modal">SI</button></a>
                </div>
            </div>
        </div>
    </div>

    <!-- VENTANA MODAL VER LISTA DE RESPALDOS -->
    <div class="modal fade" role="dialog" tabindex="-1" id="listaRespaldos" style="color: #212529;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-database"></i> Lista de respaldos<br></h4><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body" style="max-height:300px; overflow-y:scroll;">
                    <!-- LISTA DE RESPALDOS -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" data-dismiss="modal">CERRAR</button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>