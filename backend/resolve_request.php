<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    require_once("vendor/autoload.php"); 

    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id_sender = $data->id_sender;
    $id_receiver = $data->id_receiver;
    $status = $data->status;
    $token = $data->token;
    
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
    
    $sql = "UPDATE Requests SET status= ? WHERE id_sender= ? AND id_receiver= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $status, $id_sender, $id_receiver);
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Solicitud resuelta correctamente!"));
    } 
    else {
        echo json_encode(array("mensaje" => "Error al resolver solicitud: " . $stmt->error));
    }
    
    $stmt->close();
    $conn->close();
?>
