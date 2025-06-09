<?php
// GrupoSeleccionado.php
sleep(1);
header("Content-Type: text/html;charset=utf-8");
session_start();
include('config/config.php'); //

$id_grupo_seleccionado = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$idConectado           = isset($_REQUEST['idConectado']) ? (int)$_REQUEST['idConectado'] : (isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0);
$email_user            = isset($_REQUEST['email_user']) ? filter_var($_REQUEST['email_user'], FILTER_SANITIZE_EMAIL) : (isset($_SESSION['email_user']) ? $_SESSION['email_user'] : '');

if ($id_grupo_seleccionado == 0 || $idConectado == 0) {
    echo "<p>Error: Faltan datos para cargar el chat de grupo.</p>";
    exit;
}

$QueryGrupoInfo = "SELECT * FROM grupos WHERE id_grupo= ? LIMIT 1";
$stmtGrupoSel = mysqli_prepare($con, $QueryGrupoInfo);
mysqli_stmt_bind_param($stmtGrupoSel, "i", $id_grupo_seleccionado);
mysqli_stmt_execute($stmtGrupoSel);
$ResultadoGrupoInfo = mysqli_stmt_get_result($stmtGrupoSel);
$rowGrupo = mysqli_fetch_array($ResultadoGrupoInfo);
mysqli_stmt_close($stmtGrupoSel);

