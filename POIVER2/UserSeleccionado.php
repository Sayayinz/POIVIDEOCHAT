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

    /**style para el boton examinar***/
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
  </style>
</head>

<body>

  <?php
  sleep(1);
  header("Content-Type: text/html;charset=utf-8");

  include('config/config.php');
  $IdUser                 = $_REQUEST['id'];
  $idConectado            = $_REQUEST['idConectado'];
  $email_user             = $_REQUEST['email_user'];

  //Actualizando los mensajes no leidos ya que estoy entrando en mis mensajes
  if (!empty($IdUser)) {
    $leyendoMsj = ("UPDATE msjs SET leido = 'SI' WHERE  user_id='$IdUser' AND to_id='$idConectado' ");
    $queryLeerMsjs = mysqli_query($con, $leyendoMsj);
  }

  $QueryUserSeleccionado = ("SELECT * FROM users WHERE id='$IdUser' LIMIT 1 ");
  $QuerySeleccionado     = mysqli_query($con, $QueryUserSeleccionado);

  while ($rowUser = mysqli_fetch_array($QuerySeleccionado)) {
?>
  <div class="status-bar"> </div>
  <div class="row heading">
    <div class="col-sm-2 col-xs-2 heading-avatar">
      <a href="./" style="color: #fff;">
        <div class="heading-avatar-icon">
          <i class="zmdi zmdi-arrow-left" style="font-size:20px; vertical-align: middle; margin-right: 5px;"></i>
          <img src="<?php echo 'imagenesperfil/' . $rowUser['imagen']; ?>" style="vertical-align: middle;">
        </div>
      </a>
    </div>
    <div class="col-sm-7 col-xs-7 heading-name" style="padding-left: 0px;"> 
      <a class="heading-name-meta" style="padding-left:0px;">
        <?php echo $rowUser['nombre_apellido']; ?>
      </a>
      <div style="margin: 0px 0;">
        <label style="font-size: 0.8em; color: #f0f0f0;">Progreso de actividad:
        <progress id="chatProgress" value="0" max="100" style="width: 100%; height: 10px;"></progress></label>
      </div>
    </div>
    <div class="col-sm-3 col-xs-3 heading-icons text-right" style="padding-top: 5px;"> 
      <button id="videoCallBtn" class="btn btn-sm" title="Iniciar videollamada" style="color: white; background: none; border: none; font-size: 28px; padding: 0px 10px; vertical-align: middle;">
          <i class="zmdi zmdi-videocam"></i>
      </button>
    
    </div>
  </div>



    <div class="row message" id="conversation">
      <?php
      $QueryUserClick = ("SELECT UserIdSession FROM clickuser WHERE UserIdSession='$idConectado' LIMIT 1");
      $QueryClick     = mysqli_query($con, $QueryUserClick);
      $veririficaClick = mysqli_num_rows($QueryClick);
      if ($veririficaClick == 0) {
        $InserClick = ("INSERT INTO clickuser (UserIdSession,clickUser) VALUES ('$idConectado','$IdUser')");
        $ResulInsertClick = mysqli_query($con, $InserClick);
      } else {
        $UpdateClick = ("UPDATE clickuser SET clickUser='$IdUser' WHERE UserIdSession='$idConectado'");
        $ResultUpdateClick = mysqli_query($con, $UpdateClick);
      }


      //Mostrando msjs deacuerdo al Usuario seleccionado
      $Msjs = ("SELECT * FROM msjs WHERE (user_id ='" . $idConectado . "' AND to_id='" . $IdUser . "') OR (user_id='" . $IdUser . "' AND to_id='" . $idConectado . "') ORDER BY id ASC");
      $QueryMsjs = mysqli_query($con, $Msjs);

      while ($UserMsjs = mysqli_fetch_array($QueryMsjs)) {
        $archivo = $UserMsjs['archivos'];
        $explode = explode('.', $archivo);
        $extension_arch = array_pop($explode);

        if ($idConectado == $UserMsjs['user_id']) { ?>
          <div class="row message-body">






            <div class="col-sm-12 message-main-sender">
              <div class="sender">
                <div class="message-text">
                  <?php
                  if (!empty($UserMsjs['message'])) {
                    echo $UserMsjs['message'];
                  } else { ?>
                    <img src="<?php echo 'archivos/' . $archivo; ?>" style="width: 100%; max-width: 250px;">
                    <div class="row">
                      <div class="col-md-12">
                        <a class="boton_desc" download="" href="archivos/<?php echo $archivo; ?>" title="Descargar Imagen">Descargar
                        </a>
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
        <?php } else { ?>
          <div class="row message-body">
            <div class="col-sm-12 message-main-receiver">
              <div class="receiver">
                <div class="message-text">
                  <?php
                  if ($UserMsjs['message'] != "") {
                    echo $UserMsjs['message'];
                  } else { ?>
                    <img src="<?php echo './archivos/' . $UserMsjs['archivos']; ?>" style="width: 100%; max-width: 250px;">
                    <div class="row">
                      <div class="col-md-12">
                        <a class="boton_desc" download="" href="archivos/<?php echo $archivo; ?>" title="Descargar Imagen">Descargar
                        </a>
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

      <?php  }
      }
      ?>

    </div>



    <div class="row reply" id="formnormal">

    



      <form class="conversation-compose" id="formenviarmsj" name="formEnviaMsj">
        <input type="hidden" name="user_id" value="<?php echo $idConectado; ?>">
        <input type="hidden" name="to_id" value="<?php echo $rowUser['id']; ?>">
        <input type="hidden" name="user" value="<?php echo $email_user; ?>">
        <input type="hidden" name="to_user" value="<?php echo $rowUser['nombre_apellido']; ?> ">

        <div class="emoji">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" id="smiley" x="3147" y="3209">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.153 11.603c.795 0 1.44-.88 1.44-1.962s-.645-1.96-1.44-1.96c-.795 0-1.44.88-1.44 1.96s.645 1.965 1.44 1.965zM5.95 12.965c-.027-.307-.132 5.218 6.062 5.55 6.066-.25 6.066-5.55 6.066-5.55-6.078 1.416-12.13 0-12.13 0zm11.362 1.108s-.67 1.96-5.05 1.96c-3.506 0-5.39-1.165-5.608-1.96 0 0 5.912 1.055 10.658 0zM11.804 1.01C5.61 1.01.978 6.034.978 12.23s4.826 10.76 11.02 10.76S23.02 18.424 23.02 12.23c0-6.197-5.02-11.22-11.216-11.22zM12 21.355c-5.273 0-9.38-3.886-9.38-9.16 0-5.272 3.94-9.547 9.214-9.547a9.548 9.548 0 0 1 9.548 9.548c0 5.272-4.11 9.16-9.382 9.16zm3.108-9.75c.795 0 1.44-.88 1.44-1.963s-.645-1.96-1.44-1.96c-.795 0-1.44.878-1.44 1.96s.645 1.963 1.44 1.963z" fill="#7d8489" />
          </svg>
        </div>
        
        <input class="input-msg" name="message" id="message" placeholder="Escribir tu Mensaje y presiona Enter..." autocomplete="off" autofocus="autofocus" required="true">
        <i class="zmdi zmdi-comment-image" style="font-size: 45px; color: grey;" title="Enviar Imagen." id="mostrarformenviarimg"></i>
        
     
      </form>
    </div>


    <!---audio para cuando se envia un msj-->
    <audio class="audio" style="display:none;">
      <source src="effect.mp3" type="audio/mp3">
    </audio>
    <!---fin del audio--->


    <!---- form enviar img--->
    <div class="row reply" id="formenviaimg">
      <form class="conversation-compose" id="formenviarmsj" name="formEnviaMsj" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo $idConectado; ?>">
        <input type="hidden" name="to_id" value="<?php echo $rowUser['id']; ?>">
        <input type="hidden" name="to_user" value="<?php echo $rowUser['nombre_apellido']; ?> ">
        <input type="hidden" name="user" value="<?php echo $email_user; ?>">


        <div class="col-sm-12 col-xs-12 reply-recording">
          <label for="uploadFile" id="uploadIcon">
            <i class="zmdi zmdi-camera camara"></i>
          </label>
          <input type="file" name="namearchivo" value="upload" id="uploadFile" class="uploadFile" required />
       
        </div>
        <button class="send" name="enviar" id="botonenviarimg" name="botonenviarimg">
          <div class="circle">
            <i class="zmdi zmdi-mail-send" title="Enviar Imagen..."></i>
          </div>
        </button>
        <i class="zmdi zmdi-mail-reply" style="font-size: 50px;color: grey;" id="volverformnormal" title="Volver . ."></i>
      </form>
    </div>








    
  <?php } ?>

 <script type="text/javascript">
    // Usar un nombre de variable que sea menos probable que colisione globalmente, 
    // y lo inicializamos dentro del scope de esta carga de chat.
    var currentChatProgresoView; // Renombrada para evitar cualquier posible colisión futura

    $(function() { // Esto se ejecuta cuando el HTML de UserSeleccionado.php está listo en el DOM
      currentChatProgresoView = 0; // Inicializar/resetear el progreso para esta vista de chat
      // Intentar obtener el valor de progreso si ya existe para este chat (ej. desde un data-attribute o localStorage)
      // Por ahora, lo reiniciamos siempre al cargar.
      if ($('#chatProgress').length > 0) {
          $('#chatProgress').val(currentChatProgresoView); // Actualizar la barra de progreso visual
      }


      function scrollConversationToEnd() {
        var conversation = $('#conversation');
        if (conversation.length > 0) {
          conversation.animate({ scrollTop: conversation[0].scrollHeight }, 500); // Reducido el tiempo para ser más rápido
        }
      }
      scrollConversationToEnd(); // Llamar al cargar

      var idConectado = "<?php echo $idConectado; ?>";
      var idUsuarioChatActual = $("input[name='to_id']", "#formenviarmsj").val(); // Obtener el ID del usuario con el que se chatea

      // Función para buscar mensajes nuevos (tu lógica existente adaptada)
      var mensajesInterval;
      function iniciarActualizacionMensajes() {
        clearInterval(mensajesInterval); // Limpiar intervalo anterior
        mensajesInterval = setInterval(function() {
          // Solo busca si la ventana está activa/visible (opcional, para optimizar)
          // if (document.hasFocus()) { 
            $.ajax({
                type: "POST",
                url: "buscarMensajesNuevos.php", // Este script determina el 'clickUser' desde la BD basado en 'idConectado'
                dataType: "json",
                data: { idConectado: idConectado },
                success: function(data) {
                    if (data.msj == true) { // Si buscarMensajesNuevos indica que hay msjs nuevos para el usuario actual EN el chat activo
                        $("#conversation").load('MsjsUsers.php?id=' + idConectado + '&clickUser=' + idUsuarioChatActual, function() {
                           scrollConversationToEnd();
                        });
                    }
                }
            });
          // }
        }, 8000); // Llama cada 8 segundos
      }
      iniciarActualizacionMensajes();


      // Manejo del envío de mensajes al presionar Enter
      $("#formenviarmsj .input-msg").keypress(function(e) {
        if (e.which == 13 && $(this).val().trim() !== '') {
          e.preventDefault(); 

          var form = $("#formenviarmsj");
          var url = "acciones/RegistMsj.php";
          $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function(data) {
              if ($('#chatProgress').length > 0) {
                  currentChatProgresoView += 5; 
                  if (currentChatProgresoView >= 100) {
                    alert("¡Felicidades! Has ganado una mejora de emojis 🎉😎✨");
                    $('#conversation').css({
                      'background': 'linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet)',
                      'background-size': '200% 200%',
                      'animation': 'rainbowBackground 5s ease infinite'
                    });
                    setTimeout(function(){
                      $('#conversation').css({
                        'background': 'url(\'assets/img/fondochat.jpg\')', // Ruta desde la raíz
                        'animation': 'none'
                      });
                    }, 10000);
                    currentChatProgresoView = 0; 
                  }
                  $('#chatProgress').val(currentChatProgresoView);
              }

              $("#conversation").load('MsjsUsers.php?id=' + idConectado + '&clickUser=' + idUsuarioChatActual, function() {
                scrollConversationToEnd();
              }); 
              $("#formenviarmsj .input-msg").val("");
              if ($(".audio").length > 0) $(".audio")[0].play();
            }
          });
          return false;
        }
      });

      // Lógica para mostrar/ocultar forms de enviar imagen
      $("#formenviaimg").hide();
      $("#mostrarformenviarimg").click(function() {
        $("#formnormal").hide();
        $("#formenviaimg").show(200);
      });
      $("#volverformnormal").click(function() {
        $("#formenviaimg").hide();
        $("#formnormal").show(200);
      });

      // Lógica para enviar imágenes (la tuya, adaptada para recargar mensajes)
      var enviandoImagen = false; 
      $('body').off('click', '#botonenviarimg').on('click', '#botonenviarimg', async function(e) {
        e.preventDefault();
        if (enviandoImagen) return;
        enviandoImagen = true; 

        const formElement = $(this).closest('form')[0];
        const formData = new FormData(formElement);
        // const idConectado = "<?php echo $idConectado; ?>"; // Ya está definido arriba
        // const idUsuarioChatActual = $("input[name='to_id']", formElement).val(); // Obtener to_id del form actual
        
        const namearchivo = $("#uploadFile", formElement).val();
        if (!namearchivo) {
          alert("Debes seleccionar una imagen");
          enviandoImagen = false;
          return;
        }

        try {
          const response = await fetch('acciones/archivo.php', {
            method: 'POST',
            body: formData,
          });
          if (!response.ok) throw new Error('Error en la solicitud de subida de archivo');
          
          // const data = await response.text(); // archivo.php no debería devolver HTML para toda la conversación
                                            // sino solo una confirmación o error.
                                            // La recarga de mensajes se hace después.

          if ($(".audio").length > 0) $(".audio")[0].play(); 

          $("#formenviaimg").hide();
          $("#formnormal").show(200);

          $("#conversation").load('MsjsUsers.php?id=' + idConectado + '&clickUser=' + idUsuarioChatActual, function() {
            scrollConversationToEnd();
          });
          
          formElement.reset();
        } catch (error) {
          console.error('Error al enviar imagen:', error);
          alert('Error al enviar la imagen.');
        } finally {
          enviandoImagen = false;
        }
      });

      // Script para Jitsi (ya lo tienes, asegúrate que #videoCallBtn exista y sea único)
      $('body').off('click', '#videoCallBtn').on('click', '#videoCallBtn', function() {
        const user1_id = parseInt("<?php echo $idConectado; ?>");
        const user2_id = parseInt(idUsuarioChatActual); // Ya tenemos el ID del otro usuario
        
        let roomNameBase = "poiChatVideo_";
        if (user1_id < user2_id) {
            roomNameBase += user1_id + "_" + user2_id;
        } else {
            roomNameBase += user2_id + "_" + user1_id;
        }
        const roomName = roomNameBase.replace(/\s+/g, "_").toLowerCase();
        const jitsiUrl = `https://meet.jit.si/${roomName}`;
        window.open(jitsiUrl, "_blank");
      });

    });
  </script>
</body>
</html>


  