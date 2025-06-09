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

    $id_usuario_actual = $_SESSION['id'];
    $id_tarea = isset($_POST['id_tarea']) ? (int)$_POST['id_tarea'] : 0;

    if (empty($id_tarea)) {
        $response['message'] = 'ID de tarea no proporcionado.';
        echo json_encode($response);
        exit;
    }

    // Opcional: Verificar permisos (ej. solo el creador o si la tarea está completada_general)
    $stmt_info_tarea = mysqli_prepare($con, "SELECT id_creador, completada_general, id_grupo FROM tareas_grupo WHERE id_tarea = ?");
    mysqli_stmt_bind_param($stmt_info_tarea, "i", $id_tarea);
    mysqli_stmt_execute($stmt_info_tarea);
    $result_info_tarea = mysqli_stmt_get_result($stmt_info_tarea);
    $info_tarea = mysqli_fetch_assoc($result_info_tarea);
    mysqli_stmt_close($stmt_info_tarea);

    if (!$info_tarea) {
        $response['message'] = 'Tarea no encontrada.';
        echo json_encode($response);
        exit;
    }

    // Lógica de permisos: por ejemplo, solo el creador puede borrar, o cualquiera si ya está completada.
    // Aquí, para simplificar, permitimos borrar si es el creador o si está completada.
    // Necesitarías también verificar si es admin del grupo si tuvieras roles de grupo.
    if ($info_tarea['id_creador'] != $id_usuario_actual && !$info_tarea['completada_general']) {
         $response['message'] = 'No tienes permiso para eliminar esta tarea o no está completada.';
         echo json_encode($response);
         exit;
    }

    mysqli_begin_transaction($con);
    try {
        // Eliminar primero de tarea_miembros_estado (CASCADE debería encargarse, pero por si acaso)
        $stmt_del_estados = mysqli_prepare($con, "DELETE FROM tarea_miembros_estado WHERE id_tarea = ?");
        mysqli_stmt_bind_param($stmt_del_estados, "i", $id_tarea);
        mysqli_stmt_execute($stmt_del_estados);
        mysqli_stmt_close($stmt_del_estados);

        // Eliminar de tareas_grupo
        $stmt_del_tarea = mysqli_prepare($con, "DELETE FROM tareas_grupo WHERE id_tarea = ?");
        mysqli_stmt_bind_param($stmt_del_tarea, "i", $id_tarea);
        mysqli_stmt_execute($stmt_del_tarea);
        $affected_rows = mysqli_stmt_affected_rows($stmt_del_tarea);
        mysqli_stmt_close($stmt_del_tarea);

        if ($affected_rows > 0) {
            mysqli_commit($con);
            $response = ['status' => 'success', 'message' => 'Tarea eliminada exitosamente.'];
        } else {
            throw new Exception("La tarea no pudo ser eliminada o ya no existía.");
        }

    } catch (Exception $e) {
        mysqli_rollback($con);
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Método no permitido.';
}
echo json_encode($response);
?>