<?php
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
//header("Content-Security-Policy: default-src 'self'");
    
    $allowed_domains = array(
        'http://localhost:4200',
        'https://uabook-81dcf.web.app'
    );
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if (in_array($origin, $allowed_domains)) {
        header("Access-Control-Allow-Origin: $origin");
    } 
    else {
        header("HTTP/1.1 403 Forbidden");
        exit();
    }
    
    include_once("sql.php");
    require_once("vendor/autoload.php");
    include_once("functions.php");

    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id_user = $data->id_user;

    $token = $data->token;
    if (!$token) {
        echo json_encode(array("mensaje" => "Token de autorización no proporcionado"));
        exit();
    }
    try {
        $sql_check_password = "SELECT password FROM Users WHERE id=?";
        $stmt_check_password = $conn->prepare($sql_check_password);
        $stmt_check_password->bind_param("s", $data->id_user);
        $stmt_check_password->execute();
        $result_check_password = $stmt_check_password->get_result();
        if ($result_check_password->num_rows > 0) {
            $row = $result_check_password->fetch_assoc();
            $hash = prepareHashToUse($row['password']);
            $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($hash, 'HS256'));   
            $id_user_from_token = $decoded->id;

            if ($id_user != $id_user_from_token) {
                echo json_encode(array("mensaje" => "El ID de usuario no coincide con el token."));
                exit();
            }  
        } 
        else {
            $response = array("code"  => 401); // Error con la base de datos
        }
    } 
    catch (Exception $e) {
        echo json_encode(array("mensaje" => "Token de autorización inválido"));
        exit();
    }
    if (intval($id_user_from_token) != intval($data->id_user)) {
        // Si no coinciden, devolver un error de autorización
        echo json_encode(array("mensaje" => "No tienes permiso para editar este perfil"));
        exit();
    }
    
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
