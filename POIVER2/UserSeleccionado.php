<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Chat - WhatApp!</title>
  <style type="text/css" media="screen">
    .zmdi-mail-reply:hover {
      color: #00796B !important;
      cursor: pointer;
    }

    .zmdi-comment-image:hover {
      color: #00796B !important;
      cursor: pointer;
    }

    .uploadFile {
      visibility: hidden;
    }

    #uploadIcon {
      cursor: pointer;
    }

    .camara {
      font-size: 45px;
      float: right !important;
      margin-left: 1000px;
      margin-top: -5px;
    }

    .camara:hover {
      color: #333;
    }

    .fa-microphone:hover {
      cursor: pointer;
      color: #333;
    }
    /* Estilo para el botón de videollamada */
    #videoCallBtn {
        color: white;
        background: none;
        border: none;
        font-size: 28px; /* Ajusta según tu diseño */
        padding: 0px 10px;
        vertical-align: middle;
        cursor: pointer;
    }
    #videoCallBtn:hover {
        color: #f0f0f0;
    }
    /* Animación para el fondo de recompensa */
    @keyframes rainbowBackground {
        0%{background-position:0% 50%}
        50%{background-position:100% 50%}
        100%{background-position:0% 50%}
    }
  </style>
</head>

<body>

  <?php
  session_start(); 
  header("Content-Type: text/html;charset=utf-8");

  include('config/config.php');
  
  $IdUser                 = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
  $idConectado            = isset($_REQUEST['idConectado']) ? (int)$_REQUEST['idConectado'] : (isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0);
  $email_user             = isset($_REQUEST['email_user']) ? filter_var($_REQUEST['email_user'], FILTER_SANITIZE_EMAIL) : (isset($_SESSION['email_user']) ? $_SESSION['email_user'] : '');
  // $tipoChatSeleccionado   = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'user'; // Ya no se usa aquí directamente

  if ($IdUser == 0 || $idConectado == 0) {
      echo "<p>Error: Faltan datos para cargar el chat.</p>";
      exit;
  }

  if (!empty($IdUser)) {
    $stmtLeyendo = mysqli_prepare($con, "UPDATE msjs SET leido = 'SI' WHERE user_id=? AND to_id=? AND tipo_chat='privado'");
    if ($stmtLeyendo) {
        mysqli_stmt_bind_param($stmtLeyendo, "ii", $IdUser, $idConectado);
        mysqli_stmt_execute($stmtLeyendo);
        mysqli_stmt_close($stmtLeyendo);
    }
  }

  $QueryUserSeleccionado = "SELECT *, puntos_recompensa, nivel_recompensa FROM users WHERE id= ? LIMIT 1"; //
  $stmtUserSel = mysqli_prepare($con, $QueryUserSeleccionado);
  mysqli_stmt_bind_param($stmtUserSel, "i", $IdUser);
  mysqli_stmt_execute($stmtUserSel);
  $QuerySeleccionado = mysqli_stmt_get_result($stmtUserSel);


  if ($QuerySeleccionado && $rowUser = mysqli_fetch_array($QuerySeleccionado)) {
  ?>
  <div class="status-bar"> </div>
  <div class="row heading">
  
    <div class="col-sm-2 col-xs-2 heading-avatar">
      <a href="./" style="color: #fff;">
        <div class="heading-avatar-icon">
          <i class="zmdi zmdi-arrow-left" style="font-size:20px; vertical-align: middle; margin-right: 5px;"></i>
          <img src="<?php echo 'imagenesperfil/' . htmlspecialchars($rowUser['imagen']); ?>" style="vertical-align: middle; width:40px; height:40px; border-radius:50%;">
        </div>
      </a>
    </div>
    <div class="col-sm-7 col-xs-7 heading-name" style="padding-left: 0px;">
      <a class="heading-name-meta" style="padding-left:0px;">
        <?php echo htmlspecialchars($rowUser['nombre_apellido']); ?>
      </a>
      <div style="margin: 0px 0;">
        <label style="font-size: 0.8em; color: #f0f0f0;">Progreso de actividad:
        <progress id="chatProgress" value="0" max="100" style="width: 100%; height: 10px;"></progress></label>
      </div>
    </div>
    <div class="col-sm-3 col-xs-3 heading-icons text-right" style="padding-top: 5px;">
      <button id="videoCallBtn" title="Iniciar videollamada">
          <i class="zmdi zmdi-videocam"></i>
      </button>
    </div>
  </div>

    <div class="row message" id="conversation">
      <?php
      $QueryVerificaClickUser = "SELECT id FROM clickuser WHERE UserIdSession= ?";
      $stmtVerifica = mysqli_prepare($con, $QueryVerificaClickUser);
      mysqli_stmt_bind_param($stmtVerifica, "i", $idConectado);
      mysqli_stmt_execute($stmtVerifica);
      $ResVerificaClickUser = mysqli_stmt_get_result($stmtVerifica);

      $stmtClickUser = false;
      if(mysqli_num_rows($ResVerificaClickUser) > 0){
          $stmtClickUser = mysqli_prepare($con, "UPDATE clickuser SET clickUser=?, tipoClick='user' WHERE UserIdSession=?");
          if ($stmtClickUser) mysqli_stmt_bind_param($stmtClickUser, "ii", $IdUser, $idConectado);
      } else {
          $stmtClickUser = mysqli_prepare($con, "INSERT INTO clickuser (UserIdSession, clickUser, tipoClick) VALUES (?, ?, 'user')");
          if ($stmtClickUser) mysqli_stmt_bind_param($stmtClickUser, "iis", $idConectado, $IdUser); // tipoClick es ENUM, no necesita 's' si es 'user' o 'group' directamente
                                                                                                   // Debería ser "ii" si $IdUser es int
      }
      if ($stmtClickUser) {
          mysqli_stmt_execute($stmtClickUser);
          mysqli_stmt_close($stmtClickUser);
      }
      mysqli_stmt_close($stmtVerifica);


      $MsjsQuery = "SELECT * FROM msjs WHERE tipo_chat='privado' AND 
                ((user_id = ? AND to_id= ?) OR (user_id= ? AND to_id= ?)) 
                ORDER BY id ASC";
      $stmtMsjs = mysqli_prepare($con, $MsjsQuery);
      mysqli_stmt_bind_param($stmtMsjs, "iiii", $idConectado, $IdUser, $IdUser, $idConectado);
      mysqli_stmt_execute($stmtMsjs);
      $QueryMsjs = mysqli_stmt_get_result($stmtMsjs);


      if ($QueryMsjs) {
        while ($UserMsjs = mysqli_fetch_array($QueryMsjs)) {
          $archivo = $UserMsjs['archivos'];

          if ($idConectado == $UserMsjs['user_id']) { ?>
            <div class="row message-body">
              <div class="col-sm-12 message-main-sender">
                <div class="sender">
                  <div class="message-text">
                    <?php
                    if (!empty($UserMsjs['message'])) {
                      echo htmlspecialchars($UserMsjs['message']);
                    } else if (!empty($archivo)) { ?>
                      <img src="<?php echo 'archivos/' . htmlspecialchars($archivo); ?>" style="width: 100%; max-width: 250px;">
                      <div class="row">
                        <div class="col-md-12">
                          <a class="boton_desc" download="<?php echo htmlspecialchars($archivo); ?>" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar</a>
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
          <?php } else { ?>
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
                          <a class="boton_desc" download="<?php echo htmlspecialchars($archivo); ?>" href="archivos/<?php echo htmlspecialchars($archivo); ?>" title="Descargar Imagen">Descargar</a>
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
          }
        }
        mysqli_stmt_close($stmtMsjs);
      } else {
        echo "<p>Error al cargar mensajes: " . mysqli_error($con) . "</p>";
      }
      ?>
    </div>

    <div class="row reply" id="formnormal">
      <form class="conversation-compose" id="formenviarmsj" name="formEnviaMsj">
        <input type="hidden" name="user_id" value="<?php echo $idConectado; ?>">
        <input type="hidden" name="to_id" value="<?php echo $rowUser['id']; ?>">
        <input type="hidden" name="user" value="<?php echo htmlspecialchars($email_user); ?>">
        <input type="hidden" name="to_user" value="<?php echo htmlspecialchars($rowUser['nombre_apellido']); ?>">
        <input type="hidden" name="tipo_chat" value="privado">

       <div class="emoji" id="btnCompartirUbicacion" title="Compartir ubicación" style="cursor: pointer;">
  <i class="zmdi zmdi-pin" style="font-size: 24px; color: #7d8489;"></i>
</div>
        
        <input class="input-msg" name="message" id="message" placeholder="Escribir tu Mensaje y presiona Enter..." autocomplete="off" autofocus="autofocus" required="true">
        <i class="zmdi zmdi-comment-image" style="font-size: 45px; color: grey;" title="Enviar Imagen." id="mostrarformenviarimg"></i>
      </form>
    </div>

    <audio class="audio" style="display:none;">
      <source src="effect.mp3" type="audio/mp3">
    </audio>

    <div class="row reply" id="formenviaimg">
      <form class="conversation-compose" id="formEnviarArchivo" name="formEnviarArchivo" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo $idConectado; ?>">
        <input type="hidden" name="to_id" value="<?php echo $rowUser['id']; ?>">
        <input type="hidden" name="to_user" value="<?php echo htmlspecialchars($rowUser['nombre_apellido']); ?> ">
        <input type="hidden" name="user" value="<?php echo htmlspecialchars($email_user); ?>">
        <input type="hidden" name="tipo_chat" value="privado_archivo"> 
        <div class="col-sm-12 col-xs-12 reply-recording">
          <label for="uploadFile" id="uploadIcon">
            <i class="zmdi zmdi-camera camara"></i>
          </label>
          <input type="file" name="namearchivo" id="uploadFile" class="uploadFile" required />
        </div>
        <button type="submit" class="send" id="botonenviarimg">
          <div class="circle">
            <i class="zmdi zmdi-mail-send" title="Enviar Imagen..."></i>
          </div>
        </button>
        <i class="zmdi zmdi-mail-reply" style="font-size: 50px;color: grey;" id="volverformnormal" title="Volver . ."></i>
      </form>
    </div>
    
  <?php 
  mysqli_stmt_close($stmtUserSel); // Cerrar la sentencia preparada para la información del usuario
  } else {
    echo "<p>Usuario no encontrado o error en la consulta.</p>";
  }
  ?>


<script type="text/javascript">

if (typeof window.PUNTOS_NIVEL === 'undefined') {
    window.PUNTOS_NIVEL = {
        1: 0, 2: 100, 3: 250, 4: 500, 5: 1000,
    };
}
if (typeof window.MAX_NIVEL === 'undefined') {
    window.MAX_NIVEL = 5;
}

$(function() {
    // Ahora puedes usar window.PUNTOS_NIVEL y window.MAX_NIVEL dentro de esta función.
    // O si prefieres, puedes asignarlas a variables locales para mayor claridad aquí:
    const PUNTOS_NIVEL_LOCAL = window.PUNTOS_NIVEL;
    const MAX_NIVEL_LOCAL = window.MAX_NIVEL;

    var puntos_totales_usuario_conectado;
    var nivel_usuario_conectado_actual;
    var currentChatProgresoView = 0;
    
    var idConectadoGlobal = parseInt("<?php echo $idConectado; ?>");
    var idUsuarioChatActualGlobal = parseInt($("input[name='to_id']", "#formenviarmsj").val());

    // console.log("UserSeleccionado.php cargado para chat con:", idUsuarioChatActualGlobal, ". Usuario conectado:", idConectadoGlobal);

    var $chatProgress = $('#chatProgress');
    if ($chatProgress.length === 0) {
        // console.error("Elemento #chatProgress no encontrado.");
    } else {
        $chatProgress.val(0);
    }

    function cargarDatosInicialesUsuarioConectado() {
        // console.log("Solicitando datos iniciales para usuario:", idConectadoGlobal);
        $.ajax({
            url: 'acciones/get_user_reward_info.php',
            type: 'POST',
            data: { userId: idConectadoGlobal },
            dataType: 'json',
            success: function(rewardInfo) {
                if (rewardInfo && rewardInfo.status === 'success') {
                    puntos_totales_usuario_conectado = parseInt(rewardInfo.puntos_recompensa);
                    nivel_usuario_conectado_actual = parseInt(rewardInfo.nivel_recompensa);
                    // console.log("Datos iniciales recibidos: Puntos=" + puntos_totales_usuario_conectado + ", Nivel=" + nivel_usuario_conectado_actual);
                    actualizarBarraProgresoNivel();
                } else {
                    // console.error("Error get_user_reward_info:", rewardInfo.message);
                    puntos_totales_usuario_conectado = 0;
                    nivel_usuario_conectado_actual = 1;
                    actualizarBarraProgresoNivel();
                }
            },
            error: function() {
                // console.error("Error AJAX get_user_reward_info.");
                puntos_totales_usuario_conectado = 0;
                nivel_usuario_conectado_actual = 1;
                actualizarBarraProgresoNivel();
            }
        });
    }
    
    cargarDatosInicialesUsuarioConectado();

    function scrollConversationToEnd() {
        var conversation = $('#conversation');
        if (conversation.length > 0 && conversation[0].scrollHeight) {
            conversation.scrollTop(conversation[0].scrollHeight);
        }
    }
    // setTimeout(scrollConversationToEnd, 100); // Scroll inicial un poco después

    if (!isNaN(idUsuarioChatActualGlobal) && idUsuarioChatActualGlobal !== 0) {
        // Llama a la función global definida en home.php
        startChatUpdate('user', idUsuarioChatActualGlobal, idConectadoGlobal);
    }

    function getPuntosParaSiguienteNivel(nivelActual) {
        // Usar las constantes locales o las de window
        if (nivelActual >= MAX_NIVEL_LOCAL || !PUNTOS_NIVEL_LOCAL[nivelActual + 1]) {
            return PUNTOS_NIVEL_LOCAL[MAX_NIVEL_LOCAL] + (PUNTOS_NIVEL_LOCAL[MAX_NIVEL_LOCAL] - (PUNTOS_NIVEL_LOCAL[MAX_NIVEL_LOCAL-1] || PUNTOS_NIVEL_LOCAL[MAX_NIVEL_LOCAL]-100) );
        }
        return PUNTOS_NIVEL_LOCAL[nivelActual + 1];
    }

    function getPuntosDelNivelActual(nivelActual) {
        return PUNTOS_NIVEL_LOCAL[nivelActual] || PUNTOS_NIVEL_LOCAL[1];
    }

    function actualizarBarraProgresoNivel() {
        if (typeof puntos_totales_usuario_conectado === 'undefined' || typeof nivel_usuario_conectado_actual === 'undefined') {
            if ($chatProgress.length) $chatProgress.val(0);
            return;
        }

        let puntosNivelBase = getPuntosDelNivelActual(nivel_usuario_conectado_actual);
        let puntosParaSiguiente = getPuntosParaSiguienteNivel(nivel_usuario_conectado_actual);
        
        let progresoEnNivel = puntos_totales_usuario_conectado - puntosNivelBase;
        let rangoDelNivel = puntosParaSiguiente - puntosNivelBase;

        if ($chatProgress.length) {
            if (nivel_usuario_conectado_actual >= MAX_NIVEL_LOCAL && puntos_totales_usuario_conectado >= PUNTOS_NIVEL_LOCAL[MAX_NIVEL_LOCAL]) {
                $chatProgress.val(100);
            } else if (rangoDelNivel > 0) {
                let porcentajeProgreso = (progresoEnNivel / rangoDelNivel) * 100;
                $chatProgress.val(Math.min(Math.max(porcentajeProgreso, 0), 100));
            } else { 
                $chatProgress.val(100);
            }
        }
    }

    $("#formenviarmsj .input-msg").off('keypress').on('keypress', function(e) {
        var $inputMsg = $(this);
        if (e.which == 13 && $inputMsg.val().trim() !== '') {
            e.preventDefault();
            var form = $("#formenviarmsj");
            var url = "acciones/RegistMsj.php";
            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function(data_regist_msj) {
                    $("#conversation").load('MsjsUsers.php?id=' + idConectadoGlobal + '&clickUser=' + idUsuarioChatActualGlobal, function() {
                        scrollConversationToEnd();
                    });
                    $inputMsg.val("");
                    if ($(".audio").length > 0) $(".audio")[0].play();

                    currentChatProgresoView += 5;
                    if (currentChatProgresoView >= 100) {
                        $('#conversation').css({
                            'background': 'linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet)',
                            'background-size': '200% 200%',
                            'animation': 'rainbowBackground 5s ease infinite'
                        });
                        setTimeout(function(){
                            $('#conversation').css({
                            'background': 'url(\'assets/img/fondochat.jpg\')',
                            'animation': 'none'
                            });
                        }, 10000);
                        currentChatProgresoView = 0;
                    }
                    
                    let nivel_anterior_conectado = nivel_usuario_conectado_actual;
                    $.ajax({
                        url: 'acciones/get_user_reward_info.php',
                        type: 'POST',
                        data: { userId: idConectadoGlobal },
                        dataType: 'json',
                        success: function(rewardInfo) {
                            if (rewardInfo && rewardInfo.status === 'success') {
                                puntos_totales_usuario_conectado = parseInt(rewardInfo.puntos_recompensa);
                                nivel_usuario_conectado_actual = parseInt(rewardInfo.nivel_recompensa);
                                actualizarBarraProgresoNivel();
                                if (nivel_usuario_conectado_actual > nivel_anterior_conectado) {
                                    alert("¡Felicidades! Has subido al Nivel " + nivel_usuario_conectado_actual + " 🎉😎✨");
                                    // Aquí podrías llamar a la función para recargar la lista de usuarios si es necesario
                                    // Ejemplo: if(typeof users === "function") users();
                                }
                            }
                        }
                    });
                }
            });
            return false;
        }
    });

    $("#mostrarformenviarimg").off('click').on('click', function() {
        $("#formnormal").hide();
        $("#formenviaimg").show(200);
    });
    $("#volverformnormal").off('click').on('click', function() {
        $("#formenviaimg").hide();
        $("#formnormal").show(200);
    });

    $('#formEnviarArchivo').off('submit').on('submit', async function(e) {
        e.preventDefault();
        // ... (tu código de envío de imagen)
        // Asegúrate de que 'enviandoImagen' esté declarado si lo usas aquí
        var enviandoImagen = false; // O muévelo a un scope más amplio si es necesario
        if (enviandoImagen) return;
        enviandoImagen = true; 
        const formElement = this;
        const formData = new FormData(formElement);
        const namearchivo = $("#uploadFile", formElement).val();
        if (!namearchivo) {
          alert("Debes seleccionar una imagen");
          enviandoImagen = false;
          return;
        }
        try {
          const response = await fetch('acciones/archivo.php', { method: 'POST', body: formData });
          if (!response.ok) throw new Error('Error en la subida');
          if ($(".audio").length > 0) $(".audio")[0].play(); 
          $("#formenviaimg").hide(); $("#formnormal").show(200);
          $("#conversation").load('MsjsUsers.php?id=' + idConectadoGlobal + '&clickUser=' + idUsuarioChatActualGlobal, scrollConversationToEnd);
          formElement.reset();
        } catch (error) { console.error('Error al enviar imagen:', error); alert('Error al enviar la imagen.');
        } finally { enviandoImagen = false; }
    });
    
    $('#videoCallBtn').off('click').on('click', function() {
        // ... (tu código de videollamada)
        const user1_id = idConectadoGlobal; 
        const user2_id = idUsuarioChatActualGlobal; 
        
        if (isNaN(user1_id) || isNaN(user2_id) || user1_id === 0 || user2_id === 0) {
            alert("Error: IDs de usuario no válidos para la videollamada.");
            return;
        }
        let roomNameBase = "poiChatVideo_";
        if (user1_id < user2_id) {
            roomNameBase += user1_id + "_" + user2_id;
        } else {
            roomNameBase += user2_id + "_" + user1_id;
        }
        const roomName = roomNameBase; 
        const domain = 'meet.jit.si';
        const jitsiUrl = `https://${domain}/${encodeURIComponent(roomName)}#config.prejoinPageEnabled=false`;
        window.open(jitsiUrl, '_blank'); 
        var mensajeInvitacion = "Te he invitado a una videollamada. Haz clic para unirte: " + jitsiUrl;
        var datosMensajeInvitacion = {
            user_id: idConectadoGlobal,
            to_id: idUsuarioChatActualGlobal,
            user: "<?php echo htmlspecialchars($_SESSION['email_user']); ?>",
            to_user: $(".heading-name-meta").text().trim() || "Usuario", 
            message: mensajeInvitacion,
            tipo_chat: "privado" 
        };
        $.ajax({
            type: "POST",
            url: "acciones/RegistMsj.php",
            data: datosMensajeInvitacion,
            success: function() {
                $("#conversation").load('MsjsUsers.php?id=' + idConectadoGlobal + '&clickUser=' + idUsuarioChatActualGlobal, function() {
                    scrollConversationToEnd(); 
                });
            },
            error: function() {
                alert("No se pudo enviar la invitación por chat.");
            }
        });
    });

    setTimeout(scrollConversationToEnd, 250); // Un poco más de delay por si acaso

}); // Fin de $(function() {})
</script>
</body>
</html>