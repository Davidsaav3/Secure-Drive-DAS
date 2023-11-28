<?php

require_once("functions.php");

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
        file_put_contents($archivo_temp, AESEncoding(file_get_contents($archivo_temp), " MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzeNWnOMpylVaUwYNerpW i/1emwt9lAMw42utsv8UUBG9yjsGOzxRd7OvVp89MN3y/rwZskPBXZtYWCRr1nC7 bFn1l97Khy9kqRM8XxEDjZD4h9syvOO4Y940doa2zyx4H/BC8dUZwFKq5kdzG3q2 covZy1HM08219FHzEaxICIr0bU3udIEbNyPMHOAiVmvKk+9LWWwTh/Qi0KKYYJn0 D9Q/05d+MSFK+xYbEPqgO2QovC1TMVxKBu4LjxfblI58zBBUoXZRIow8xUyqNrbI GAjcsJSv++RrYDjAxzcCa0dYs9aPD0jvb3AYHFBovhPMDBYQfjz53cDqVBUPqz3e 7QIDAQAB "));

        move_uploaded_file($archivo_temp, $ruta_destino . $archivo_name);
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