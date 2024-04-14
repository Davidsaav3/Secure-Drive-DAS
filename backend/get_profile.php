<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Referrer-Policy: unsafe-url");
    include_once("sql.php");
    require_once("vendor/autoload.php");


    // Obtener el username del parámetro POST
    $username = $_POST['username'];
    if (!isset($username)) {
        echo "Se requiere el parámetro username.";
        exit();
    }
    
    $token = $_POST['token'];
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

    // Crear una conexión a la base de datos
    $conn = createDataBaseConnection();

    // Consulta preparada para obtener las solicitudes de seguimiento
    $sql_solicitudes = "SELECT id_sender as id_user FROM Requests JOIN Users ON Requests.id_sender = Users.id WHERE id_receiver = ? AND Users.status = 0 AND Requests.status = 0";
    $stmt_solicitudes = $conn->prepare($sql_solicitudes);
    $stmt_solicitudes->bind_param("s", $username);
    $stmt_solicitudes->execute();
    $result_solicitudes = $stmt_solicitudes->get_result();

    $solicitudes_seguimiento = array();
    if ($result_solicitudes->num_rows > 0) {
        while($row_solicitud = $result_solicitudes->fetch_assoc()) {
            $solicitudes_seguimiento[] = array(
                "id_user" => $row_solicitud['id_user'],
            );
        }
    }

    // Consulta preparada para obtener la información del usuario
    $sql_info_usuario = "SELECT 
                            id,
                            username,
                            status,
                            (SELECT COUNT(*) FROM Posts WHERE id_user = Users.id) AS num_posts,
                            (SELECT COUNT(*) FROM Requests WHERE id_receiver = Users.id AND status = 1) AS followers,
                            (SELECT COUNT(*) FROM Requests WHERE id_sender = Users.id AND status = 1) AS following
                        FROM 
                            Users 
                        WHERE 
                            username = ?";
    $stmt_info_usuario = $conn->prepare($sql_info_usuario);
    $stmt_info_usuario->bind_param("s", $username);
    $stmt_info_usuario->execute();
    $result_info_usuario = $stmt_info_usuario->get_result();

    if ($result_info_usuario->num_rows > 0) {
        $row_info_usuario = $result_info_usuario->fetch_assoc();

        $user = array(
            "id" => $row_info_usuario['id'],
            "username" => $row_info_usuario['username'],
            "num_posts" => $row_info_usuario['num_posts'],
            "followers" => $row_info_usuario['followers'],
            "following" => $row_info_usuario['following'],
            "status" => $row_info_usuario['status'],
            "requests" => $solicitudes_seguimiento
        );

        echo json_encode($user);
    } else {
        echo "No se encontró información del usuario.";
    }

    // Cerrar la conexión
    $conn->close();
?>
