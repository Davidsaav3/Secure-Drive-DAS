<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id = $data->id;
    $text = $data->text;

    $sql = "UPDATE Posts SET text = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $text, $id);
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Post editado correctamente!"));
    } else {
        echo json_encode(array("mensaje" => "Error al editar el post: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
