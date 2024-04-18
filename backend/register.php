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
    $sql_insert_user = "INSERT INTO Users (username, password, status) VALUES (?, ?, '1')";
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
