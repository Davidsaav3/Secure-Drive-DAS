<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("functions.php");
    include_once("sql.php");
    include_once("smpt.php");

    $conn = createDataBaseConnection();

// Directorio para guardar
$destiny = "storage/";
$response = array();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["uploaded_file"])) {
    $archivo_name = $_FILES["uploaded_file"]["name"];
    $archivo_temp = $_FILES["uploaded_file"]["tmp_name"];
    $archivo_size = $_FILES["uploaded_file"]["size"];
    $archivo_type = $_FILES["uploaded_file"]["type"];
    $archivo_error = $_FILES["uploaded_file"]["error"];

    // Recibir la ruta desde Angular
    $ruta = isset($_POST["ruta"]) ? $_POST["ruta"] : '';
    // Se cifra el archivo y se sube al servidor
    if ($archivo_error === UPLOAD_ERR_OK) {
        // Combina la ruta con el directorio de destino
        $ruta_destino = $destiny . $ruta . '/';
        
        // Crea el directorio si no existe
        if (!is_dir($ruta_destino)) {
            mkdir($ruta_destino, 0777, true);
        }
        //Obtenemos el nombre del usuario
        $user = explode("/", $ruta)[0];
        //Generamos una clave AES para cifrar
        $key4Encrypt = random_bytes(32);
        //Se inicia la conexion con la base de datos y se recupera la clave publica del usuario
        $conn = createDataBaseConnection();
        $sql = "SELECT k2 FROM usuarios WHERE username='".$user."'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $publicKey = $row["k2"];
        }
        else{
            $response = array("code"  => 401);
        }
        //Ciframos la clave AES con RSA usando la clave publica
        $key2Upload = RSAencoding($publicKey, $key4Encrypt);
        
        ////////////////////////////// David //////////////////////////////////////////
        // Inserción en la tabla files
        $sql_insert = "INSERT INTO files (user, name, ckey) VALUES ('$user', '$archivo_name', '$key2Upload')";
        if ($conn->query($sql_insert) === TRUE) {
            // Mueve el archivo al directorio de destino cifrado con la clave generada anteriormente andes de ser cifrada con la clave publica
            file_put_contents($archivo_temp, AESEncoding(file_get_contents($archivo_temp), $key4Encrypt));
            move_uploaded_file($archivo_temp, $ruta_destino . $archivo_name);
            $response = array("code"  => 201);
        } else {
            $response = array("code"  => 402);
        }
        closeDataBaseConnection($conn);
        //////////////////////////////////////////////////////////////////////////////
    } else {
        $response = array("code"  => 401);
    }
} else {
    $response = array("code"  => 401);
}

echo json_encode($response);
?>