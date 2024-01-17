<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
require_once("functions.php");
$keys = generateRSA();

$file_name = "archivo.txt";

// Directorio donde se guardó el archivo cifrado
    $source = "storage/dsp0000/" . $file_name;

// Verificar si el archivo existe
if (file_exists($source)) {
    // Descifrar el archivo
    $tmp_file = "storage/dsp0000/enc.enc";
    file_put_contents($tmp_file, AESDecode(file_get_contents($source), "2FA"));

    // Obtener el contenido del archivo temporal
    $tmp_content = file_get_contents($tmp_file);

    // Establecer las cabeceras para la descarga del archivo
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($tmp_content));

    // Enviar el contenido descifrado al navegador
    echo $tmp_content;

    // Eliminar el archivo temporal después de enviar su contenido
    unlink($tmp_file);
    exit;
} else {
    echo "El archivo no existe en el servidor.";
}
?>
