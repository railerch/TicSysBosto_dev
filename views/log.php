<table id="sysLogs" class="table table-bordered align-middle" style="text-align:center; width:100% !important; font-family:monospace; font-size:0.8em;">
    <thead>
        <tr style="background: #353535;color: rgb(255,255,255);">
            <td>ID</td>
            <th>Fecha</th>
            <th >IP</th>
            <th>Usuario</th>
            <th style="max-width:35%">Plataforma/Navegador</th>
            <th>Actividad</th>
        </tr>
    </thead>
    <tbody>
        <?php 
            // ABRIR ARCHIVO DE REGISTROS
            $logsHandle = fopen('../log.md', 'r');
            $contador = 1;

            while($registro = fgets($logsHandle) ){
                $datos = explode('|', $registro);

                // FORMATEAR CONTADOR
                if($contador < 10){
                    $contador = '0'.$contador;
                }else{
                    $contador = $contador;
                }
        ?>
        <tr>
            <td style="width: 5% !important"><?php echo $contador?></td>
            <td><?php echo $datos[0]?></td>
            <td><?php echo $datos[1]?></td>
            <td><?php if($datos[2] != '  () '){echo $datos[2]; }else{ echo 'N/A'; }?></td>
            <td style="max-width:35%"><?php echo $datos[3]?></td>
            <td><?php echo $datos[4]?></td>
        </tr>
        <?php $contador++; }?>
    </tbody>
</table>

<script type="text/javascript">
$(document).ready(function() {
    
    // IDIOMA ESPAÑOL PARA DATATABLES
    $("#sysLogs").DataTable({
        "language": {
            "url": "config/dataTableSpanish.json"
        }
    });
})
</script>