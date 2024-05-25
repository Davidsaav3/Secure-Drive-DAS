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

include_once("sql.php");
require_once("vendor/autoload.php");
include_once("functions.php");

// Crear una conexión a la base de datos
$conn = createDataBaseConnection();

// Obtener el username del parámetro POST
$username = $_POST['username'];
$id2 = $_POST['id'];

if (!isset($username)) {
    echo "Se requiere el parámetro username.";
    exit();
}

$token = $_POST['token'];
if (!$token) {
    echo json_encode(array("mensaje" => "Token de autorización no proporcionado"));
    exit();
}

$auth = 0;
$id_user = null; // Inicializar $id_user
try {
       // Decodificar el token para obtener el ID del usuario
    $sql_check_password = "SELECT 
    (SELECT id FROM Users WHERE username = ?) AS id_result,
    (SELECT password FROM Users WHERE id = ?) AS password_result;";
    
    $stmt_check_password = $conn->prepare($sql_check_password);
    $stmt_check_password->bind_param("ss", $username, $id2);
    $stmt_check_password->execute();
    $result_check_password = $stmt_check_password->get_result();
     
    if ($result_check_password->num_rows > 0) {
        $row = $result_check_password->fetch_assoc();
        $id_user = $row['id_result']; // Obtener el ID del usuario
        $hash = prepareHashToUse($row['password_result']);
        
        $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($hash, 'HS256'));
        $username2 = $decoded->username;
        $id_user_from_token = $decoded->id;

        if ($id2 != $id_user_from_token) {
            echo json_encode(array("mensaje" => "El ID de usuario no coincide con el token."));
            exit();
        }  

        if ($id_user == $id2) {
            $auth = -1; // Otros casos
        }
        
    } else {
        echo json_encode(array("mensaje" => "Usuario no encontrado"));
        exit();
    }
} catch (Exception $e) {
    echo json_encode(array("mensaje" => "Token de autorización inválido"));
    exit();
}

// Consultar el estado de la cuenta del usuario en la tabla Users
$sql_check_account_status = "SELECT status FROM Users WHERE id = ?";
$stmt_check_account_status = $conn->prepare($sql_check_account_status);
$stmt_check_account_status->bind_param("i", $id_user);
$stmt_check_account_status->execute();
$result_account_status = $stmt_check_account_status->get_result();

if ($result_account_status->num_rows > 0) {
    $row_account_status = $result_account_status->fetch_assoc();
    $account_status = $row_account_status['status'];
} else {
    echo json_encode(array("mensaje" => "No se encontró el estado de la cuenta del usuario"));
    exit();
}

// Consultar el estado de las solicitudes del usuario en la tabla Requests
$sql_check_request_status = "SELECT status FROM Requests WHERE id_receiver = ? AND id_sender = ?";
$stmt_check_request_status = $conn->prepare($sql_check_request_status);
$stmt_check_request_status->bind_param("ii", $id_user, $id2);
$stmt_check_request_status->execute();
$result_request_status = $stmt_check_request_status->get_result();

//echo $result_request_status . $account_status . $auth;
//exit();

if ($result_request_status->num_rows > 0) {
    $row_request_status = $result_request_status->fetch_assoc();
    $request_status = $row_request_status['status'];
    
    // Determinar el valor del campo "auth" basado en el estado de la cuenta y las solicitudes
    //echo $request_status . $account_status . $auth;
    //exit();
        
    if ($auth != -1) {
        if ($account_status == 1) {
            // Cuenta abierta
            $auth = 1;
        } 
        //echo $request_status . $account_status . $auth;
        //exit();
        if ($account_status == 0 && $request_status == 1) {
            // Cuenta cerrada con solicitud aceptada
            //echo $request_status;
            //exit();
            $auth = 1;
        }
    }
} else {
    if($auth==0){
        $auth = $account_status; // Si no hay solicitudes
    }
}

// Consulta preparada para obtener las solicitudes de seguimiento
$sql_solicitudes = "SELECT id_sender as id_user, Users.username as username FROM Requests JOIN Users ON Requests.id_sender = Users.id WHERE id_receiver = ? AND Users.status = 0 AND Requests.status = 0";
$stmt_solicitudes = $conn->prepare($sql_solicitudes);
$stmt_solicitudes->bind_param("i", $id_user);
$stmt_solicitudes->execute();
$result_solicitudes = $stmt_solicitudes->get_result();

$solicitudes_seguimiento = array();
if ($result_solicitudes->num_rows > 0) {
    while ($row_solicitud = $result_solicitudes->fetch_assoc()) {
        $solicitudes_seguimiento[] = array(
            "id_user" => $row_solicitud['id_user'],
            "username" => $row_solicitud['username'],
        );
    }
}

// Consulta preparada para obtener la información del usuario
//echo $id_user;
//exit();

$sql_info_usuario = "SELECT 
                        id,
                        username,
                        status,
                        (SELECT COUNT(*) FROM Posts WHERE id_user = Users.id) AS num_posts,
                        (SELECT COUNT(*) FROM Requests WHERE id_receiver = Users.id AND status = 1) AS followers,
                        (SELECT COUNT(*) FROM Requests WHERE id_sender = Users.id AND status = 1) AS following,
                        (SELECT status FROM Requests WHERE id_receiver = Users.id) AS status1

                    FROM 
                        Users 
                    WHERE 
                        id = ?";
$stmt_info_usuario = $conn->prepare($sql_info_usuario);
$stmt_info_usuario->bind_param("i", $id_user);
$stmt_info_usuario->execute();
$result_info_usuario = $stmt_info_usuario->get_result();

if ($result_info_usuario->num_rows > 0) {
    $row_info_usuario = $result_info_usuario->fetch_assoc();


//echo $auth;
//exit();

    $user = array(
        "id" => $row_info_usuario['id'],
        "username" => $row_info_usuario['username'],
        "num_posts" => $row_info_usuario['num_posts'],
        "followers" => $row_info_usuario['followers'],
        "following" => $row_info_usuario['following'],
        "status1" => $row_info_usuario['status1'],
        "status" => $row_info_usuario['status'],
        "auth" => $auth, // Nuevo campo "auth"
        "requests" => $solicitudes_seguimiento
    );

    echo json_encode($user);
} else {
    echo "No se encontró información del usuario.";
}

// Cerrar la conexión
$stmt_check_account_status->close();
$stmt_check_request_status->close();
$stmt_info_usuario->close();
$stmt_solicitudes->close();
$conn->close();
?>
