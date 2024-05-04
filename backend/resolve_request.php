<?php
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
//header("Content-Security-Policy: default-src 'self'");
    
        $allowed_domains = array(
        'http://localhost:4200',
        'https://uabook-81dcf.web.app'
    );
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if (in_array($origin, $allowed_domains)) {
        header("Access-Control-Allow-Origin: $origin");
    } 
    else {
        header("HTTP/1.1 403 Forbidden");
        exit();
    }
    
    include_once("sql.php");
    require_once("vendor/autoload.php"); 
    include_once("functions.php");

    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $id_sender = $data->id_sender;
    $id_receiver = $data->id_receiver;
    $status = $data->status;; 
    
    $token = $data->token;
    if (!$token) {
        echo json_encode(array("mensaje" => "Token de autorización no proporcionado"));
        exit();
    }
    try {
        $sql_check_password = "SELECT password FROM Users WHERE id=?";
        $stmt_check_password = $conn->prepare($sql_check_password);
        $stmt_check_password->bind_param("s", $data->id_user);
        $stmt_check_password->execute();
        $result_check_password = $stmt_check_password->get_result();
        if ($result_check_password->num_rows > 0) {
            $row = $result_check_password->fetch_assoc();
            $hash = prepareHashToUse($row['password']);
            $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($hash, 'HS256'));   
            $decoded_id = $decoded->id;
        
        if ($id_receiver != $decoded_id) {
            echo json_encode(array("mensaje" => "El ID de usuario no coincide con el token."));
            exit();
        }
        } 
        else {
            $response = array("code"  => 401); // Error con la base de datos
        }
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
