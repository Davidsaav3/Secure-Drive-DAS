<?php
 header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("functions.php");
    include_once("sql.php");
    include_once("smpt.php");
    require_once("functions.php");

    $conn = createDataBaseConnection();

// Directorio para guardar
$destiny = "storage/";

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

        // Mueve el archivo al directorio de destino
        file_put_contents($archivo_temp, AESEncoding(file_get_contents($archivo_temp), "2FA"));

        move_uploaded_file($archivo_temp, $ruta_destino . $archivo_name);
        
        ////////////////////////////// David //////////////////////////////////////////
        $user = explode("/", $ruta)[0];
        $file_name = explode("/", $ruta)[1];
        $file_key = "000";
        // Inserción en la tabla files
        $conn = createDataBaseConnection();
        $sql_insert = "INSERT INTO files (user, name, key) VALUES ('$user', '$file_name', '$file_key')";
        if ($conn->query($sql_insert) === TRUE) {
            echo "El archivo se ha subido correctamente y la información se ha guardado en la tabla files.";
        } else {
            echo "Error al insertar información en la tabla files: " . $conn->error;
        }
        closeDataBaseConnection($conn);
        //////////////////////////////////////////////////////////////////////////////

        echo "El archivo se ha subido correctamente.";
    } else {
        echo "Error al subir el archivo.";
    }
} else {
    echo "No se ha subido ningún archivo.";
}

/*$newname = dirname(__FILE__).'/upload/'.$_FILES['uploaded_file']['name'].'.crypto';
$tempfile = $_FILES['uploaded_file']['tmp_name'];
if (!file_exists($newname)) {
    $ALGORITHM = 'AES-128-CBC';
    $IV    = '12dasdq3g5b2434b';
    $password = '123456';
    file_put_contents($tempfile, openssl_encrypt(file_get_contents($tempfile), $ALGORITHM, $password, 0, $IV));
    if (move_uploaded_file($tempfile,$newname)) {
        echo 'It\'s done! The file has been saved.';
    } else {
        echo 'Error: A problem occurred during file upload!';
    }
} else {
    echo 'Error: File '.$_FILES['uploaded_file']['name'].' already exists';
}*/




?>