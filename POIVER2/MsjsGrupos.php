<?php
header("Content-Type: text/html;charset=utf-8"); //
include('config/config.php'); //
$id_grupo_seleccionado = $_REQUEST['id_grupo']; // El ID del grupo
$idConectado = $_REQUEST['idConectado'];     // El ID del usuario actual

$Msjs = ("SELECT m.*, u.nombre_apellido as nombre_remitente, u.imagen as imagen_remitente 
            FROM msjs m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.id_grupo_receptor = '$id_grupo_seleccionado' 
            ORDER BY m.id ASC");
$QueryMsjs = mysqli_query($con, $Msjs); //

while ($UserMsjs = mysqli_fetch_array($QueryMsjs)) { //
    $archivo = $UserMsjs['archivos']; //
    // ... (lógica de extensión de archivo si es necesario)

    if ($idConectado == $UserMsjs['user_id']) { // Mensaje enviado por el usuario actual
?>
    <div class="row message-body">
      <div class="col-sm-12 message-main-sender">
        <div class="sender">
          <div class="message-text">
            <?php
            if (!empty($UserMsjs['message'])) { //
                echo htmlspecialchars($UserMsjs['message']); //
            } else if (!empty($archivo)) { //
            ?>
              <img src="<?php echo 'archivos/' . htmlspecialchars($archivo); ?>" style="width: 100%; max-width: 250px;"> 
              <div class="row">
                <div class="col-md-12">
                  <a class="boton_desc" download="" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar</a> 
                </div>
              </div>
            <?php } ?>
          </div>
          <span class="message-time pull-right">
            <?php echo $UserMsjs['fecha'];  ?> 
          </span>
        </div>
      </div>
    </div>
<?php } else { // Mensaje recibido de otro miembro del grupo ?>
    <div class="row message-body">
      <div class="col-sm-12 message-main-receiver">
        <div class="receiver">
          <strong style="display:block; font-size:0.9em; color:#00796B;"><?php echo htmlspecialchars($UserMsjs['nombre_remitente']); ?>:</strong>
          <div class="message-text">
            <?php
            if (!empty($UserMsjs['message'])) { //
                echo htmlspecialchars($UserMsjs['message']); //
            } else if (!empty($archivo)) { //
            ?>
              <img src="<?php echo 'archivos/' . htmlspecialchars($archivo); ?>" style="width: 100%; max-width: 250px;"> 
              <div class="row">
                <div class="col-md-12">
                  <a class="boton_desc" download="" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar</a> 
                </div>
              </div>
            <?php } ?>
          </div>
          <span class="message-time pull-right">
            <?php echo $UserMsjs['fecha'];  ?> 
          </span>
        </div>
      </div>
    </div>
<?php
    } // Fin del else
} // Fin del while
?>