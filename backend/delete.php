<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

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
    } else {
        $response = array("code" => "El archivo no existe");
        echo json_encode($response);
    }
} else {
    $response = array("code" => "Petición no válida");
    echo json_encode($response);
}
?>