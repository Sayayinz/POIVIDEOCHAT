<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../config/config.php'); // [from previous user input]

if (!isset($_SESSION['id'])) {
    echo "<p>Error: Usuario no autenticado. Por favor, inicie sesión.</p>";
    exit;
}
$id_usuario_actual = $_SESSION['id'];

// Asegúrate que la conexión a la BD sea exitosa
if (!$con) {
    echo "<p>Error: No se pudo conectar a la base de datos.</p>";
    exit;
}

$QueryUsers = "SELECT id, nombre_apellido FROM users WHERE id != '" . mysqli_real_escape_string($con, $id_usuario_actual) . "' ORDER BY nombre_apellido ASC";
$resultadoQuery = mysqli_query($con, $QueryUsers);

if (!$resultadoQuery) {
    echo "<p>Error en la consulta SQL: " . mysqli_error($con) . "</p>";
    exit;
}

$output = '';
if (mysqli_num_rows($resultadoQuery) > 0) {
    while ($Fila = mysqli_fetch_array($resultadoQuery)) {
        // Importante: Asegúrate que los IDs de los checkboxes sean únicos si este modal se abre múltiples veces
        // o si hay otros checkboxes en la página con IDs similares.
        $userId = htmlspecialchars($Fila['id']);
        $userName = htmlspecialchars($Fila['nombre_apellido']);
        $output .= '<div><input type="checkbox" name="miembros[]" value="' . $userId . '" id="miembro_user_' . $userId . '"> <label for="miembro_user_' . $userId . '">' . $userName . '</label></div>';
    }
} else {
    $output = "<p>No hay otros usuarios disponibles para agregar al grupo.</p>";
}
echo $output; // Devuelve el HTML generado
?>