if ($rowGrupo) {
?>
  <div class="status-bar"> </div>
  <div class="row heading">
    <div class="col-sm-2 col-xs-2 heading-avatar">
      <a href="./" style="color: #fff;">
        <div class="heading-avatar-icon">
          <i class="zmdi zmdi-arrow-left" style="font-size:20px; vertical-align: middle; margin-right: 5px;"></i>
          <img src="<?php echo 'group_images/' . ($rowGrupo['imagen_grupo'] ? htmlspecialchars($rowGrupo['imagen_grupo']) : 'default_group_icon.png'); ?>" style="vertical-align: middle; border-radius:50%; width:40px; height:40px;">
        </div>
      </a>
    </div>
    <div class="col-sm-7 col-xs-7 heading-name" style="padding-left: 0px;">
      <a class="heading-name-meta" style="padding-left:0px;">
        <i class="zmdi zmdi-accounts" style="margin-right: 5px;"></i><?php echo htmlspecialchars($rowGrupo['nombre_grupo']); ?>
      </a>
      </div>
    <div class="col-sm-3 col-xs-3 heading-icons text-right" style="padding-top: 5px;">
        <button id="btnToggleTareasGrupo" title="Ver/Ocultar Tareas" class="btn btn-sm" style="color: white; background: none; border: none; font-size: 22px; padding: 0 5px; vertical-align: middle;">
            <i class="zmdi zmdi-assignment"></i>
        </button>
        <button id="btnCrearNuevaTarea" title="Crear Nueva Tarea" class="btn btn-sm" style="color: white; background: none; border: none; font-size: 22px; padding: 0 5px; vertical-align: middle;">
            <i class="zmdi zmdi-plus-circle-o"></i>
        </button>
    </div>
  </div>

  <div id="panelLateralTareas" style="display: none; position: absolute; right: 0; top: 60px; /* Ajusta según la altura de tu cabecera */ width: 280px; height: calc(100% - 120px); /* Ajusta para que no colisione con el input de mensaje */ background-color: #ffebcc; /* Un color naranja claro como base */ border-left: 1px solid #ccc; box-shadow: -2px 0 5px rgba(0,0,0,0.1); z-index: 100; padding: 10px; overflow-y: auto;">
    <h4>Tareas del Grupo</h4>
    <div id="listaTareasGrupo">
        <p>Cargando tareas...</p>
    </div>
    <div id="formularioCrearTarea" style="margin-top: 20px; border-top: 1px solid #ddd; padding-top:10px; display:none;">
        <h5>Crear Nueva Tarea</h5>
        <form id="formNuevaTarea">
            <input type="hidden" name="id_grupo_tarea" value="<?php echo $id_grupo_seleccionado; ?>">
            <div class="form-group">
                <label for="titulo_tarea_input">Título:</label>
                <input type="text" class="form-control input-sm" id="titulo_tarea_input" name="titulo_tarea" required>
            </div>
            <div class="form-group">
                <label for="descripcion_tarea_input">Descripción (opcional):</label>
                <textarea class="form-control input-sm" id="descripcion_tarea_input" name="descripcion_tarea" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Guardar Tarea</button>
            <button type="button" id="btnCancelarNuevaTarea" class="btn btn-default btn-sm">Cancelar</button>
        </form>
    </div>
  </div>


  <div class="row message" id="conversation">
    <?php
 $queryClickUser = "
        INSERT INTO clickuser (UserIdSession, clickUser, tipoClick) 
        VALUES (?, ?, 'group') 
        ON DUPLICATE KEY UPDATE 
        clickUser = VALUES(clickUser), 
        tipoClick = VALUES(tipoClick)
    ";
    
    $stmtClickUser = mysqli_prepare($con, $queryClickUser);
    if ($stmtClickUser) {
        // El tipo de 'clickUser' en tu tabla es VARCHAR(50).
        // $id_grupo_seleccionado es un ID numérico, pero se guardará como string en clickUser.
        $id_grupo_como_string = (string)$id_grupo_seleccionado; 
        $id_conectado_como_string = (string)$idConectado; // UserIdSession también es VARCHAR(50)

        mysqli_stmt_bind_param($stmtClickUser, "ss", $id_conectado_como_string, $id_grupo_como_string);
        
        if (!mysqli_stmt_execute($stmtClickUser)) {
            // Manejo de error si la ejecución falla
            // echo "Error al actualizar/insertar clickuser: " . mysqli_stmt_error($stmtClickUser);
        }
        mysqli_stmt_close($stmtClickUser);
    } else {
        // Manejo de error si la preparación de la consulta falla
        // echo "Error al preparar la consulta para clickuser: " . mysqli_error($con);
    }
    
    // Marcar mensajes del grupo como leídos (lógica simplificada: quitar el contador visual en users.php)
    // Para una lógica real de "leído hasta X mensaje", se necesitaría una tabla adicional.
    // Por ahora, asumimos que al abrir el grupo, el usuario ve los mensajes.
    // El script users.php ya tiene una lógica para contar no leídos que se podría adaptar o usar.

    // Mostrando mensajes del grupo
    $Msjs = ("SELECT m.*, u.nombre_apellido as nombre_remitente, u.imagen as imagen_remitente 
              FROM msjs m 
              JOIN users u ON m.user_id = u.id 
              WHERE m.id_grupo_receptor = ? AND m.tipo_chat = 'grupal'
              ORDER BY m.id ASC");
    $stmtMsjs = mysqli_prepare($con, $Msjs);
    mysqli_stmt_bind_param($stmtMsjs, "i", $id_grupo_seleccionado);
    mysqli_stmt_execute($stmtMsjs);
    $QueryMsjs = mysqli_stmt_get_result($stmtMsjs);

    while ($UserMsjs = mysqli_fetch_array($QueryMsjs)) {
        $esRemitenteConectado = ($idConectado == $UserMsjs['user_id']);
        $archivoMsj = $UserMsjs['archivos'];
    ?>
      <div class="row message-body">
        <?php if ($esRemitenteConectado) { ?>
          <div class="col-sm-12 message-main-sender">
            <div class="sender">
              <div class="message-text">
                <?php 
                if (!empty($UserMsjs['message'])) {
                    echo htmlspecialchars($UserMsjs['message']); 
                } else if (!empty($archivoMsj)) { ?>
                    <img src="<?php echo 'archivos/' . htmlspecialchars($archivoMsj); ?>" style="width: 100%; max-width: 250px; border-radius: 5px;">
                    <a class="boton_desc" download="<?php echo htmlspecialchars($archivoMsj); ?>" href="archivos/<?php echo htmlspecialchars($archivoMsj); ?>" title="Descargar Imagen" style="display: block; text-align: center; margin-top: 5px; color: #fff; background-color: rgba(0,0,0,0.5); padding: 3px; border-radius: 3px; font-size:0.8em;">Descargar</a>
                <?php } ?>
              </div>
              <span class="message-time pull-right">
                <?php echo htmlspecialchars($UserMsjs['fecha']);  ?>
              </span>
            </div>
          </div>
        <?php } else { ?>
          <div class="col-sm-12 message-main-receiver">
            <div class="receiver">
              <strong style="display:block; font-size:0.9em; color:#00796B;"><?php echo htmlspecialchars($UserMsjs['nombre_remitente']); ?>:</strong>
              <div class="message-text">
                <?php 
                if (!empty($UserMsjs['message'])) {
                    echo htmlspecialchars($UserMsjs['message']); 
                } else if (!empty($archivoMsj)) { ?>
                    <img src="<?php echo 'archivos/' . htmlspecialchars($archivoMsj); ?>" style="width: 100%; max-width: 250px; border-radius: 5px;">
                    <a class="boton_desc" download="<?php echo htmlspecialchars($archivoMsj); ?>" href="archivos/<?php echo htmlspecialchars($archivoMsj); ?>" title="Descargar Imagen" style="display: block; text-align: center; margin-top: 5px; color: #333; background-color: rgba(255,255,255,0.5); padding: 3px; border-radius: 3px; font-size:0.8em;">Descargar</a>
                <?php } ?>
              </div>
              <span class="message-time pull-right">
                <?php echo htmlspecialchars($UserMsjs['fecha']);  ?>
              </span>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php 
    } 
    mysqli_stmt_close($stmtMsjs);
    ?>
  </div>

  <div class="row reply" id="formnormalGrupo">
    <form class="conversation-compose" id="formenviarmsjGrupo" name="formEnviaMsjGrupo">
      <input type="hidden" name="user_id" value="<?php echo $idConectado; ?>">
      <input type="hidden" name="id_grupo_receptor" value="<?php echo $id_grupo_seleccionado; ?>">
      <input type="hidden" name="user" value="<?php echo htmlspecialchars($email_user); ?>">
      <input type="hidden" name="tipo_chat" value="grupal">

     <div class="emoji" id="btnCompartirUbicacion" title="Compartir ubicación" style="cursor: pointer;">
  <i class="zmdi zmdi-pin" style="font-size: 24px; color: #7d8489;"></i>
</div>
      <input class="input-msg" name="message" id="messageGrupo" placeholder="Escribir mensaje al grupo..." autocomplete="off" autofocus="autofocus" required="true">
      <i class="zmdi zmdi-attachment-alt" style="font-size: 30px; color: grey; padding: 8px; cursor:pointer;" title="Enviar Archivo al Grupo." id="mostrarFormEnviarArchivoGrupo"></i>
    </form>
  </div>
  
  <div class="row reply" id="formEnviarArchivoGrupo" style="display:none;">
      <form class="conversation-compose" id="formSubirArchivoGrupo" name="formSubirArchivoGrupo" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo $idConectado; ?>">
        <input type="hidden" name="id_grupo_receptor_archivo" value="<?php echo $id_grupo_seleccionado; ?>">
        <input type="hidden" name="user" value="<?php echo htmlspecialchars($email_user); ?>">
        <input type="hidden" name="tipo_chat" value="grupal_archivo">
        <div class="col-sm-10 col-xs-10 reply-main" style="padding: 5px 0px !important;">
          <input type="file" name="namearchivo_grupo" id="uploadFileGrupo" class="form-control input-sm" required style="height: auto; padding: 5px;" />
        </div>
        <div class="col-sm-1 col-xs-1 reply-send" style="padding:0px !important;">
            <button type="submit" class="send" id="btnEnviarArchivoGrupo" style="width: 100%; height: 100%; padding:0;">
              <div class="circle" style="width: 40px; height: 40px; margin: auto;">
                <i class="zmdi zmdi-mail-send" title="Enviar Archivo" style="font-size:20px;"></i>
              </div>
            </button>
        </div>
         <div class="col-sm-1 col-xs-1 reply-emojis" style="padding:0px !important;">
            <i class="zmdi zmdi-mail-reply" style="font-size: 30px;color: grey; padding:5px !important;" id="volverFormNormalDesdeArchivoGrupo" title="Volver"></i>
        </div>
      </form>
  </div>


  <audio class="audio" style="display:none;">
    <source src="effect.mp3" type="audio/mp3">
  </audio>

  <script type="text/javascript">
    $(function() {
        var idConectadoGlobalGrupo = parseInt("<?php echo $idConectado; ?>");
        var idGrupoSeleccionadoGlobal = parseInt("<?php echo $id_grupo_seleccionado; ?>");

        function scrollConversationGrupoToEnd() {
            var conversation = $('#conversation');
            if (conversation.length > 0 && conversation[0].scrollHeight) {
                conversation.scrollTop(conversation[0].scrollHeight);
            }
        }
        setTimeout(scrollConversationGrupoToEnd, 200);

       if (!isNaN(idGrupoSeleccionadoGlobal) && idGrupoSeleccionadoGlobal !== 0) {
        // Llama a la función global definida en home.php
        startChatUpdate('group', idGrupoSeleccionadoGlobal, idConectadoGlobalGrupo);
    }

        // Envío de mensajes de texto para grupos
        $("#formenviarmsjGrupo .input-msg").off('keypress').on('keypress', function(e) {
            var $inputMsg = $(this);
            if (e.which == 13 && $inputMsg.val().trim() !== '') {
                e.preventDefault();
                var formData = $("#formenviarmsjGrupo").serialize();
                $.ajax({
                    type: "POST",
                    url: "acciones/RegistMsj.php",
                    data: formData,
                    success: function(data) {
                        $("#conversation").load('MsjsGrupos.php?id_grupo=' + idGrupoSeleccionadoGlobal + '&idConectado=' + idConectadoGlobalGrupo, function() {
                           scrollConversationGrupoToEnd();
                        });
                        $inputMsg.val("");
                        if ($(".audio").length > 0) $(".audio")[0].play();
                        // Aquí no se maneja la barra de progreso de nivel individual como en UserSeleccionado
                    },
                    error: function() { console.error("Error al enviar mensaje de grupo."); }
                });
                return false;
            }
        });

        // Lógica para mostrar/ocultar forms de envío de archivo
        $("#mostrarFormEnviarArchivoGrupo").off('click').on('click', function() {
            $("#formnormalGrupo").hide();
            $("#formEnviarArchivoGrupo").show(200);
        });
        $("#volverFormNormalDesdeArchivoGrupo").off('click').on('click', function() {
            $("#formEnviarArchivoGrupo").hide();
            $("#formnormalGrupo").show(200);
            $('#uploadFileGrupo').val(''); // Limpiar el input file
        });

        // Envío de archivos en grupo
        $('#formSubirArchivoGrupo').off('submit').on('submit', async function(e) {
            e.preventDefault();
            var enviandoArchivoGrupo = $(this).data('enviando'); // Evitar envíos múltiples
            if (enviandoArchivoGrupo) return;
            $(this).data('enviando', true);

            const formElement = this;
            const formData = new FormData(formElement);
            const namearchivo = $("#uploadFileGrupo", formElement).val();

            if (!namearchivo) {
                alert("Debes seleccionar un archivo para enviar al grupo.");
                $(this).data('enviando', false);
                return;
            }
            try {
                const response = await fetch('acciones/archivo_grupo.php', { // NECESITARÁS CREAR acciones/archivo_grupo.php
                    method: 'POST',
                    body: formData
                }); 
                // No parsear como JSON a menos que archivo_grupo.php devuelva JSON
                // const result = await response.json(); 
                // if (!response.ok || result.status !== 'success') {
                //    throw new Error(result.message || 'Error en la subida del archivo al grupo.');
                // }
                if (!response.ok ) { // Chequeo básico
                   throw new Error('Error en la subida del archivo al grupo.');
                }


                if ($(".audio").length > 0) $(".audio")[0].play();
                $("#formEnviarArchivoGrupo").hide();
                $("#formnormalGrupo").show(200);
                $("#conversation").load('MsjsGrupos.php?id_grupo=' + idGrupoSeleccionadoGlobal + '&idConectado=' + idConectadoGlobalGrupo, scrollConversationGrupoToEnd);
                formElement.reset();
            } catch (error) {
                console.error('Error al enviar archivo al grupo:', error);
                alert('Error al enviar el archivo al grupo: ' + error.message);
            } finally {
                $(this).data('enviando', false);
            }
        });


        // ---- Lógica para TAREAS ----
        var panelTareasVisible = false;

        $("#btnToggleTareasGrupo").off('click').on('click', function() {
            panelTareasVisible = !panelTareasVisible;
            if (panelTareasVisible) {
                $("#panelLateralTareas").fadeIn(200);
                cargarTareasGrupo();
            } else {
                $("#panelLateralTareas").fadeOut(200);
                $("#formularioCrearTarea").hide(); // Ocultar form si estaba abierto
            }
        });

        $("#btnCrearNuevaTarea").off('click').on('click', function() {
            if (!panelTareasVisible) { // Si el panel no está visible, mostrarlo
                 $("#panelLateralTareas").fadeIn(200);
                 panelTareasVisible = true;
                 cargarTareasGrupo(); // Cargar tareas existentes
            }
            $("#formularioCrearTarea").slideToggle(200);
        });
        
        $("#btnCancelarNuevaTarea").off('click').on('click', function() {
            $("#formularioCrearTarea").slideUp(200);
            $('#formNuevaTarea')[0].reset();
        });


        function cargarTareasGrupo() {
            $("#listaTareasGrupo").html("<p>Cargando tareas...</p>");
            $.ajax({
                url: 'acciones/listar_tareas_grupo.php',
                type: 'GET',
                data: { id_grupo: idGrupoSeleccionadoGlobal },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.tareas) {
                        renderizarListaTareas(response.tareas);
                    } else {
                        $("#listaTareasGrupo").html("<p>No hay tareas o error al cargarlas: " + response.message + "</p>");
                    }
                },
                error: function() {
                    $("#listaTareasGrupo").html("<p>Error de conexión al cargar tareas.</p>");
                }
            });
        }

         function renderizarListaTareas(tareas) {
            var htmlTareas = '';
            if (tareas.length > 0) {
                tareas.forEach(function(tarea) {
                    let tareaClaseCompletadaGeneral = tarea.completada_general ? 'tarea-completada-general' : '';
                    // El icono de eliminar es parte del botón ahora
                    let checkedUsuario = tarea.completada_por_usuario_actual ? 'checked' : '';
                    let disabledCheckbox = tarea.completada_general ? 'disabled' : '';

                    htmlTareas += `
                        <div class="tarea-item ${tareaClaseCompletadaGeneral}" data-id-tarea="${tarea.id_tarea}">
                            <div class="tarea-item-header">
                                <span class="tarea-item-titulo">${escapeHtml(tarea.titulo_tarea)}</span>
                                <div class="tarea-item-controles">
                                    <input type="checkbox" class="checkbox-marcar-tarea" data-id-tarea="${tarea.id_tarea}" ${checkedUsuario} ${disabledCheckbox}>
                                    ${tarea.completada_general ? `<button class="btn-eliminar-tarea" data-id-tarea="${tarea.id_tarea}" title="Eliminar tarea"><i class="zmdi zmdi-delete"></i></button>` : ''}
                               
                                ${tarea.descripcion_tarea ? `<p class="tarea-item-descripcion">${escapeHtml(tarea.descripcion_tarea)}</p>` : ''}
                            <small class="tarea-item-fecha">Creada: ${new Date(tarea.fecha_creacion_tarea).toLocaleString()}</small>
                               
                                    </div>
                                    
                            </div>
                           
                        </div>
                    `;
                });
            } else {
                htmlTareas = "<p style='text-align:center; color:#777; margin-top:10px;'>No hay tareas asignadas a este grupo.</p>";
            }
            $("#listaTareasGrupo").html(htmlTareas);
        }
        
        function escapeHtml(unsafe) {
            // ... (función escapeHtml existente) ...
            if (unsafe === null || typeof unsafe === 'undefined') return '';
            return unsafe
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        $('#formNuevaTarea').off('submit').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: 'acciones/crear_tarea.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        $('#formNuevaTarea')[0].reset();
                        $("#formularioCrearTarea").slideUp(200);
                        cargarTareasGrupo(); // Recargar lista
                        // Notificar a otros miembros (ej. enviar un mensaje al chat)
                        enviarMensajeDeSistemaAlGrupo("Nueva tarea creada: " + $("#titulo_tarea_input").val());
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error de conexión al crear la tarea.');
                }
            });
        });

        // Marcar/Desmarcar tarea como completada por el usuario
        $("#listaTareasGrupo").off('change', '.checkbox-marcar-tarea').on('change', '.checkbox-marcar-tarea', function() {
            var idTarea = $(this).data('id-tarea');
            var completada = $(this).is(':checked');
            var checkboxElement = $(this);

            $.ajax({
                url: 'acciones/marcar_tarea_completada.php',
                type: 'POST',
                data: {
                    id_tarea: idTarea,
                    completada: completada ? 1 : 0 // Enviar 1 para true, 0 para false
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // console.log(response.message);
                        // Actualizar visualmente la tarea específica o recargar toda la lista
                        cargarTareasGrupo(); // Más simple por ahora
                        if (response.completada_general) {
                             enviarMensajeDeSistemaAlGrupo("¡Tarea '" + checkboxElement.closest('.tarea-item').find('strong').text() + "' completada por todos!");
                        }
                    } else {
                        alert('Error: ' + response.message);
                        checkboxElement.prop('checked', !completada); // Revertir si falla
                    }
                },
                error: function() {
                    alert('Error de conexión al marcar la tarea.');
                    checkboxElement.prop('checked', !completada); // Revertir si falla
                }
            });
        });
        
        // Eliminar tarea
        $("#listaTareasGrupo").off('click', '.btn-eliminar-tarea').on('click', '.btn-eliminar-tarea', function() {
            if (!confirm("¿Estás seguro de que quieres eliminar esta tarea completada?")) {
                return;
            }
            var idTarea = $(this).data('id-tarea');
            var tituloTarea = $(this).closest('.tarea-item').find('strong').text();

            $.ajax({
                url: 'acciones/eliminar_tarea.php',
                type: 'POST',
                data: { id_tarea: idTarea },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        cargarTareasGrupo(); // Recargar lista
                        enviarMensajeDeSistemaAlGrupo("Tarea eliminada: " + tituloTarea);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error de conexión al eliminar la tarea.');
                }
            });
        });

        function enviarMensajeDeSistemaAlGrupo(mensaje) {
            $.ajax({
                type: "POST",
                url: "acciones/RegistMsj.php", // Usamos el mismo script
                data: {
                    user_id: 0, // ID 0 para sistema o un ID de bot dedicado
                    user: "Sistema", // Nombre para el sistema
                    id_grupo_receptor: idGrupoSeleccionadoGlobal,
                    message: "[TAREAS] " + mensaje,
                    tipo_chat: "grupal"
                },
                success: function() {
                    $("#conversation").load('MsjsGrupos.php?id_grupo=' + idGrupoSeleccionadoGlobal + '&idConectado=' + idConectadoGlobalGrupo, function() {
                       scrollConversationGrupoToEnd();
                    });
                }
            });
        }


    }); // Fin de $(function() {})
  </script>
<?php
} else {
    echo "<p>Grupo no encontrado.</p>";
}
?>
</body>
</html>