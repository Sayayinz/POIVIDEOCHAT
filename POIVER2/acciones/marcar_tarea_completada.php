<?php
session_start();
include('../config/config.php'); //
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['id'])) {
        $response['message'] = 'Usuario no autenticado.';
        echo json_encode($response);
        exit;
    }

    $id_usuario = $_SESSION['id'];
    $id_tarea = isset($_POST['id_tarea']) ? (int)$_POST['id_tarea'] : 0;
    $estado_completado = isset($_POST['completada']) ? (bool)$_POST['completada'] : false; // true o false

    if (empty($id_tarea)) {
        $response['message'] = 'ID de tarea no proporcionado.';
        echo json_encode($response);
        exit;
    }

    mysqli_begin_transaction($con);

    try {
        // 1. Actualizar el estado para el miembro específico
        $fecha_completado_sql = $estado_completado ? date("Y-m-d H:i:s") : null;
        $stmt_update_miembro = mysqli_prepare($con, "UPDATE tarea_miembros_estado SET completada_usuario = ?, fecha_completado_usuario = ? WHERE id_tarea = ? AND id_usuario = ?");
        mysqli_stmt_bind_param($stmt_update_miembro, "isii", $estado_completado, $fecha_completado_sql, $id_tarea, $id_usuario);
        mysqli_stmt_execute($stmt_update_miembro);
        $affected_rows = mysqli_stmt_affected_rows($stmt_update_miembro);
        mysqli_stmt_close($stmt_update_miembro);

        if ($affected_rows <= 0) {
             // Podría ser que el usuario no esté asignado a la tarea o ya tenía ese estado.
             // Para ser más robusto, podrías verificar si el registro existe.
             // O simplemente continuar y verificar el estado general.
        }

        // 2. Verificar si todos los miembros han completado la tarea
        // Obtener el id_grupo de la tarea
        $stmt_grupo_info = mysqli_prepare($con, "SELECT id_grupo FROM tareas_grupo WHERE id_tarea = ?");
        mysqli_stmt_bind_param($stmt_grupo_info, "i", $id_tarea);
        mysqli_stmt_execute($stmt_grupo_info);
        $result_grupo_info = mysqli_stmt_get_result($stmt_grupo_info);
        $grupo_info = mysqli_fetch_assoc($result_grupo_info);
        mysqli_stmt_close($stmt_grupo_info);

        if (!$grupo_info) {
            throw new Exception("Tarea no encontrada para verificar estado general.");
        }
        $id_grupo = $grupo_info['id_grupo'];

        // Contar total de miembros en el grupo para esta tarea
        $stmt_total_miembros = mysqli_prepare($con, "SELECT COUNT(*) as total FROM tarea_miembros_estado WHERE id_tarea = ?");
        mysqli_stmt_bind_param($stmt_total_miembros, "i", $id_tarea);
        mysqli_stmt_execute($stmt_total_miembros);
        $total_miembros_data = mysqli_stmt_get_result($stmt_total_miembros);
        $total_miembros = mysqli_fetch_assoc($total_miembros_data)['total'];
        mysqli_stmt_close($stmt_total_miembros);

        // Contar total de miembros que han completado la tarea
        $stmt_completados = mysqli_prepare($con, "SELECT COUNT(*) as completados FROM tarea_miembros_estado WHERE id_tarea = ? AND completada_usuario = TRUE");
        mysqli_stmt_bind_param($stmt_completados, "i", $id_tarea);
        mysqli_stmt_execute($stmt_completados);
        $completados_data = mysqli_stmt_get_result($stmt_completados);
        $miembros_completados = mysqli_fetch_assoc($completados_data)['completados'];
        mysqli_stmt_close($stmt_completados);
        
        $completada_general_estado = ($total_miembros > 0 && $miembros_completados == $total_miembros);

        // 3. Actualizar tareas_grupo.completada_general
        $stmt_update_general = mysqli_prepare($con, "UPDATE tareas_grupo SET completada_general = ? WHERE id_tarea = ?");
        mysqli_stmt_bind_param($stmt_update_general, "ii", $completada_general_estado, $id_tarea);
        mysqli_stmt_execute($stmt_update_general);
        mysqli_stmt_close($stmt_update_general);

        mysqli_commit($con);
        $response = [
            'status' => 'success', 
            'message' => 'Estado de la tarea actualizado.',
            'completada_general' => $completada_general_estado,
            'completada_por_usuario_actual' => $estado_completado
        ];

    } catch (Exception $e) {
        mysqli_rollback($con);
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>