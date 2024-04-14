<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    require_once("vendor/autoload.php");
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $token = $data->token;
    
    if (!$token) {
        echo json_encode(array("mensaje" => "Token de autorización no proporcionado"));
        exit();
    }
    
    try {
        $key = 'your_secret_key';
        $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));    
        $id_user_from_token = $decoded->id;
    } catch (Exception $e) {
        echo json_encode(array("mensaje" => "Token de autorización inválido"));
        exit();
    }
    
    if ($id_user_from_token !== $data->id_user) {
        // Si no coinciden, devolver un error de autorización
        echo json_encode(array("mensaje" => "No tienes permiso para editar este perfil"));
        exit();
    }
    
    $id_user = $data->id_user;
    $status = $data->status;
    
    // Actualizar el estado del perfil en la base de datos
    $sql_update_profile = "UPDATE Users SET status = ? WHERE id = ?";
    $stmt_update_profile = $conn->prepare($sql_update_profile);
    $stmt_update_profile->bind_param("ii", $status, $id_user);
    
    if ($stmt_update_profile->execute()) {
        echo json_encode(array("mensaje" => "¡Perfil editado correctamente!"));
    } else {
        echo json_encode(array("mensaje" => "Error al editar el perfil: " . $stmt_update_profile->error));
    }
    
    $stmt_update_profile->close();
    $conn->close();
?>
