<?php
include('../config/config.php'); //
// session_start(); // Descomentar si necesitas datos de sesión, aunque los principales vienen por POST

$response = ['status' => 'error', 'message' => 'Error desconocido.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_equipo_user = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    
    // Datos del remitente y del grupo
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $id_grupo_receptor = isset($_POST['id_grupo_receptor_archivo']) ? (int)$_POST['id_grupo_receptor_archivo'] : 0;
    $user_email = isset($_POST['user']) ? filter_var($_POST['user'], FILTER_SANITIZE_EMAIL) : ''; // Email del remitente

    $leido = "NO"; // En grupos, "leido" es más complejo; usualmente no se usa igual que en privados.
                  // Se podría omitir o tener una lógica diferente para grupos.
                  // Para la tabla msjs, si la columna "leido" no puede ser NULL, se debe enviar un valor.
    date_default_timezone_set("America/Bogota");
    $hora = date('h:i a', time() - 3600 * date('I'));
    $fecha = date("d/m/Y");
    $FechaMsj = $fecha . " " . $hora;

    if ($user_id == 0 || $id_grupo_receptor == 0 || empty($user_email)) {
        $response['message'] = 'Faltan datos del remitente o del grupo.';
        echo json_encode($response); // Es mejor devolver JSON para manejo en JS
        exit;
    }

    $directorio = '../archivos/';
    if (!file_exists($directorio)) {
        if (!mkdir($directorio, 0777, true)) {
            $response['message'] = 'No se puede crear el directorio de extracción.';
            echo json_encode($response);
            exit;
        }
    }

    if (isset($_FILES["namearchivo_grupo"]) && !empty($_FILES["namearchivo_grupo"]["name"])) {
        $filename = $_FILES["namearchivo_grupo"]["name"];
        $source = $_FILES["namearchivo_grupo"]["tmp_name"];
        $file_error = $_FILES["namearchivo_grupo"]["error"];

        if ($file_error !== UPLOAD_ERR_OK) {
            $response['message'] = 'Error al subir el archivo. Código: ' . $file_error;
            echo json_encode($response);
            exit;
        }
        
        $logitudPass = 10;
        $newNameFoto = substr(md5(microtime()), 1, $logitudPass);
        $explode = explode('.', $filename);
        $extension_foto = strtolower(array_pop($explode)); // a minúsculas por consistencia
        $nuevoNameFoto = $newNameFoto . '.' . $extension_foto;
        $target_path = $directorio . '/' . $nuevoNameFoto;

        if (move_uploaded_file($source, $target_path)) {
            // Insertar registro del archivo en la tabla msjs para el grupo
            // 'to_user' y 'to_id' no se usan para chats grupales, pero la tabla los tiene.
            // Podrías dejarlos NULL si tu tabla lo permite, o un valor placeholder.
            // Lo importante es id_grupo_receptor y tipo_chat='grupal'
            
            $sqlInsertArchivoGrupo = "INSERT INTO msjs (user, user_id, id_grupo_receptor, fecha, nombre_equipo_user, leido, archivos, tipo_chat) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, 'grupal')";
            $stmt = mysqli_prepare($con, $sqlInsertArchivoGrupo);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "siissss", $user_email, $user_id, $id_grupo_receptor, $FechaMsj, $nombre_equipo_user, $leido, $nuevoNameFoto);
                if (mysqli_stmt_execute($stmt)) {
                    $response = ['status' => 'success', 'message' => 'Archivo enviado al grupo.'];
                } else {
                    $response['message'] = 'Error al guardar registro del archivo en BD: ' . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            } else {
                 $response['message'] = 'Error al preparar la inserción del archivo: ' . mysqli_error($con);
            }
        } else {
            $response['message'] = 'Error al mover el archivo al directorio final.';
        }
    } else {
        $response['message'] = 'No se seleccionó ningún archivo o hubo un error en la subida.';
    }
} else {
     $response['message'] = 'Método no permitido.';
}
// Devuelve siempre una respuesta JSON para que el fetch en JS la maneje
echo json_encode($response);
?>