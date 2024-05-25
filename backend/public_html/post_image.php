<?php
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
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

include_once("sql.php");
include_once("functions.php");

require_once("vendor/autoload.php"); 

$key = getConfigVariable('enc');
$conn = createDataBaseConnection();

// Verificar si se han recibido los datos del formulario y el archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['url_image']) && isset($_POST['id_user']) && isset($_POST['text'])) {
    $id_user = $_POST['id_user'];
    $text = $_POST['text'];

    $token = $_POST['token'];
    if (!$token) {
        echo json_encode(array("mensaje" => "Token de autorización no proporcionado"));
        exit();
    }
    try {
        $sql_check_password = "SELECT password FROM Users WHERE id=?";
        $stmt_check_password = $conn->prepare($sql_check_password);
        $stmt_check_password->bind_param("s", $id_user);
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

    // Verificar si el archivo se cargó correctamente
    if ($_FILES['url_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(array("mensaje" => "Error al cargar la imagen."));
        exit();
    }
    
    $uploadedFile = $_FILES['url_image']['tmp_name'];
    $filename = $_FILES['url_image']['name'];
    
    $destiny= 'storage/';
    $destination = $destiny . $id_user . '/' . $filename; 
    // Crea el directorio si no existe
    if (!is_dir($destiny . $id_user)) {
        mkdir($destiny . $id_user, 0777, true);
    }
    file_put_contents($uploadedFile, AESEncoding(file_get_contents($uploadedFile), $key));
    if (!move_uploaded_file($uploadedFile, $destination)) {
        echo json_encode(array("mensaje" => "Error al subir la imagen."));
        exit();
    }
        
    // Verificar si el ID de usuario existe antes de insertar el post
    $sql_check_user = "SELECT id FROM Users WHERE id = ?";
    $stmt_check_user = $conn->prepare($sql_check_user);
    $stmt_check_user->bind_param("i", $id_user);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();
    
    if ($result_check_user->num_rows === 0) {
        echo json_encode(array("mensaje" => "Error al subir imagen: ID de usuario no válido."));
        exit(); // Salir del script si el ID de usuario no es válido
    }
    
    // Preparar la consulta para insertar el post
    $sql = "INSERT INTO Posts (id_user, text, url_image, date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $route = AESEncoding($destination, $key);
    $stmt->bind_param("iss", $id_user, $text, $route); // Guardar la ruta del archivo en la base de datos
    
    if ($stmt->execute()) {
        echo json_encode(array("mensaje" => "¡Imagen subida correctamente!"));
    }
    else {
        echo json_encode(array("mensaje" => "Error al subir imagen: " . $stmt->error));
    }
    
    $stmt->close();
}
else {
    echo json_encode(array("mensaje" => "Error: Datos del formulario no recibidos."));
}

$conn->close();
?>
