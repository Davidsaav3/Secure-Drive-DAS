<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

// Incluir el archivo autoload.php generado por Composer
require __DIR__ . '/vendor/autoload.php';
include_once("sql.php");
include_once("functions.php");

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

        // Registrar evento de inicio de sesión en la base de datos
        $login_time = date('Y-m-d H:i:s');
        $IP_address = $_SERVER['REMOTE_ADDR']; // Obtener la dirección IP del usuario
        $query = "INSERT INTO Log (user_id, login_time, IP_address) VALUES (?, ?, ?)";
        $stmt_log = $conn->prepare($query);
        $stmt_log->bind_param("iss", $row['id'], $login_time, $IP_address);
        $stmt_log->execute();
        $stmt_log->close();

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
