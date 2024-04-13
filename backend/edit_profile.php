<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id_user = $data->id_user;
    $status = $data->status;
    
    $sql = "UPDATE Users SET status= ? WHERE id= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $status, $id_user);
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Perfil editado correctamente!"));
    } 
    else {
        echo json_encode(array("mensaje" => "Error al editar perfil: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
