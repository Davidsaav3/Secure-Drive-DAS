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

include_once("functions.php");
include_once("sql.php");
require __DIR__ . '/vendor/autoload.php'; // Incluir la biblioteca JWT

$conn = createDataBaseConnection();
$data = json_decode(file_get_contents('php://input'));

// Consulta preparada para verificar si el nombre de usuario ya existe
$sql_check_username = "SELECT * FROM Users WHERE username=?";
$stmt_check_username = $conn->prepare($sql_check_username);
$stmt_check_username->bind_param("s", $data->username);
$stmt_check_username->execute();
$result_check_username = $stmt_check_username->get_result();

if ($result_check_username->num_rows > 0) {
    $response = array("code" => 400); // El nombre de usuario ya está en uso
} else {
    $ogHash = generateHash($data->password);
    $uploadHash = prepareHashToUpload($ogHash);
    // Consulta preparada para insertar el nuevo usuario
    $sql_insert_user = "INSERT INTO Users (username, password, status) VALUES (?, ?, '0')";
    $stmt_insert_user = $conn->prepare($sql_insert_user);
    $stmt_insert_user->bind_param("ss", $data->username, $uploadHash);

    if ($stmt_insert_user->execute()) {
        $inserted_id = $stmt_insert_user->insert_id;

        // Generar el payload del token
        $payload = array(
            "id" => $inserted_id,
            "username" => $data->username
        );

        // Configurar la clave secreta
        $secretKey = $ogHash;

        // Generar el token JWT
        $token = Firebase\JWT\JWT::encode($payload, $secretKey, 'HS256');

        $response = array("code"  => 100, "id" => $inserted_id, "username" => $data->username, "token" => $token); // Devolver el id, el username y el token JWT
    } else {
        $response = array("code"  => 401); // Error al insertar el usuario
    }
}

// Cerrar todas las conexiones y liberar recursos
$stmt_check_username->close();
$stmt_insert_user->close();
closeDataBaseConnection($conn);

echo json_encode($response);
?>
