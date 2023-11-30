<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    include_once("smpt.php");
    require_once("functions.php");

    $conn = createDataBaseConnection();
    
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_name"])) {
    $archivo_name = $_POST["file_name"];

    // Directorio donde se almacenan los archivos
    $destiny = "storage/";

    // Ruta completa del archivo
    $file_path = $destiny . $archivo_name;

    // Verificar si el archivo existe antes de intentar eliminarlo
    if (file_exists($file_path)) {
        unlink($file_path); // Elimina el archivo
        
        $response = array("code" => "Archivo eliminado correctamente");
        echo json_encode($response);
        
        ////////////////////////////// David //////////////////////////////////////////
        $user = explode("/", $archivo_name)[0];
        $file_name = explode("/", $archivo_name)[1];
        $sql_delete = "DELETE FROM files WHERE user = '$user' AND name = '$file_name'";
        if ($conn->query($sql_delete) === TRUE) {
            echo "El archivo se ha eliminado correctamente de la tabla files.";
        } else {
            echo "Error al eliminar información en la tabla files: " . $conn->error;
        }
        closeDataBaseConnection($conn);
        /////////////////////////////////////////////////////////////////////////////////
        
    } else {
        $response = array("code" => "El archivo no existe".$file_path);
        echo json_encode($response);
    }
} else {
    $response = array("code" => "Petición no válida");
    echo json_encode($response);
}
?>