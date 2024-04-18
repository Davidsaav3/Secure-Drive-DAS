<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

include_once("sql.php");
include_once("functions.php");
require_once("vendor/autoload.php");

$conn = createDataBaseConnection();
$data = json_decode(file_get_contents('php://input'));

$id_post = $data->id;
$id_user = $data->id_user;

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
        $decoded_id = $decoded->id;
        
        if ($id_user != $decoded_id) {
            echo json_encode(array("mensaje" => "El ID de usuario no coincide con el token."));
            exit();
        }
    } else {
        $response = array("code"  => 401); // Error con la base de datos
    }
} catch (Exception $e) {
    echo json_encode(array("mensaje" => "Token de autorización inválido"));
    exit();
}

// Verificar si ya existe un registro en la tabla Likes para este usuario y post
$sql_check_like = "SELECT * FROM Likes WHERE id_user = ? AND id_post = ?";
$stmt_check_like = $conn->prepare($sql_check_like);
$stmt_check_like->bind_param("ii", $id_user, $id_post);
$stmt_check_like->execute();
$result_check_like = $stmt_check_like->get_result();

if ($result_check_like->num_rows === 0) {
    // Si no existe, insertar un nuevo registro
    $sql_insert_like = "INSERT INTO Likes (id_user, id_post) VALUES (?, ?)";
    $stmt_insert_like = $conn->prepare($sql_insert_like);
    $stmt_insert_like->bind_param("ii", $id_user, $id_post);
    
    if ($stmt_insert_like->execute()) {
        echo json_encode(array("mensaje" => "Nuevo registro de like insertado"));
    } else {
        echo json_encode(array("mensaje" => "Error al insertar el nuevo registro de like: " . $stmt_insert_like->error));
    }
    
    $stmt_insert_like->close();
} else {
    // Si existe, eliminar el registro existente
    $sql_delete_like = "DELETE FROM Likes WHERE id_user = ? AND id_post = ?";
    $stmt_delete_like = $conn->prepare($sql_delete_like);
    $stmt_delete_like->bind_param("ii", $id_user, $id_post);
    
    if ($stmt_delete_like->execute()) {
        echo json_encode(array("mensaje" => "Registro de like eliminado correctamente"));
    } else {
        echo json_encode(array("mensaje" => "Error al eliminar el registro de like: " . $stmt_delete_like->error));
    }
    
    $stmt_delete_like->close();
}

$stmt_check_like->close();
$conn->close();
?>
