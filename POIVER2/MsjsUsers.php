<?php
  header("Content-Type: text/html;charset=utf-8");
  include('config/config.php'); //
  $idConectado  = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
  $clickUser = isset($_REQUEST['clickUser']) ? (int)$_REQUEST['clickUser'] : 0;

  if ($idConectado == 0 || $clickUser == 0) {
      exit; // Salir si no hay datos válidos
  }

  // Consulta para obtener los mensajes de la conversación privada
  $Msjs = ("SELECT * FROM msjs 
            WHERE tipo_chat='privado' AND 
                  ((user_id = ? AND to_id = ?) OR (user_id = ? AND to_id = ?)) 
            ORDER BY id ASC");
            
  $stmt = mysqli_prepare($con, $Msjs);
  mysqli_stmt_bind_param($stmt, "iiii", $idConectado, $clickUser, $clickUser, $idConectado);
  mysqli_stmt_execute($stmt);
  $QueryMsjs = mysqli_stmt_get_result($stmt);

  if ($QueryMsjs) {
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
    <?php } else { // Mensaje recibido del otro usuario (Receiver) ?>
      <div class="row message-body">
        <div class="col-sm-12 message-main-receiver">
          <div class="receiver">
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
  } else {
    echo "<p>Error al cargar mensajes: " . mysqli_error($con) . "</p>";
  }
  mysqli_stmt_close($stmt);
  ?>