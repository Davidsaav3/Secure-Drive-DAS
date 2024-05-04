<?php
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Referrer-Policy: unsafe-url");
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

// Incluir el archivo autoload.php generado por Composer
require __DIR__ . '/vendor/autoload.php';
include_once("sql.php");
include_once("functions.php");

$enc = getConfigVariable(('enc'));
$conn = createDataBaseConnection();
$data = json_decode(file_get_contents('php://input'));

// Consulta preparada para obtener la información del usuario por nombre de usuario
$sql = "SELECT id, password, username FROM Users WHERE username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $data->username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (checkHash($row['password'], $data->password)) {

        $payload = array(
            "id" => $row['id'],
            "username" => $row['username']
        );

        $secretKey = prepareHashToUse($row['password']);
        $token = Firebase\JWT\JWT::encode($payload, $secretKey, 'HS256');

        // Directorio donde se guardará el archivo JSON
        $json_dir = 'logs/';
        // Nombre del archivo JSON
        $json_file = 'logs.json';
        // Ruta completa al archivo JSON
        $json_path = $json_dir . $json_file;
        // Obtener datos del usuario
        $login_time = date('Y-m-d H:i:s');
        $IP_address = $_SERVER['REMOTE_ADDR']; // Obtener la dirección IP del usuario
        // Leer el archivo JSON de logs si existe
        $logs = [];
        if (file_exists($json_path)) {
            $logs_json = AESDecode(file_get_contents($json_path), $enc);
            $logs = json_decode($logs_json, true);
        }
        
        // Agregar el nuevo log al array
        $new_log = array(
            'user_id' => $row['id'],
            'login_time' => $login_time,
            'IP_address' => $IP_address
        );
        $logs[] = $new_log;
        
        // Guardar el array de logs en el archivo JSON
        file_put_contents($json_path, AESEncoding(json_encode($logs), $enc));
        
 


        $response = array("code" => 100, "token" => $token, "id" => $row['id'], "username" => $row['username']);
    } else {
        $response = array("code" => 400); // Contraseña incorrecta
    }
} else {
    $response = array("code" => 401); // Usuario no encontrado
}

// Cerrar todas las consultas preparadas y liberar recursos
$stmt->close();
closeDataBaseConnection($conn);

echo json_encode($response);
?>
