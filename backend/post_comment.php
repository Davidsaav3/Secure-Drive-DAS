<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id_post = $data->id_post;
    $id_user = $data->id_user;
    $text = $data->text; 
    
    $sql = "INSERT INTO Comments (id_user, id_post, text) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_user, $id_post, $text); // Corregido: se cambia "ii" por "iis"
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Comentario agregado correctamente!"));
    } 
    else {
        echo json_encode(array("mensaje" => "Error al agregar el comentario: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
