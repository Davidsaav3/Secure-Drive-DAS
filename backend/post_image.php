<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id_user = $data->id_user;
    $text = $data->text;
    $url_image = $data->url_image; 
    
    $sql = "INSERT INTO Posts (id_user, text, url_image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_user, $text, $url_image);
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Imagen subida correctamente!"));
    }
    else {
        echo json_encode(array("mensaje" => "Error al subir imagen: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
