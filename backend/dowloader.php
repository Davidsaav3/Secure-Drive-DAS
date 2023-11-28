<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_name"])) {
    $file_name = $_POST["file_name"];
    $file_path = "storage/" . $file_name;

    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        $response = array("code" => "Archivo no encontrado ".$file_name);
        echo json_encode($response);
    }
} else {
    $response = array("code" => "Solicitud no válida");
    echo json_encode($response);
}
?>
