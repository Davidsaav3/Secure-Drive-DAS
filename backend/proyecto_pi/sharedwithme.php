<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
require_once("sql.php");
$conn = createDataBaseConnection();

/*
//Se seleccionan los archivos que se han compartido con el usuario usando una sentencia SQL para mostrarselos
*/
$sql = "SELECT file_name, file_owner FROM share WHERE shared_user='".$_POST["username"]."'"  ;
$result = $conn->query($sql);
$response = array();
if ($result->num_rows > 0) {
    $fileData = array();
    $id = 0;
    while ($row = $result->fetch_assoc()) {//Por cada archivo que le han compartido se agrega a un array que se pasa al front end
        $filePath = 'storage/' . $row['file_owner'] . '/' . $row['file_name'];
        if (file_exists($filePath)) {
            $tmp_file = "storage/dsp0000/enc.enc";
            $fileContent = base64_encode($filePath);
            $fileContent = '';
            $fileInfo = array(
                "id" => $id,
                "nombre" => $row['file_name'],
                "tamano" => filesize($filePath),
                "tipo" => pathinfo($filePath, PATHINFO_EXTENSION),
                "url" => get_file_url($filePath),
                "archivo" => $fileContent, // Agregar el contenido del archivo,
                "owner" => $row['file_owner']
            );
            $fileData[] = $fileInfo;
            $id++;
        } 
        else {
            array_push($response, "El archivo no existe en la ruta especificada");
        }
    }
    $response = array("archivos" => $fileData);
} else {
    $response = array("code" => 400);//no se han compartido archivos contigo   
} 
closeDataBaseConnection($conn);
echo json_encode($response);

function get_file_url($file) {
    // Construir la URL según la estructura de tu aplicación Angular
    // Puedes cambiar esta lógica según la configuración de tu servidor y tu aplicación Angular
    $angular_base_url = "https://proteccloud.000webhostapp.com/";
    $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);
    return $angular_base_url . $relative_path;
}
?>