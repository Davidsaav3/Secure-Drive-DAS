<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("functions.php");
    include_once("sql.php");
    require __DIR__ . '/vendor/autoload.php'; // Incluir la biblioteca JWT
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    
    $sql = "SELECT * FROM Users WHERE username='".$data->username."'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $response = array("code" => 400); // El nombre de usuario ya está en uso
    } else {
        $randomNumber = rand(100000, 999999);
        $keysToUse = generateRSA();
        $ogHash = generateHash($data->password);
        $pvKey = AESEncoding($keysToUse["private"], $ogHash);
        $sql2 = "INSERT INTO Users (username, password, status) VALUES ('".$data->username."', '".prepareHashToUpload($ogHash)."', '1')";
    
        if ($conn->query($sql2) === TRUE) {
            $inserted_id = $conn->insert_id;
            
            // Generar el payload del token
            $payload = array(
                "id" => $inserted_id,
                "username" => $data->username
            );
    
            // Configurar la clave secreta (debes cambiarla por una clave segura)
            $secretKey = "your_secret_key";
    
            // Generar el token JWT
            $token = Firebase\JWT\JWT::encode($payload, $secretKey, 'HS256');
    
            $response = array("code"  => 100, "id" => $inserted_id, "username" => $data->username, "token" => $token); // Devolver el id, el username y el token JWT
        } else {
            $response = array("code"  => 401); // Error al insertar el usuario
        }
    }
    
    closeDataBaseConnection($conn);
    echo json_encode($response);
?>
