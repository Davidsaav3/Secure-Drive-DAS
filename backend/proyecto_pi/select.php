<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include_once("functions.php");
include_once("sql.php");
include_once("smpt.php");

$conn = createDataBaseConnection();

// Verificar si se proporcionó el campo 'username' en la solicitud POST
if (isset($_POST['username'])) {
    $usernameToExclude = $_POST['username'];

    // Obtener todos los usernames excepto el proporcionado
    $sql = "SELECT username FROM usuarios WHERE username <> '" . $usernameToExclude . "'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $usernames = array();

        // Agregar cada username al array
        while ($row = $result->fetch_assoc()) {
            $usernames[] = $row['username'];
        }

        // Devolver el array de usernames en formato JSON
        $response = array("code" => 200, "usernames" => $usernames);
    } else {
        $response = array("code" => 404, "usernames" => array());
    }
} else {
    $response = array("code" => 400, "message" => "El campo 'username' es obligatorio en la solicitud POST.");
}

closeDataBaseConnection($conn);

echo json_encode($response);
?>
