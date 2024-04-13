<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include_once("sql.php");

$conn = createDataBaseConnection();

// Verificar si se han recibido los datos del formulario y el archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['url_image']) && isset($_POST['id_user']) && isset($_POST['text'])) {
    $id_user = $_POST['id_user'];
    $text = $_POST['text'];
    
    // Verificar si el archivo se cargó correctamente
    if ($_FILES['url_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(array("mensaje" => "Error al cargar la imagen."));
        exit();
    }
    
    // Obtener el archivo de la solicitud y guardarlo en una carpeta
    $uploadedFile = $_FILES['url_image']['tmp_name'];
    $uploadPath = 'storage/'; // Reemplazar con la ruta absoluta correcta
    $filename = $_FILES['url_image']['name'];
    $destination = $uploadPath . $filename;
    
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
    $stmt->bind_param("iss", $id_user, $text, $destination); // Guardar la ruta del archivo en la base de datos
    
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
