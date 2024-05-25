<?php

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST, GET");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Referrer-Policy: unsafe-url");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'");
include_once("functions.php");
$key = getConfigVariable('key');
$enc = getConfigVariable('enc');
if (isset($_SERVER['HTTP_KEY'])) {
    $api_key = $_SERVER['HTTP_KEY'];
    if ($api_key === $key) {
        echo "Clave API válida";
    } 
    else {
        header("HTTP/1.1 401 Unauthorized");
        echo "Clave API inválida";
        exit();
    }
} 
else {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

// Ruta del archivo JSON en el servidor
$json_file = 'logs/logs.json';

// Leer el contenido del archivo JSON
$json_data = AESDecode(file_get_contents($json_file), $enc);

// Decodificar el JSON en un array asociativo
$data = json_decode($json_data, true);

// Calcular la fecha hace tres meses
$three_months_ago = strtotime('-3 months');

// Filtrar los registros que tienen una fecha anterior a hace tres meses
$new_data = array_filter($data, function($record) use ($three_months_ago) {
    $login_time = strtotime($record['login_time']);
    return $login_time >= $three_months_ago;
});

// Codificar el nuevo array de datos en formato JSON
$new_json_data = json_encode(array_values($new_data));

// Escribir el nuevo contenido en el archivo JSON
file_put_contents($json_file, AESEncoding($new_json_data, $enc));

echo "Registros de hace 3 meses o más eliminados y archivo actualizado.";
?>
