<?php
include('../config/config.php'); //

date_default_timezone_set("America/Bogota"); //
$hora = date('h:i a', time() - 3600 * date('I')); //
$fecha = date("d/m/Y"); //
$FechaMsj = $fecha . " " . $hora; //
$nombre_equipo_user = gethostbyaddr($_SERVER['REMOTE_ADDR']); //

$de           = $_POST['user']; //
$UserId       = (int)$_POST['user_id']; // Castear a entero
$msj          = utf8_decode(strip_tags(trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)))); //
$tipo_chat    = isset($_POST['tipo_chat']) ? $_POST['tipo_chat'] : 'privado'; //
$leido        = "NO"; //

// Puntos a otorgar por mensaje
$puntos_por_mensaje = 5; // Puedes ajustar esto

if ($msj != '') {
    if ($tipo_chat === 'grupal' && isset($_POST['id_grupo_receptor'])) {
        $id_grupo_receptor = (int)$_POST['id_grupo_receptor']; // Castear a entero
        $NuevoMsj  = ("INSERT INTO msjs (user, user_id, id_grupo_receptor, message, fecha, nombre_equipo_user, leido, tipo_chat)
            VALUES ('$de', '$UserId', '$id_grupo_receptor', '$msj', '$FechaMsj', '$nombre_equipo_user', '$leido', 'grupal')");
    } elseif ($tipo_chat === 'privado' && isset($_POST['to_id'])) {
        $to_id        = (int)$_POST['to_id']; // Castear a entero
        $para         = $_POST['to_user']; //
        $NuevoMsj  = ("INSERT INTO msjs (user, user_id, to_user, to_id, message, fecha, nombre_equipo_user, leido, tipo_chat)
            VALUES ('$de', '$UserId', '$para', '$to_id', '$msj', '$FechaMsj', '$nombre_equipo_user', '$leido', 'privado')"); //
    } else {
        exit;
    }
    $reg = mysqli_query($con, $NuevoMsj); //

    // Si el mensaje se registró correctamente, actualizar puntos y nivel
    if ($reg) {
        // Obtener puntos y nivel actual del usuario
        $queryUserInfo = mysqli_prepare($con, "SELECT puntos_recompensa, nivel_recompensa FROM users WHERE id = ?");
        mysqli_stmt_bind_param($queryUserInfo, "i", $UserId);
        mysqli_stmt_execute($queryUserInfo);
        $resultUserInfo = mysqli_stmt_get_result($queryUserInfo);
        $userInfo = mysqli_fetch_assoc($resultUserInfo);
        mysqli_stmt_close($queryUserInfo);

        if ($userInfo) {
            $nuevos_puntos = $userInfo['puntos_recompensa'] + $puntos_por_mensaje;
            $nivel_actual = $userInfo['nivel_recompensa'];
            $nuevo_nivel = $nivel_actual;

            // Lógica simple para subir de nivel (puedes hacerla más compleja)
            // Nivel 1: 0-99 puntos
            // Nivel 2: 100-249 puntos
            // Nivel 3: 250-499 puntos
            // Nivel 4: 500-999 puntos
            // Nivel 5: 1000+ puntos
            if ($nuevos_puntos >= 1000) {
                $nuevo_nivel = 5;
            } elseif ($nuevos_puntos >= 500) {
                $nuevo_nivel = 4;
            } elseif ($nuevos_puntos >= 250) {
                $nuevo_nivel = 3;
            } elseif ($nuevos_puntos >= 100) {
                $nuevo_nivel = 2;
            } else {
                $nuevo_nivel = 1;
            }
            
            // Actualizar puntos y nivel en la base de datos
            $stmtUpdateUser = mysqli_prepare($con, "UPDATE users SET puntos_recompensa = ?, nivel_recompensa = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmtUpdateUser, "iii", $nuevos_puntos, $nuevo_nivel, $UserId);
            mysqli_stmt_execute($stmtUpdateUser);
            mysqli_stmt_close($stmtUpdateUser);

            // Si el nivel cambió, podrías querer devolver esta información
            // para actualizar la interfaz sin recargar, o manejarlo de otra forma.
            // if ($nuevo_nivel > $nivel_actual) {
            //    echo json_encode(['status' => 'success', 'new_level' => $nuevo_nivel]);
            //    exit;
            // }
        }
    }
}
?>