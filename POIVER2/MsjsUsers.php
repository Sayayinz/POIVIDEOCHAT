<?php
  header("Content-Type: text/html;charset=utf-8");
  include('config/config.php');
  $idConectado  = $_REQUEST['id']; // El usuario que está viendo el chat

  // Determinar con quién es el chat (el 'clickUser')
  if (isset($_REQUEST['clickUser']) && !empty($_REQUEST['clickUser'])) {
      $clickUser = $_REQUEST['clickUser'];
  } else {
      // Si no se pasa clickUser, intenta obtenerlo de la tabla clickuser
      // Esta lógica asume que clickuser siempre tiene el ID del *otro* participante en un chat privado
      $QueryUserClick = ("SELECT clickUser FROM clickuser WHERE UserIdSession='$idConectado' AND tipoClick='user' LIMIT 1 ");
      $QueryClick     = mysqli_query($con, $QueryUserClick);
      if ($QueryClick && mysqli_num_rows($QueryClick) > 0) {
        $UserClickData  = mysqli_fetch_array($QueryClick);
        $clickUser      = $UserClickData['clickUser'];
      } else {
        // No se pudo determinar el otro usuario del chat, no mostrar mensajes o mostrar un error.
        // echo "<p>No se pudo determinar el chat.</p>";
        exit; 
      }
  }

  // Ahora $clickUser tiene el ID del OTRO usuario en la conversación privada
  $Msjs = ("SELECT * FROM msjs 
            WHERE tipo_chat='privado' AND 
                  ((user_id ='$idConectado' AND to_id='$clickUser') OR (user_id='$clickUser' AND to_id='$idConectado')) 
            ORDER BY id ASC");
  $QueryMsjs = mysqli_query($con, $Msjs);

  if ($QueryMsjs) {
    while ($UserMsjs = mysqli_fetch_array($QueryMsjs)) {
      $archivo = $UserMsjs['archivos'];
      // $explode = explode('.', $archivo); // No necesitas esto a menos que uses la extensión para algo
      // $extension_arch = array_pop($explode);

      if ($idConectado == $UserMsjs['user_id']) { // Mensaje enviado por el usuario actual
?>
      <div class="row message-body">
        <div class="col-sm-12 message-main-sender">
          <div class="sender">
            <div class="message-text">
              <?php
              if (!empty($UserMsjs['message'])) {
                echo htmlspecialchars($UserMsjs['message']); // Siempre sanitiza la salida
              } else if (!empty($archivo)) { ?>
                <img src="<?php echo 'archivos/' . htmlspecialchars($archivo); ?>" style="width: 100%; max-width: 250px;">
                <div class="row">
                  <div class="col-md-12">
                    <a class="boton_desc" download="<?php echo htmlspecialchars($archivo); ?>" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar
                    </a>
                  </div>
                </div>
              <?php } ?>
            </div>
            <span class="message-time pull-right">
              <?php echo htmlspecialchars($UserMsjs['fecha']);  ?>
            </span>
          </div>
        </div>
      </div>
    <?php } else { // Mensaje recibido del otro usuario ?>
      <div class="row message-body">
        <div class="col-sm-12 message-main-receiver">
          <div class="receiver">
            <div class="message-text">
              <?php
              if (!empty($UserMsjs['message'])) {
                echo htmlspecialchars($UserMsjs['message']);
              } else if (!empty($archivo)) { ?>
                <img src="<?php echo 'archivos/' . htmlspecialchars($UserMsjs['archivos']); ?>" style="width: 100%; max-width: 250px;">
                <div class="row">
                  <div class="col-md-12">
                    <a class="boton_desc" download="<?php echo htmlspecialchars($archivo); ?>" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar
                    </a>
                  </div>
                </div>
              <?php } ?>
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
  ?>