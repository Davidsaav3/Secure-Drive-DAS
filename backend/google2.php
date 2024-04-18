<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    include_once("functions.php");
    
    // Incluir el archivo autoload.php generado por Composer
    require __DIR__ . '/vendor/autoload.php';
    
    // Obtener el código de autorización de Google
    $code = $_POST['code'];
    
    // Configurar los parámetros para intercambiar el código por un token de acceso
    $params = array(
        'code' => $code,
        'client_id' => '230938867099-dhnn6mvf1jvpnn9k74hhpmd9k96ajvo5.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect_uri' => 'http://localhost:4200/inicio', // URL de este script
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
    echo json_encode($token_info);
?>
