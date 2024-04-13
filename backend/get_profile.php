<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Referrer-Policy: unsafe-url");
    include_once("sql.php");
    
    $id_user = $_POST['id_user'];
    if (!isset($id_user)) {
        echo "Se requiere el parámetro id_user.";
        exit();
    }
    
    $conn = createDataBaseConnection();
    $sql_solicitudes = "SELECT id_sender as id_user FROM Requests JOIN Users ON Requests.id_sender = Users.id WHERE id_receiver = $id_user AND Users.status = 0";

    $result_solicitudes = $conn->query($sql_solicitudes);
    $solicitudes_seguimiento = array();
    
    if ($result_solicitudes->num_rows > 0) {
        while($row_solicitud = $result_solicitudes->fetch_assoc()) {
            $solicitudes_seguimiento[] = array(
                "id_user" => $row_solicitud['id_user'],
            );
        }
    }
    
    $sql_info_usuario = "SELECT 
                            id,
                            username,
                            (SELECT COUNT(*) FROM Posts WHERE id_user = $id_user) AS num_posts,
                            (SELECT COUNT(*) FROM Requests WHERE id_receiver = $id_user AND status = 1) AS followers,
                            (SELECT COUNT(*) FROM Requests WHERE id_sender = $id_user AND status = 1) AS following
                        FROM 
                            Users 
                        WHERE 
                            id = $id_user";
    
    $result_info_usuario = $conn->query($sql_info_usuario);
    
    if ($result_info_usuario->num_rows > 0) {
        $row_info_usuario = $result_info_usuario->fetch_assoc();
        
        $user = array(
            "id" => $row_info_usuario['id'],
            "username" => $row_info_usuario['username'],
            "num_posts" => $row_info_usuario['num_posts'],
            "followers" => $row_info_usuario['followers'],
            "following" => $row_info_usuario['following'],
            "status" => true,
            "requests" => $solicitudes_seguimiento
        );
    
        echo json_encode($user);
    } 
    else {
        echo "No se encontró información del usuario.";
    }
    
    $conn->close();
?>
