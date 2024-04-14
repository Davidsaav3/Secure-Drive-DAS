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
        $id_user = $decoded->id;
    } catch (Exception $e) {
        echo json_encode(array("mensaje" => "Token de autorización inválido"));
        exit();
    }
    
    if (!property_exists($data, 'id')) {
        echo json_encode(array("mensaje" => "No se proporcionó el ID del post"));
        exit();
    }
    
    if (!property_exists($data, 'text')) {
        echo json_encode(array("mensaje" => "No se proporcionó el texto del post"));
        exit();
    }
    
    $id = $data->id;
    $text = $data->text;
    
    $sql_check_permission = "SELECT id FROM Posts WHERE id = ? AND id_user = ?";
    $stmt_check_permission = $conn->prepare($sql_check_permission);
    $stmt_check_permission->bind_param("ii", $id, $id_user);
    $stmt_check_permission->execute();
    $result_check_permission = $stmt_check_permission->get_result();
    
    if ($result_check_permission->num_rows === 0) {
        echo json_encode(array("mensaje" => "No tienes permisos para editar este post"));
        exit();
    }
    
    $sql_update_post = "UPDATE Posts SET text = ? WHERE id = ?";
    $stmt_update_post = $conn->prepare($sql_update_post);
    $stmt_update_post->bind_param("si", $text, $id);
    
    if ($stmt_update_post->execute()) {
        echo json_encode(array("mensaje" => "¡Post editado correctamente!"));
    } else {
        echo json_encode(array("mensaje" => "Error al editar el post: " . $stmt_update_post->error));
    }
    
    $stmt_update_post->close();
    $conn->close();
?>
