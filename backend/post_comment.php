<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include_once("sql.php");
require_once("vendor/autoload.php"); 
include_once("functions.php");

$conn = createDataBaseConnection();
$data = json_decode(file_get_contents('php://input'));

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
        $decoded_id = $decoded->id; // Usar una variable diferente para almacenar el ID extraído del token
        if ($id_user != $decoded_id) {
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

$id_post = $data->id_post;
$text = $data->text;

$sql = "INSERT INTO Comments (id_user, id_post, text) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id_user, $id_post, $text); 

if ($stmt->execute()) {
    echo json_encode(array("mensaje" => "¡Comentario agregado correctamente!"));
} else {
    echo json_encode(array("mensaje" => "Error al agregar el comentario: " . $stmt->error));
}

$stmt->close();
$conn->close();
?>
