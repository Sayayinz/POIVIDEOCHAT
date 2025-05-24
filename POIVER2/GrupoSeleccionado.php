<?php
sleep(1);
header("Content-Type: text/html;charset=utf-8");
session_start(); // Asegúrate que la sesión esté iniciada para acceder a $_SESSION
include('config/config.php');

$id_grupo_seleccionado = $_REQUEST['id']; // Ahora este es id_grupo
$idConectado           = $_REQUEST['idConectado'];
$email_user            = $_REQUEST['email_user'];

// (Opcional) Actualizar "mensajes leídos" para este usuario en este grupo
// Esta lógica es más compleja que en chats privados. Podrías, por ejemplo,
// registrar la última vez que el usuario vio el grupo.

$QueryGrupoInfo = ("SELECT * FROM grupos WHERE id_grupo='$id_grupo_seleccionado' LIMIT 1");
$ResultadoGrupoInfo = mysqli_query($con, $QueryGrupoInfo);
$rowGrupo = mysqli_fetch_array($ResultadoGrupoInfo);

if ($rowGrupo) {
?>
  <div class="status-bar"> </div>
  <div class="row heading">
    <div class="col-sm-2 col-xs-2 heading-avatar">
      <a href="./" style="color: #fff;">
        <div class="heading-avatar-icon">
          <i class="zmdi zmdi-arrow-left" style="font-size:20px; vertical-align: middle; margin-right: 5px;"></i>
        
          <img src="<?php echo 'group_images/' . ($rowGrupo['imagen_grupo'] ? $rowGrupo['imagen_grupo'] : 'default_group_icon.png'); ?>" style="vertical-align: middle; border-radius:50%; width:40px; height:40px;">
        </div>
      </a>
    </div>
    <div class="col-sm-10 col-xs-10 heading-name" style="padding-left: 0px;">
      <a class="heading-name-meta" style="padding-left:0px;">
        <i class="zmdi zmdi-accounts" style="margin-right: 5px;"></i><?php echo htmlspecialchars($rowGrupo['nombre_grupo']); ?>
      </a>
      
    </div>
    
  </div>

  <div class="row message" id="conversation">
    <?php
    // Actualizar clickuser para grupos. Podrías necesitar una columna 'clickTipo' o un manejo diferente.
    // Por ahora, asumimos que 'clickuser' guarda el ID del chat activo (sea usuario o grupo)
    $UpdateClick = ("UPDATE clickuser SET clickUser='$id_grupo_seleccionado', tipoClick='group' WHERE UserIdSession='$idConectado'"); // Añadir tipoClick
    // ... (manejo de inserción si no existe)
    mysqli_query($con, $UpdateClick);

    // Mostrando mensajes del grupo
    $Msjs = ("SELECT m.*, u.nombre_apellido as nombre_remitente, u.imagen as imagen_remitente 
              FROM msjs m 
              JOIN users u ON m.user_id = u.id 
              WHERE m.id_grupo_receptor = '$id_grupo_seleccionado' 
              ORDER BY m.id ASC");
    $QueryMsjs = mysqli_query($con, $Msjs);

    while ($UserMsjs = mysqli_fetch_array($QueryMsjs)) {
        $esRemitenteConectado = ($idConectado == $UserMsjs['user_id']);
    ?>
      <div class="row message-body">
        <?php if ($esRemitenteConectado) { ?>
          <div class="col-sm-12 message-main-sender">
            <div class="sender">
              <div class="message-text">
                <?php echo htmlspecialchars($UserMsjs['message']); ?>
              
              </div>
              <span class="message-time pull-right">
                <?php echo $UserMsjs['fecha'];  ?>
              </span>
            </div>
          </div>
        <?php } else { ?>
          <div class="col-sm-12 message-main-receiver">
            <div class="receiver">
              <strong style="display:block; font-size:0.9em; color:#00796B;"><?php echo htmlspecialchars($UserMsjs['nombre_remitente']); ?>:</strong>
              <div class="message-text">
                <?php echo htmlspecialchars($UserMsjs['message']); ?>
                 

              </div>
              <span class="message-time pull-right">
                <?php echo $UserMsjs['fecha'];  ?>
              </span>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>

  <div class="row reply" id="formnormal">
    <form class="conversation-compose" id="formenviarmsjGrupo" name="formEnviaMsjGrupo">
      <input type="hidden" name="user_id" value="<?php echo $idConectado; ?>">
      <input type="hidden" name="id_grupo_receptor" value="<?php echo $id_grupo_seleccionado; ?>">
      <input type="hidden" name="user" value="<?php echo $email_user; ?>">
      
      <input type="hidden" name="tipo_chat" value="grupal">


      <div class="emoji">
        
      </div>
      <input class="input-msg" name="message" id="messageGrupo" placeholder="Escribir mensaje al grupo..." autocomplete="off" autofocus="autofocus" required="true">
     
    </form>
  </div>


  <audio class="audio" style="display:none;">
    <source src="effect.mp3" type="audio/mp3">
  </audio>
  <script type="text/javascript">
    // Scroll al final
    var scrolltoh = $('#conversation')[0].scrollHeight;
    $('#conversation').scrollTop(scrolltoh);

    // Envío de mensajes para grupos
    $("#formenviarmsjGrupo").keypress(function(e) {
        if (e.which == 13) {
            var url = "acciones/RegistMsj.php"; // El mismo script puede manejarlo si se adapta
            $.ajax({
                type: "POST",
                url: url,
                data: $("#formenviarmsjGrupo").serialize(), // Asegúrate que este form tenga los campos correctos
                success: function(data) {
                    // Cargar mensajes del grupo
                    $("#conversation").load('MsjsGrupos.php?id_grupo=<?php echo $id_grupo_seleccionado; ?>&idConectado=<?php echo $idConectado; ?>'); // Necesitarás MsjsGrupos.php
                    $("#messageGrupo").val("");
                    $(".audio")[0].play();
                    
                    // Actualizar barra de progreso (si aplica también a grupos)
                    progreso += 5; // Asumiendo que 'progreso' es una variable global o accesible
                    if (progreso >= 100) {
                        alert("¡Recompensa de grupo desbloqueada!"); // O la lógica de recompensa
                        progreso = 0;
                    }
                    $('#chatProgress').val(progreso); // Asegúrate que #chatProgress exista o sea relevante aquí
                }
            });
            return false;
        }
    });
    // Lógica para enviar imágenes a grupos (similar a UserSeleccionado.php, adaptando el form y el script de subida)
    // ...
  </script>
<?php
} else {
    echo "<p>Grupo no encontrado.</p>";
}
?>
</body>
</html>