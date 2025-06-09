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

    $id_creador = $_SESSION['id'];
    $id_grupo = isset($_POST['id_grupo_tarea']) ? (int)$_POST['id_grupo_tarea'] : 0;
    $titulo_tarea = isset($_POST['titulo_tarea']) ? trim($_POST['titulo_tarea']) : '';
    $descripcion_tarea = isset($_POST['descripcion_tarea']) ? trim($_POST['descripcion_tarea']) : null;

    if (empty($id_grupo) || empty($titulo_tarea)) {
        $response['message'] = 'Faltan datos para crear la tarea (ID de grupo o título).';
        echo json_encode($response);
        exit;
    }

    mysqli_begin_transaction($con);

    try {
        // 1. Insertar la tarea en tareas_grupo
        $stmt_crear_tarea = mysqli_prepare($con, "INSERT INTO tareas_grupo (id_grupo, id_creador, titulo_tarea, descripcion_tarea) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_crear_tarea, "iiss", $id_grupo, $id_creador, $titulo_tarea, $descripcion_tarea);
        mysqli_stmt_execute($stmt_crear_tarea);
        $id_nueva_tarea = mysqli_insert_id($con);
        mysqli_stmt_close($stmt_crear_tarea);

        if (!$id_nueva_tarea) {
            throw new Exception("Error al insertar la tarea principal.");
        }

        // 2. Obtener los miembros del grupo
        $stmt_miembros = mysqli_prepare($con, "SELECT id_usuario FROM miembros_grupo WHERE id_grupo = ?");
        mysqli_stmt_bind_param($stmt_miembros, "i", $id_grupo);
        mysqli_stmt_execute($stmt_miembros);
        $result_miembros = mysqli_stmt_get_result($stmt_miembros);
        
        $miembros_del_grupo = [];
        while ($fila_miembro = mysqli_fetch_assoc($result_miembros)) {
            $miembros_del_grupo[] = $fila_miembro['id_usuario'];
        }
        mysqli_stmt_close($stmt_miembros);

        if (empty($miembros_del_grupo)) {
            throw new Exception("El grupo no tiene miembros para asignar la tarea.");
        }

        // 3. Insertar un registro en tarea_miembros_estado para cada miembro
        $stmt_estado_miembro = mysqli_prepare($con, "INSERT INTO tarea_miembros_estado (id_tarea, id_usuario, completada_usuario) VALUES (?, ?, FALSE)");
        foreach ($miembros_del_grupo as $id_miembro) {
            mysqli_stmt_bind_param($stmt_estado_miembro, "ii", $id_nueva_tarea, $id_miembro);
            mysqli_stmt_execute($stmt_estado_miembro);
        }
        mysqli_stmt_close($stmt_estado_miembro);

        mysqli_commit($con);
        $response = ['status' => 'success', 'message' => 'Tarea creada exitosamente.', 'id_tarea' => $id_nueva_tarea];

    } catch (Exception $e) {
        mysqli_rollback($con);
        $response['message'] = 'Error en la base de datos: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>