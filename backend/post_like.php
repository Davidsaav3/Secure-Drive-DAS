<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id = $data->id;
    $id_user = $data->id_user;
    
    $sql = "INSERT INTO Likes (id_user, id_post) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_user, $id);
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Like subido correctamente!"));
    } 
    else {
        echo json_encode(array("mensaje" => "Error al subir like: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
