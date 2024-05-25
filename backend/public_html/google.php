<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Referrer-Policy: unsafe-url");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
//header("Content-Security-Policy: default-src 'self'");

include_once("sql.php");
include_once("functions.php");

// Incluir el archivo autoload.php generado por Composer
require __DIR__ . '/vendor/autoload.php';

// Obtener el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Obtener el código de autorización de Google
    $code = $_POST['code'];

    // Configurar los parámetros para intercambiar el código por un token de acceso
    $params = array(
        'code' => $code,
        'client_id' => '230938867099-dhnn6mvf1jvpnn9k74hhpmd9k96ajvo5.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect_uri' => 'https://uabook-81dcf.web.app/inicio', // URL de este script
        'grant_type' => 'authorization_code'
    );

    // Realizar una solicitud POST a la URL de token de Google OAuth para intercambiar el código por un token de acceso
    $response = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($params)
        )
    )));

    // Decodificar la respuesta JSON
    $token_info = json_decode($response, true);

    // Devolver el token de acceso al frontend
    $oauth_response = array("token" => $token_info);

    // Login normal
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
            $login_response = array("code" => 100, "token" => $token, "id" => $row['id'], "username" => $row['username']);
        } else {
            $login_response = array("code" => 400); // Contraseña incorrecta
        }
    } else {
        $login_response = array("code"=> 401); // Usuario no encontrado
    }

    closeDataBaseConnection($conn);

    // Combinar las respuestas y devolverlas al frontend
    $response = array("oauth" => $oauth_response, "login" => $login_response);
    echo json_encode($response);
} else {
    // Manejar otros métodos de solicitud (GET, OPTIONS, etc.) según sea necesario
    echo json_encode(array("error" => "Método de solicitud no permitido"));
}
?>