<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include_once("functions.php");
include_once("sql.php");

//if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_name"])) {
    //$file_name = $_POST["file_name"];
    $file_name ="archivo.txt";
    $file_path = "storage/dsp/archivo.txt";

    if (file_exists($file_path)) {
        // Comprobamos que también exista en la base de datos y obtenemos su clave
        $conn = createDataBaseConnection();
        $user = explode("/", $file_path)[1];
        $sql = "SELECT ckey FROM files WHERE user='".$user."' AND name='".$file_name."'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $encryptedKey = $row["ckey"];

            $sql = "SELECT password, k1 FROM usuarios WHERE user='".$user."'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashRdy = prepareHashToUse($row["password"]);
                $privateRdy = AESDecode($row["k1"], $hashRdy);
                $decryptedKey = RSAdecode($privateRdy, $encryptedKey);

                // No es necesario crear un archivo temporal
                $decryptedContent = AESDecode(file_get_contents($file_path), $decryptedKey);

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $file_name . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . strlen($decryptedContent)); // Usar la longitud del contenido descifrado

                // Enviar el contenido descifrado directamente al navegador
                echo $decryptedContent;

                // Opcionalmente, puedes limpiar la salida del búfer y finalizar el script
                ob_clean();
                exit();
            } else {
                $response = array("code" => "No se ha encontrado en la base de datos el archivo");
            }
        } else {
            $response = array("code" => "No se ha encontrado en la base de datos el archivo");
        }
    } else {
        $response = array("code" => "Archivo no encontrado ".$file_name);
        echo json_encode($response);
    }-
?>
