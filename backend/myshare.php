<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
require_once("functions.php");
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json'); // Agregar esta línea para indicar que se espera un contenido JSON
require_once("sql.php");
$conn = createDataBaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folderPath = $_POST["folder_path"];

    // Verificar si la carpeta existe y es accesible
    if (is_dir($folderPath)) {
        $files = scandir($folderPath);
        // Eliminar los elementos "." y ".." de la lista
        $files = array_diff($files, array('.', '..'));

        $fileData = array();
        $id = 0;

        foreach ($files as $file) {
            if (!is_dir($file)) {  // Excluir directorios del listado
                $filePath = $folderPath . '/' . $file;
                if (file_exists($filePath)) {
                    $tmp_file = "storage/dsp0000/enc.enc";
                    //$tmp_content = file_get_contents($tmp_file);
                    $fileContent = base64_encode($filePath);
                    $fileContent = '';
                    $sql = "SELECT shared_user FROM share WHERE file_name='".$file."' AND file_owner='".$_POST["username"]."'";
                    $result = $conn->query($sql);
                      if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $fileInfo = array(
                                "id" => $id,
                                "nombre" => $file,
                                "tamano" => filesize($filePath),
                                "tipo" => pathinfo($filePath, PATHINFO_EXTENSION),
                                "url" => get_file_url($filePath),
                                "archivo" => $fileContent, // Agregar el contenido del archivo,
                                "shared_user" => $row['shared_user']
                            );
                            $fileData[] = $fileInfo;
                            $id++;
                        }
                    } else {   
                    } 
                   
                } 
                else {
                    array_push($response, "El archivo no existe en la ruta especificada");
                }
                
            }
        }
        closeDataBaseConnection($conn);
        $response = array("archivos" => $fileData);
        echo json_encode($response);
    } else {
        $response = array("code" => "Carpeta no encontrada");
        echo json_encode($response);
    }
} else {
    $response = array("code" => "Método no permitido");
    echo json_encode($response);
}

function get_file_url($file) {
    // Construir la URL según la estructura de tu aplicación Angular
    // Puedes cambiar esta lógica según la configuración de tu servidor y tu aplicación Angular
    $angular_base_url = "https://proteccloud.000webhostapp.com/";
    $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
    return $angular_base_url . $relative_path;
}
?>