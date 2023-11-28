<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

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
                $fileInfo = array(
                    "id" => $id,
                    "nombre" => $file,
                    "tamano" => filesize($folderPath . '/' . $file),
                    "tipo" => pathinfo($folderPath . '/' . $file, PATHINFO_EXTENSION),
                    // Puedes agregar más propiedades según tus necesidades
                );
                $fileData[] = $fileInfo;
                $id++;
            }
        }

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
?>