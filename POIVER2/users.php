<?php
session_start();
include('config/config.php'); //
if (isset($_SESSION['email_user']) != "") {
  $idConectado        = $_SESSION['id']; //
  $email_user         = $_SESSION['email_user']; //
  $NombreUsarioSesion = $_SESSION['nombre_apellido']; //
  $imgPerfil          = $_SESSION['imagen']; //

?>
  <div class="status-bar"></div>
  <div class="row heading">
    <div class="col-sm-8 col-xs-8 heading-avatar">
      <div class="heading-avatar-icon">
        <img src="<?php echo 'imagenesperfil/' . $imgPerfil; ?>">  
        <strong style="padding: 0px 0px 0px 20px;">
          <?php echo $NombreUsarioSesion; ?> 
        </strong>
      </div>
    </div>
    
    <div class="col-sm-1 col-xs-1 heading-compose pull-right">
      <a href="acciones/salir.php?id=<?php echo $idConectado; ?>"> 
        <i class="zmdi zmdi-power" style="font-size: 25px;"></i>
      </a>
    </div>
    <div class="col-sm-1 col-xs-1 pull-right icohome">
      <a href="home.php"> 
        <i class="zmdi zmdi-refresh zmdi-hc-2x"></i>
      </a>
    </div>
    
    <div class="col-sm-1 col-xs-1 pull-right" id="showCreateGroupModal" title="Crear nuevo grupo">
        <i class="zmdi zmdi-accounts-add" style="font-size: 25px; color: white; cursor:pointer; padding-top:5px;"></i>
    </div>
  </div>

  <div class="row searchBox">
    <div class="col-sm-12 searchBox-inner">
      <div class="form-group has-feedback">
        <input id="searchText" type="search" class="form-control" name="searchText" placeholder="Buscar usuarios o grupos"> 
        <span class="glyphicon glyphicon-search form-control-feedback"></span> 
      </div>
    </div>
  </div>

  <div class="row sideBar"> 
    <?php
    // PRIMERO: Listar Grupos
    $QueryGrupos = ("SELECT g.id_grupo, g.nombre_grupo, g.imagen_grupo,
                        (SELECT GROUP_CONCAT(u.imagen ORDER BY mg_inner.id_relacion_miembro ASC SEPARATOR ',') 
                         FROM miembros_grupo mg_inner 
                         JOIN users u ON mg_inner.id_usuario = u.id 
                         WHERE mg_inner.id_grupo = g.id_grupo LIMIT 4) as imagenes_miembros
                      FROM grupos g
                      INNER JOIN miembros_grupo mg ON g.id_grupo = mg.id_grupo
                      WHERE mg.id_usuario = '$idConectado'
                      ORDER BY g.nombre_grupo ASC"); //
    $resultadoQueryGrupos = mysqli_query($con, $QueryGrupos); //

    if ($resultadoQueryGrupos && mysqli_num_rows($resultadoQueryGrupos) > 0) {
        while ($FilaGrupos = mysqli_fetch_array($resultadoQueryGrupos)) { //
            $id_grupo_actual = $FilaGrupos['id_grupo']; //
            
            $res_grupo_msj = ("SELECT COUNT(*) as numero_filas FROM msjs 
                               WHERE id_grupo_receptor = '$id_grupo_actual' 
                               AND user_id != '$idConectado' 
                               AND leido = 'NO' AND tipo_chat='grupal'"); // Añadido tipo_chat='grupal'
            $re_grupo = mysqli_query($con, $res_grupo_msj); //
            $data_grupo = mysqli_fetch_assoc($re_grupo); //
            $no_leidos_grupo = $data_grupo ? $data_grupo['numero_filas'] : 0; //

            $imagenes_miembros_array = [];
            if (!empty($FilaGrupos['imagenes_miembros'])) { //
                $imagenes_miembros_array = explode(',', $FilaGrupos['imagenes_miembros']); //
            }
    ?>
      <div class="row sideBar-body" data-id="<?php echo $FilaGrupos['id_grupo']; ?>" data-type="group"> 
        <div class="col-sm-3 col-xs-3 sideBar-avatar">
          <div class="avatar-icon group-avatar-stacked">
            <?php
            if (!empty($imagenes_miembros_array)) { //
                $count = 0;
                foreach ($imagenes_miembros_array as $img_miembro) { //
                    if ($count < 4) { //
                        echo '<img src="imagenesperfil/' . htmlspecialchars($img_miembro) . '" class="stacked-avatar" style="border:1px solid #fff;">'; //
                        $count++; //
                    }
                }
            } else { 
                echo '<img src="group_images/' . ($FilaGrupos['imagen_grupo'] ? htmlspecialchars($FilaGrupos['imagen_grupo']) : 'default_group_icon.png') . '" style="border:2px solid #00796B; width: 45px; height: 45px; border-radius: 50%; object-fit: cover;">'; //
            }
            ?>
          </div>
        </div>
        <div class="col-sm-9 col-xs-9 sideBar-main">
          <div class="row">
            <div class="col-sm-8 col-xs-8 sideBar-name">
              <span class="name-meta">
                <i class="zmdi zmdi-accounts" style="margin-right: 5px;"></i><?php echo htmlspecialchars($FilaGrupos['nombre_grupo']); ?> 
              </span>
            </div>
            <div class="col-sm-4 col-xs-4 pull-right sideBar-time" style="color:#93918f;">
              <?php if ($no_leidos_grupo > 0) : ?>
                <span class="notification-counter">
                  <?php echo $no_leidos_grupo; ?> 
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php
        } 
    } 

    // SEGUNDO: Listar Usuarios Individuales
    $QueryUsers = ("SELECT 
        id,imagen,
        email_user,
        nombre_apellido,
        fecha_session,
        estatus 
      FROM users WHERE id !='$idConectado' ORDER BY nombre_apellido ASC "); 
    $resultadoQueryUsers = mysqli_query($con, $QueryUsers); 

    while ($FilaUsers = mysqli_fetch_array($resultadoQueryUsers)) { 
      $id_persona     = $FilaUsers['id']; 

      $resultado_privado = ("SELECT COUNT(*) as numero_filas FROM msjs WHERE user_id='$id_persona' AND to_id='$idConectado' AND leido='NO' AND tipo_chat='privado'"); 
      $re_privado  = mysqli_query($con, $resultado_privado); 
      $data_privado = mysqli_fetch_assoc($re_privado); 
      $no_leidos_privado = $data_privado['numero_filas']; 

      if ($no_leidos_privado > 0) { 
    ?>
        <div style="display:none;">
          <audio controls autoplay>
            <source src="effect.mp3" type="audio/mp3"> 
          </audio>
        </div>
    <?php
      }
    ?>
      <div class="row sideBar-body" data-id="<?php echo $FilaUsers['id']; ?>" data-type="user"> 
        <div class="col-sm-3 col-xs-3 sideBar-avatar">
          <?php
          if ($FilaUsers['estatus'] != 'Inactiva') { ?> 
            <div class="avatar-icon">
              <img src="<?php echo 'imagenesperfil/' . $FilaUsers['imagen']; ?>" class="notification-container" style="border:3px solid #28a745 !important;"> 
            </div>
          <?php } else { ?>
            <div class="avatar-icon">
              <img src="<?php echo 'imagenesperfil/' . $FilaUsers['imagen']; ?>" class="notification-container" style="border:3px solid #696969 !important;"> 
            </div>
          <?php } ?>
        </div>
        <div class="col-sm-9 col-xs-9 sideBar-main">
          <div class="row">
            <div class="col-sm-8 col-xs-8 sideBar-name">
              <span class="name-meta">
                <?php echo $FilaUsers['nombre_apellido']; ?> 
              </span>
            </div>
            <div class="col-sm-4 col-xs-4 pull-right sideBar-time" style="color:#93918f;">
              <span class="notification-counter">
                <?php echo $no_leidos_privado; ?> 
              </span>
            </div>
          </div>
        </div>
      </div>
    <?php } // Fin del bucle de usuarios
    ?>
  </div> 

  
  <script type="text/javascript" src="assets/js/jquery-3.1.1.min.js"></script> 
  <script type="text/javascript">
    $(function() {
      // Click en un usuario o grupo
      // Asegúrate que este selector sea lo suficientemente específico si .sideBar-body se usa en otros contextos.
      // Si users.php solo genera esta lista, está bien.
      $("#myusers").off('click', '.sideBar-body').on('click', ".sideBar-body", function() { // Delegación desde #myusers
        $(".sideBar-body").removeClass("seleccionado"); 
        $(this).addClass("seleccionado"); 

        var id = $(this).data('id'); 
        var type = $(this).data('type'); 
        var idConectado = "<?php echo $idConectado; ?>"; 
        var email_user = "<?php echo $email_user; ?>"; 
        
        var dataString = 'id=' + id + '&idConectado=' + idConectado + '&email_user=' + email_user + '&type=' + type;
        var ruta = (type === 'user') ? "UserSeleccionado.php" : "GrupoSeleccionado.php"; 

        $('#capausermsj').html('<img src="assets/img/cargando.gif" class="ImgCargando"/>'); 
        $.ajax({
          url: ruta, 
          type: "POST", 
          data: dataString, 
          success: function(data) {
            $("#capausermsj").html(data); 
            // El scroll al final se maneja dentro de UserSeleccionado.php/GrupoSeleccionado.php
          },
          error: function(xhr, status, error) {
            console.error("Error al cargar el chat: ", status, error);
            $('#capausermsj').html("<p>Error al cargar la conversación. Por favor, inténtelo de nuevo.</p>");
          }
        });
        return false; 
      });

      // No es necesario el código para .heading-compose y .newMessage-back aquí
      // si este archivo (users.php) solo genera el contenido de la barra lateral.
      // Esa lógica debería estar en home.php si .side-two es parte de la estructura de home.php.
      // Si .side-two es cargado por users.php, entonces sí puede quedarse aquí.
      // Por tu estructura actual, users.php carga toda la cabecera, así que puede quedarse.
      $(".heading-compose").click(function() { 
        $(".side-two").css({ 
          "left": "0" 
        });
      });
      $(".newMessage-back").click(function() { 
        $(".side-two").css({ 
          "left": "-100%" 
        });
      });

    });
  </script>
<?php
} // Fin del if (isset($_SESSION['email_user']))
?>