<?php
session_start();
include('../config/config.php'); //
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Error desconocido.', 'tareas' => []];

if (!isset($_SESSION['id'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}
$id_usuario_actual = $_SESSION['id'];
$id_grupo = isset($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : 0;

if (empty($id_grupo)) {
    $response['message'] = 'ID de grupo no proporcionado.';
    echo json_encode($response);
    exit;
}

// Consultar tareas del grupo y el estado de completado para el usuario actual
// Mostraremos tareas no completadas_general O completadas_general pero creadas recientemente (ej. últimos 7 días)
// O simplemente todas las no completadas_general y un número limitado de las completadas
$query_tareas = "
    SELECT 
        tg.id_tarea, 
        tg.titulo_tarea, 
        tg.descripcion_tarea, 
        tg.fecha_creacion_tarea, 
        tg.completada_general,
        COALESCE(tms.completada_usuario, 0) as completada_por_usuario_actual
    FROM tareas_grupo tg
    LEFT JOIN tarea_miembros_estado tms ON tg.id_tarea = tms.id_tarea AND tms.id_usuario = ?
    WHERE tg.id_grupo = ?
    ORDER BY tg.completada_general ASC, tg.fecha_creacion_tarea DESC
";
// Podrías añadir un LIMIT aquí si la lista se vuelve muy larga

$stmt = mysqli_prepare($con, $query_tareas);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $id_usuario_actual, $id_grupo);
    mysqli_stmt_execute($stmt);
    $result_tareas = mysqli_stmt_get_result($stmt);
    $tareas_array = [];
    while ($fila = mysqli_fetch_assoc($result_tareas)) {
        $fila['completada_por_usuario_actual'] = (bool)$fila['completada_por_usuario_actual'];
        $fila['completada_general'] = (bool)$fila['completada_general'];
        $tareas_array[] = $fila;
    }
    mysqli_stmt_close($stmt);
    $response['status'] = 'success';
    $response['tareas'] = $tareas_array;
    $response['message'] = 'Tareas obtenidas.';
} else {
    $response['message'] = 'Error al preparar la consulta de tareas: ' . mysqli_error($con);
}

echo json_encode($response);
?>