<?php
// POIVER2/get_active_clickuser.php
session_start();
include('config/config.php'); // Ajusta la ruta si es necesario

if (isset($_POST['userIdSession'])) {
    $userIdSession = (int)$_POST['userIdSession'];

    // Asegúrate que tipoClick exista en tu tabla clickuser
    $query = "SELECT clickUser FROM clickuser WHERE UserIdSession = ? AND tipoClick = 'user' LIMIT 1";
    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userIdSession);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            echo $row['clickUser'];
        } else {
            echo "0"; 
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "0"; 
    }
} else {
    echo "0"; 
}
?>