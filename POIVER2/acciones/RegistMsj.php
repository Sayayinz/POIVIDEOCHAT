<?php
include('../config/config.php'); //

date_default_timezone_set("America/Bogota"); //
$hora = date('h:i a', time() - 3600 * date('I')); //
$fecha = date("d/m/Y"); //
$FechaMsj = $fecha . " " . $hora; //
$nombre_equipo_user = gethostbyaddr($_SERVER['REMOTE_ADDR']); //

$de           = $_POST['user']; //
$UserId       = $_POST['user_id']; //
$msj          = utf8_decode(strip_tags(trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING)))); //
$tipo_chat    = isset($_POST['tipo_chat']) ? $_POST['tipo_chat'] : 'privado'; // Nuevo, por defecto privado
$leido        = "NO"; //

if ($msj != '') {
    if ($tipo_chat === 'grupal' && isset($_POST['id_grupo_receptor'])) {
        $id_grupo_receptor = $_POST['id_grupo_receptor'];
        $NuevoMsj  = ("INSERT INTO msjs (user, user_id, id_grupo_receptor, message, fecha, nombre_equipo_user, leido, tipo_chat)
            VALUES ('$de', '$UserId', '$id_grupo_receptor', '$msj', '$FechaMsj', '$nombre_equipo_user', '$leido', 'grupal')");
    } elseif ($tipo_chat === 'privado' && isset($_POST['to_id'])) {
        $to_id        = $_POST['to_id']; //
        $para         = $_POST['to_user']; //
        $NuevoMsj  = ("INSERT INTO msjs (user, user_id, to_user, to_id, message, fecha, nombre_equipo_user, leido, tipo_chat)
            VALUES ('$de', '$UserId', '$para', '$to_id', '$msj', '$FechaMsj', '$nombre_equipo_user', '$leido', 'privado')"); //
    } else {
        // Manejar error o no hacer nada si faltan datos
        exit;
    }
    $reg = mysqli_query($con, $NuevoMsj); //
}
?>