<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include_once("sql.php");
require_once("functions.php");
$conn = createDataBaseConnection();

/*
Se hacen consultas a la base de datos para dejar de compartir los archivos, estas consultas gestionan el borrado de las entradas en la BBDD relacionadas con compartir archivos
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_name"])) {
    $archivo_name = $_POST["file_name"];

    $user = explode("/", $archivo_name)[0];
    $file_name = explode("/", $archivo_name)[1];
    $num = explode("/", $archivo_name)[2];
    $user2 = explode("/", $archivo_name)[3];

    if($num==0){
        $sql_delete = "DELETE FROM share WHERE file_owner = '$user' AND file_name = '$file_name' AND shared_user = '$user2'";
    }
    else{
        $sql_delete = "DELETE FROM share WHERE shared_user = '$user' AND file_name = '$file_name' AND file_owner = '$user2'";
    }
    if ($conn->query($sql_delete) === TRUE) {
        echo "El archivo se ha eliminado correctamente de la tabla share.";
    } else { 
        echo "Error al eliminar información en la tabla files: " . $conn->error;
    }
    closeDataBaseConnection($conn);
    
    $response = array("code" => "Archivo eliminado correctamente");
    echo json_encode($response);
    //

} else {
    $response = array("code" => "Petición no válida");
    echo json_encode($response);
}
?>