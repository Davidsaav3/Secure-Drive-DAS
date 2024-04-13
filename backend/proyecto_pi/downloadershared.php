<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
include_once("functions.php");
include_once("sql.php");

//Comprobamos que recibimos una peticion POST con los parametros deseados
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_name"])) {
    //Recuperamos el nombre del archivo
    $file_name = $_POST["file_name"];
    $file_path = "storage/" . $file_name;
    $file_owner = $_POST["file_owner"];
    $file_shared = $_POST["file_shared"];
    //Si existe el archivo en el servidor seguimos
    if (file_exists($file_path)) {
        // Comprobamos que también exista en la base de datos y obtenemos su clave
        $conn = createDataBaseConnection();
        $user = explode("/", $file_path)[1];
        $file = explode("/", $file_name)[1];
        $sql = "SELECT ckey, vsignature FROM share WHERE file_owner='".$user."' AND file_name='".$file."' AND shared_user='".$file_shared."'";
        $result = $conn->query($sql);
        //De existir, recuperamos la clave que se almacena CIFRADA en la base de datos y la firma
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $encryptedKey = $row["ckey"];
            $signature = $row["vsignature"];
            //Antes de seguir con el proceso lo primero que se conprueba es la firma digital
            $sql = "SELECT k2 FROM usuarios WHERE username='".$user."'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            if(VSignCheck($encryptedKey, $signature, $row["k2"])){
                //Recuperamos el hash modificado y la clave privada cifrada del servidor
                $sql = "SELECT password, k1 FROM usuarios WHERE username='".$file_shared."'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $hashRdy = prepareHashToUse($row["password"]); //Realizamos las modificaciones pertienentes para tener el hash listo, ya que NO se almacena en limpio en el servidor
                    $privateRdy = AESDecode($row["k1"], $hashRdy); //Usando el hash original desciframos usando AES la clave privada
                    $decryptedKey = RSAdecode($privateRdy, $encryptedKey); //con la clave privada lista, deciframos la clave que cifra el archivo usando RSA

                    // Desciframos el archivo usando la clave descifrada y el algoritmo AES-256-GCM
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
                    exit();
                } else {
                    $response = array("code" => "No se ha encontrado en la base de datos el archivo");
                }
            }
        } else {
            $response = array("code" => "No se ha encontrado en la base de datos el archivo");
        }
    } else {
        $response = array("code" => "Archivo no encontrado ".$file_name);
        echo json_encode($response);
    }
} else {
    $response = array("code" => "Solicitud no válida");
    echo json_encode($response);
}
?>
