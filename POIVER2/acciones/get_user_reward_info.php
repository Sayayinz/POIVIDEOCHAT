<?php
// acciones/get_user_reward_info.php
include('../config/config.php'); //
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'ID de usuario no proporcionado.'];

if (isset($_POST['userId'])) {
    $userId = (int)$_POST['userId'];

    $stmt = mysqli_prepare($con, "SELECT puntos_recompensa, nivel_recompensa FROM users WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userInfo = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($userInfo) {
            $response = [
                'status' => 'success',
                'puntos_recompensa' => $userInfo['puntos_recompensa'],
                'nivel_recompensa' => $userInfo['nivel_recompensa']
            ];
        } else {
            $response['message'] = 'Usuario no encontrado.';
        }
    } else {
        $response['message'] = 'Error en la preparación de la consulta.';
    }
}

echo json_encode($response);
exit();
?>