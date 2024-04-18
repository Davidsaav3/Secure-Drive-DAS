<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include_once("sql.php");
require_once("vendor/autoload.php"); // Incluir la biblioteca JWT
include_once("functions.php");

require __DIR__ . '/vendor/autoload.php';
$conn = createDataBaseConnection();
$data = json_decode(file_get_contents('php://input'));
$token = $data->token;
$id_user = $data->id_user;

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
    } 
    else {
        $response = array("code"  => 401); // Error con la base de datos
    }
} 
catch (Exception $e) {
    echo json_encode(array("mensaje" => "Token de autorización inválido"));
    exit();
}

if (!property_exists($data, 'id_post')) {
    echo json_encode(array("mensaje" => "No se proporcionó el ID del post"));
    exit();
}
$id_post = $data->id_post;

$sql_check_permission = "SELECT id, url_image FROM Posts WHERE id = ? AND id_user = ?";
$stmt_check_permission = $conn->prepare($sql_check_permission);
$stmt_check_permission->bind_param("ii", $id_post, $id_user);
$stmt_check_permission->execute();
$result_check_permission = $stmt_check_permission->get_result();

if ($result_check_permission->num_rows === 0) {
    echo json_encode(array("mensaje" => "No tienes permisos para eliminar este post"));
    exit();
}

$row = $result_check_permission->fetch_assoc();
$image_url = $row['url_image'];

// Eliminar la imagen asociada al post
if ($image_url && file_exists($image_url)) {
    if (!unlink($image_url)) {
        echo json_encode(array("mensaje" => "Error al eliminar la imagen."));
        exit();
    }
}

// Eliminar el directorio asociado al ID de usuario si está vacío
$destination = 'storage/' . $id_user;
if (is_dir($destination) && count(glob($destination . '/*')) === 0) {
    rmdir($destination);
}

// Eliminar los likes asociados al post
$sql_delete_likes = "DELETE FROM Likes WHERE id_post = ?";
$stmt_delete_likes = $conn->prepare($sql_delete_likes);
$stmt_delete_likes->bind_param("i", $id_post);

if (!$stmt_delete_likes->execute()) {
    echo json_encode(array("mensaje" => "Error al eliminar los likes del post: " . $stmt_delete_likes->error));
    exit();
}

// Eliminar comentarios asociados al post
$sql_delete_comments = "DELETE FROM Comments WHERE id_post = ?";
$stmt_delete_comments = $conn->prepare($sql_delete_comments);
$stmt_delete_comments->bind_param("i", $id_post);

if (!$stmt_delete_comments->execute()) {
    echo json_encode(array("mensaje" => "Error al eliminar los comentarios del post: " . $stmt_delete_comments->error));
    exit();
}

// Eliminar el post de la base de datos
$sql_delete_post = "DELETE FROM Posts WHERE id = ?";
$stmt_delete_post = $conn->prepare($sql_delete_post);
$stmt_delete_post->bind_param("i", $id_post);

if ($stmt_delete_post->execute()) {
    echo json_encode(array("mensaje" => "¡Post eliminado correctamente!"));
} 
else {
    echo json_encode(array("mensaje" => "Error al eliminar el post: " . $stmt_delete_post->error));
}

$stmt_delete_likes->close();
$stmt_delete_comments->close();
$stmt_delete_post->close();
$conn->close();
?>
