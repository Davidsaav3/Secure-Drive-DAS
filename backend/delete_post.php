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
    
    $sql = "DELETE FROM Posts WHERE id = ? AND id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $id_user);
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Archivo eliminado correctamente!"));
    } else {
        echo json_encode(array("mensaje" => "Error al eliminar el archivo: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
