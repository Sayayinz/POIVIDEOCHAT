<?php
session_start();
header("Content-Type: text/html;charset=utf-8");
include('config/config.php');
if (isset($_SESSION['email_user']) != "") {
  $email_user    = $_SESSION['email_user'];
  $imgPerfil    = $_SESSION['imagen'];
  $idConectado  = $_SESSION['id'];
?>

  <!DOCTYPE html>
  <html lang="es">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Chat - WhatApp,una sala para compartir mensajes, audios, imagenes, videos entre muchascosas mas.">
    <meta name="author" content="URIAN VIERA">
    <meta name="keyword" content="Web Developer Urian Viera">
    <title>Chat - WhatApp!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <link rel="shortcut icon" type="image/png" href="assets/img/favicon.png" />
    <link rel="stylesheet" src="https://fonts.googleapis.com/css?family=Roboto:400,700,300" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/inputEnviar.css">
    <style type="text/css" media="screen">
      .seleccionado {
        background-color: hsl(0, 0%, 90%);
      }
    </style>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.1.2/css/material-design-iconic-font.min.css">
  </head>

  <body>


    <div class="container app">
      <div class="row app-one">

        <!----Lista de usuarios------------>
        <div class="col-sm-4 side" id="myusers">
          
          <div class="side-one">

          </div>
        </div>
        <!--- grupos----->
<div id="createGroupModal" style="display:none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
  <div style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 5px;">
    
  <form id="formCrearGrupo">
      <div class="form-group">
        <label for="nombre_grupo">Nombre del Grupo:</label>
        <input type="text" class="form-control" id="nombre_grupo" name="nombre_grupo" required>
      </div>
      <div class="form-group">
        <label>Seleccionar Miembros:</label>
        <div id="listaUsuariosParaGrupo" style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
          
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Crear Grupo</button>
    </form>


  <span class="closeCreateGroup" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor:pointer;">&times;</span>
    <h2>Crear Nuevo Grupo</h2>
    
  </div>
</div>



        
        <!----contenedor del chat--->
        <div class="col-sm-8 conversation">
          <div id="capausermsj">

            <img src="assets/img/capa.png" id="capawelcome" />
          </div>
        </div>
        <!---fin--->

      </div>
    </div>


    <script type="text/javascript" src="assets/js/jquery-3.1.1.min.js"></script>
    <script src="http://netdna.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type="text/javascript">
  

  window.activeChatInterval = null;
   

  /**
   * Inicia un temporizador para actualizar el chat activo.
   * Detiene cualquier temporizador anterior.
   * @param {string} type - 'user' o 'group'
   * @param {number} id - El ID del usuario o del grupo
   * @param {number} connectedId - El ID del usuario conectado
   */
  function startChatUpdate(type, id, connectedId) {
    // 1. Detener cualquier temporizador de chat que esté corriendo
    if (window.activeChatInterval) {
      clearInterval(window.activeChatInterval);
      // console.log("Temporizador de chat anterior detenido.");
    }

    if (!type || !id || !connectedId) {
      // console.log("No se inicia nuevo temporizador: faltan datos.");
      return; // No iniciar un nuevo temporizador si faltan datos
    }

    let url, intervalTime;

    if (type === 'user') {
      url = `MsjsUsers.php?id=${connectedId}&clickUser=${id}`;
      intervalTime = 8000;
    } else if (type === 'group') {
      url = `MsjsGrupos.php?id_grupo=${id}&idConectado=${connectedId}`;
      intervalTime = 7000;
    } else {
      return; // Tipo no válido
    }

    // 2. Iniciar el nuevo temporizador
    // console.log(`Iniciando nuevo temporizador para ${type} ${id}`);
    window.activeChatInterval = setInterval(function() {
      // Opcional: una comprobación extra para no recargar si el panel de chat no está visible
      if ($('#conversation').is(':visible')) {
        // console.log("Refrescando chat:", url);
        $("#conversation").load(url, function(response, status, xhr) {
            if (status == "error") {
                console.error("Error al refrescar el chat: " + xhr.status + " " + xhr.statusText);
            }
        });
      }
    }, intervalTime);
  }
  // home.php




  
$(function() { // Este es el $(document).ready() principal

    load2(); // Carga inicial

    function load2() {

      
        // console.log("load2: Iniciando carga de users.php");
        window.setTimeout(function() {
            $.post('users.php', { fetch: 1, idConectado: "<?php echo $idConectado; ?>" }, function(data) {
                $('#myusers').html(data); // Aquí se inserta el contenido de users.php, incluyendo el botón #showCreateGroupModal
                // console.log("load2: users.php cargado en #myusers");
            });
        }, 500); // Un pequeño retraso, asegúrate que sea suficiente o considera otras estrategias si hay problemas de tiempo.
    }

    // Lógica de actualización periódica (tu código original, revisa la frecuencia)
    var intervalUsers;
    function startPeriodicUserUpdate(){
        // console.log("startPeriodicUserUpdate: iniciando intervalo");
        clearInterval(intervalUsers); // Limpia cualquier intervalo anterior
        intervalUsers = setInterval(function() {
            // console.log("Intervalo: Actualizando users.php");
            // Solo actualiza si el panel de usuarios está visible y no hay un modal activo que pueda interferir
            if ($('#myusers').is(':visible') && $('#createGroupModal:visible').length === 0) {
                users();
            }
        }, 15000); // Actualizar cada 15 segundos, por ejemplo.
    }

  function users() {
    var load_data = {
        'fetch': 1,
        'idConectado': "<?php echo $idConectado; ?>"
    };
    $.post('users.php', load_data, function(data) {
        // console.log("Respuesta de users.php al refrescar:", data); // Verifica esta salida en la consola
        if (data.trim() === "") {
            // console.error("users.php devolvió una respuesta vacía.");
            // $('#myusers').html("<p>Error al cargar la lista de contactos.</p>");
        } else {
            var currentScroll = $('#myusers .sideBar').scrollTop();
            try {
                $('#myusers').html(data); // Este es el punto crítico
                // Intenta seleccionar el primer chat si ninguno está seleccionado
                if ($('#myusers .sideBar-body.seleccionado').length === 0 && $('#myusers .sideBar-body').length > 0) {
                    // console.log("Ningún chat seleccionado, seleccionando el primero.");
                    // $('#myusers .sideBar-body').first().trigger('click'); // Esto podría causar un bucle si hay problemas. Comentar si es necesario.
                }
                $('#myusers .sideBar').scrollTop(currentScroll);
            } catch (e) {
                // console.error("Error al procesar el HTML de users.php:", e);
                // $('#myusers').html("<p>Error al mostrar la lista de contactos.</p>");
            }
        }
    }).fail(function(xhr, status, error) {
        // console.error("Fallo la petición POST a users.php:", status, error);
        // $('#myusers').html("<p>No se pudo cargar la lista de contactos. Error: " + status + "</p>");
    });
}

   
    setTimeout(startPeriodicUserUpdate, 2000); // Comienza las actualizaciones periódicas un poco después de la carga inicial

    // MANEJADORES DE EVENTOS PARA EL MODAL
    // 
    $('#myusers').on('click', '#showCreateGroupModal', function() {
      
        $('#createGroupModal').show();
      $.ajax({
    url: 'acciones/listar_usuarios_para_grupo.php',
    type: 'GET',
    // data: { idConectado: "<?php echo $idConectado; ?>" }, // Asegúrate si tu PHP necesita este dato
    success: function(data) {
        console.log("AJAX Success. Datos recibidos de listar_usuarios_para_grupo.php:");
        console.log(data); // <<--- IMPORTANTE: MIRA QUÉ HAY AQUÍ EN LA CONSOLA
        $('#listaUsuariosParaGrupo').html(data);
        if ($('#listaUsuariosParaGrupo').html().trim() === "") {
            console.warn("listaUsuariosParaGrupo está vacío después de .html(data), aunque data podría tener contenido.");
        }
    },
    error: function(jqXHR, textStatus, errorThrown) {
        console.error("Error AJAX al cargar lista de usuarios: ", textStatus, errorThrown);
        console.log("Respuesta del servidor (error):", jqXHR.responseText);
        $('#listaUsuariosParaGrupo').html('<p>Error al cargar la lista de usuarios. (Detalles: '+ textStatus + ' - ' + errorThrown +')</p>');
    }
    });

});

 
    $('.closeCreateGroup').on('click', function() {
        // console.log("Modal cerrado con X");
        $('#createGroupModal').hide();
    });

    $(window).on('click', function(event) {
        // Comprueba si el clic fue directamente sobre el fondo del modal
        if ($(event.target).is('#createGroupModal')) {
            // console.log("Modal cerrado por clic afuera");
            $('#createGroupModal').hide();
        }
    });





    
    // Envío del formulario de creación de grupo
    $('#formCrearGrupo').on('submit', function(e) {
        e.preventDefault();
        // console.log("Formulario #formCrearGrupo enviado");
        var formData = $(this).serialize();
        $.ajax({
            url: 'acciones/crear_grupo.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#createGroupModal').hide();
                    users(); // Actualiza la lista para mostrar el nuevo grupo
                } else {
                    alert('Error al crear grupo: ' + (response.message || 'Ocurrió un error desconocido.'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // console.error("Error AJAX creando grupo: ", textStatus, errorThrown, jqXHR.responseText);
                alert('Error de comunicación al crear el grupo. Verifique la consola para más detalles.');
            }
        });
    });

}); // Fin de $(function() {}) principal
    </script>
  </body>
</html>
<?php
} else {
  echo '<script type="text/javascript">
    alert("Debe Iniciar Sesion");
    window.location.assign("index.php");
    </script>';
}
?>