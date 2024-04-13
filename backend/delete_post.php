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
    
    // Primero eliminar las filas relacionadas en la tabla Comments
    $sql_delete_comments = "DELETE FROM Comments WHERE id_post = ?";
    $stmt_delete_comments = $conn->prepare($sql_delete_comments);
    $stmt_delete_comments->bind_param("i", $id);
    $stmt_delete_comments->execute();
    $stmt_delete_comments->close();
    
    // Luego eliminar las filas relacionadas en la tabla Likes
    $sql_delete_likes = "DELETE FROM Likes WHERE id_post = ?";
    $stmt_delete_likes = $conn->prepare($sql_delete_likes);
    $stmt_delete_likes->bind_param("i", $id);
    $stmt_delete_likes->execute();
    $stmt_delete_likes->close();
    
    // Finalmente, eliminar la fila en la tabla Posts
    $sql_delete_post = "DELETE FROM Posts WHERE id = ? AND id_user = ?";
    $stmt_delete_post = $conn->prepare($sql_delete_post);
    $stmt_delete_post->bind_param("ii", $id, $id_user);
    
    if ($stmt_delete_post->execute()) {
        echo json_encode(array("mensaje" => "¡Archivo eliminado correctamente!"));
    } else {
        echo json_encode(array("mensaje" => "Error al eliminar el archivo: " . $stmt_delete_post->error));
    }
    
    $stmt_delete_post->close();
    $conn->close();
?>
