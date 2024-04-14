<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    require_once("vendor/autoload.php"); // Incluir la biblioteca JWT
    
    require __DIR__ . '/vendor/autoload.php';
    
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
    } 
    catch (Exception $e) {
        echo json_encode(array("mensaje" => "Token de autorización inválido"));
        exit();
    }
    
    if (!property_exists($data, 'id_post')) {
        echo json_encode(array("mensaje" => "No se proporcionó el ID del post"));
        exit();
    }
    $id_post = $data->id_post;
    
    $sql_check_permission = "SELECT id FROM Posts WHERE id = ? AND id_user = ?";
    $stmt_check_permission = $conn->prepare($sql_check_permission);
    $stmt_check_permission->bind_param("ii", $id_post, $id_user);
    $stmt_check_permission->execute();
    $result_check_permission = $stmt_check_permission->get_result();
    
    if ($result_check_permission->num_rows === 0) {
        echo json_encode(array("mensaje" => "No tienes permisos para eliminar este post"));
        exit();
    }
    
    // Eliminar el post de la base de datos
    $sql_delete_post = "DELETE FROM Posts WHERE id = ?";
    $stmt_delete_post = $conn->prepare($sql_delete_post);
    $stmt_delete_post->bind_param("i", $id_post);
    
    if ($stmt_delete_post->execute()) {
        echo json_encode(array("mensaje" => "¡Post eliminado correctamente!"));
    } else {
        echo json_encode(array("mensaje" => "Error al eliminar el post: " . $stmt_delete_post->error));
    }
    
    $stmt_delete_post->close();
    $conn->close();
?>
