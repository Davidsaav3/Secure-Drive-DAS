<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id_sender = $data->id_sender;
    $id_receiver = $data->id_receiver;
    $status = 0; 
    
    $sql = "INSERT INTO Requests (id_sender, id_receiver, status) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id_sender, $id_receiver, $status);
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Solicitud subida correctamente!"));
    } else {
        echo json_encode(array("mensaje" => "Error al subir solicitud: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
