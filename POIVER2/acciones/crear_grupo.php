<?php
session_start();
include('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado.']);
        exit;
    }

    $nombre_grupo = trim($_POST['nombre_grupo']);
    $ids_usuarios_miembros = isset($_POST['miembros']) ? $_POST['miembros'] : []; // Array de IDs de usuarios
    $id_creador = $_SESSION['id'];

    if (empty($nombre_grupo)) {
        echo json_encode(['status' => 'error', 'message' => 'El nombre del grupo no puede estar vacío.']);
        exit;
    }

    if (empty($ids_usuarios_miembros)) {
        echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar al menos un miembro para el grupo.']);
        exit;
    }

    // Asegurarse de que el creador esté en la lista de miembros
    if (!in_array($id_creador, $ids_usuarios_miembros)) {
        $ids_usuarios_miembros[] = $id_creador;
    }
    
    // Mínimo 3 integrantes según el PDF (aunque el PDF indica para "Sesión en forma grupal")
    // Para la creación, el requisito de 3 miembros podría no aplicar, pero sí para la funcionalidad del chat grupal.
    // Lo dejaremos flexible para la creación por ahora.

    mysqli_begin_transaction($con);

    try {
        // 1. Insertar en la tabla `grupos`
        $stmt_grupo = mysqli_prepare($con, "INSERT INTO grupos (nombre_grupo, id_creador) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_grupo, "si", $nombre_grupo, $id_creador);
        mysqli_stmt_execute($stmt_grupo);
        $id_grupo_nuevo = mysqli_insert_id($con);
        mysqli_stmt_close($stmt_grupo);

        if (!$id_grupo_nuevo) {
            throw new Exception("Error al crear el grupo.");
        }

        // 2. Insertar en la tabla `miembros_grupo`
        $stmt_miembro = mysqli_prepare($con, "INSERT INTO miembros_grupo (id_grupo, id_usuario) VALUES (?, ?)");
        foreach ($ids_usuarios_miembros as $id_usuario) {
            mysqli_stmt_bind_param($stmt_miembro, "ii", $id_grupo_nuevo, $id_usuario);
            mysqli_stmt_execute($stmt_miembro);
        }
        mysqli_stmt_close($stmt_miembro);

        mysqli_commit($con);
        echo json_encode(['status' => 'success', 'message' => 'Grupo creado exitosamente.', 'id_grupo' => $id_grupo_nuevo]);

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>