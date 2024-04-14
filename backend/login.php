<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    include_once("functions.php");
    
    // Incluir el archivo autoload.php generado por Composer
    require __DIR__ . '/vendor/autoload.php';
    
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    
    $sql = "SELECT id, password, username FROM Users WHERE username='".$data->username."'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if(checkHash($row['password'], $data->password)) {
            // Se comprueba si la contraseña coincide
            // Generar el payload del token
            $payload = array(
                "id" => $row['id'],
                "username" => $row['username']
            );
    
            // Configurar la clave secreta (debes cambiarla por una clave segura)
            $secretKey = "your_secret_key";
    
            // Generar el token JWT
            $token = Firebase\JWT\JWT::encode($payload, $secretKey, 'HS256');
    
            // Devolver el token junto con el id y el username
            $response = array("code" => 100, "token" => $token, "id" => $row['id'], "username" => $row['username']);
        } else {
            $response = array("code" => 400); // Contraseña incorrecta
        }
    } else {
        $response = array("code"=> 401); // Usuario no encontrado
    }
    
    closeDataBaseConnection($conn);
    echo json_encode($response);
?>
