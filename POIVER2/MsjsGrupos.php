<?php
header("Content-Type: text/html;charset=utf-8");
include('config/config.php'); //
$id_grupo_seleccionado = isset($_REQUEST['id_grupo']) ? (int)$_REQUEST['id_grupo'] : 0;
$idConectado = isset($_REQUEST['idConectado']) ? (int)$_REQUEST['idConectado'] : 0;

if ($id_grupo_seleccionado == 0 || $idConectado == 0) {
    exit; // Salir si no hay datos válidos
}

$Msjs = ("SELECT m.*, u.nombre_apellido as nombre_remitente, u.imagen as imagen_remitente 
            FROM msjs m 
            JOIN users u ON m.user_id = u.id 
            WHERE m.id_grupo_receptor = ? AND m.tipo_chat = 'grupal'
            ORDER BY m.id ASC");

$stmt = mysqli_prepare($con, $Msjs);
mysqli_stmt_bind_param($stmt, "i", $id_grupo_seleccionado);
mysqli_stmt_execute($stmt);
$QueryMsjs = mysqli_stmt_get_result($stmt);

while ($UserMsjs = mysqli_fetch_array($QueryMsjs)) {
    $archivo = $UserMsjs['archivos'];
    $tipo_mensaje = $UserMsjs['tipo_mensaje'];

    if ($idConectado == $UserMsjs['user_id']) { // Mensaje enviado por el usuario actual (Sender)
?>
    <div class="row message-body">
      <div class="col-sm-12 message-main-sender">
        <div class="sender">
          <div class="message-text">
            <?php
              if ($tipo_mensaje === 'ubicacion' && !empty($UserMsjs['latitud'])) {
                  $lat = htmlspecialchars($UserMsjs['latitud']);
                  $lon = htmlspecialchars($UserMsjs['longitud']);
                  $mapsUrl = "https://www.google.com/maps?q={$lat},{$lon}&z=15";
                  $mapIcon = "assets/img/map-icon.png";
            ?>
              <a href="<?php echo $mapsUrl; ?>" target="_blank" class="location-message">
                  <img src="<?php echo $mapIcon; ?>" alt="Mapa" class="map-thumbnail">
                  <span class="location-text">
                      Mi ubicación
                      <small>Ver en el mapa</small>
                  </span>
              </a>
            <?php
              } else if (!empty($archivo)) { // Mensaje de archivo
            ?>
              <img src="<?php echo 'archivos/' . htmlspecialchars($archivo); ?>" style="width: 100%; max-width: 250px; border-radius: 5px;">
              <div class="row">
                <div class="col-md-12">
                  <a class="boton_desc" download="<?php echo htmlspecialchars($archivo); ?>" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar</a>
                </div>
              </div>
            <?php
              } else { // Mensaje de texto por defecto
                  echo htmlspecialchars($UserMsjs['message']);
              }
            ?>
          </div>
          <span class="message-time pull-right">
            <?php echo htmlspecialchars($UserMsjs['fecha']);  ?>
          </span>
        </div>
      </div>
    </div>
<?php } else { // Mensaje recibido de otro miembro del grupo (Receiver) ?>
    <div class="row message-body">
      <div class="col-sm-12 message-main-receiver">
        <div class="receiver">
          <strong style="display:block; font-size:0.9em; color:#00796B;"><?php echo htmlspecialchars($UserMsjs['nombre_remitente']); ?>:</strong>
          <div class="message-text">
            <?php
              if ($tipo_mensaje === 'ubicacion' && !empty($UserMsjs['latitud'])) {
                  $lat = htmlspecialchars($UserMsjs['latitud']);
                  $lon = htmlspecialchars($UserMsjs['longitud']);
                  $mapsUrl = "https://www.google.com/maps?q={$lat},{$lon}&z=15";
                  $mapIcon = "assets/img/map-icon.png";
            ?>
              <a href="<?php echo $mapsUrl; ?>" target="_blank" class="location-message">
                  <img src="<?php echo $mapIcon; ?>" alt="Mapa" class="map-thumbnail">
                  <span class="location-text">
                      Ubicación compartida
                      <small>Ver en el mapa</small>
                  </span>
              </a>
            <?php
              } else if (!empty($archivo)) { // Mensaje de archivo
            ?>
              <img src="<?php echo 'archivos/' . htmlspecialchars($archivo); ?>" style="width: 100%; max-width: 250px; border-radius: 5px;">
              <div class="row">
                <div class="col-md-12">
                  <a class="boton_desc" download="<?php echo htmlspecialchars($archivo); ?>" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar</a>
                </div>
              </div>
            <?php
              } else { // Mensaje de texto por defecto
                  echo htmlspecialchars($UserMsjs['message']);
              }
            ?>
          </div>
          <span class="message-time pull-right">
            <?php echo htmlspecialchars($UserMsjs['fecha']);  ?>
          </span>
        </div>
      </div>
    </div>
<?php
    } // Fin del else
} // Fin del while
mysqli_stmt_close($stmt);
?>