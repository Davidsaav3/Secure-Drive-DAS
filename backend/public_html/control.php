<?php
// Rutas de los archivos de clave pública y privada
$public_key_file = '../includes/public.pub';
include_once("functions.php");


$key = getConfigVariable('enc');
error_reporting(0);


// Función para verificar la autenticación utilizando el archivo PEM proporcionado
function verifyAuthentication($pem_file, $public_key_file) {

    // Mensaje de prueba para encriptar y luego desencriptar
    $message = "Mensaje de prueba";

    // Cargar la clave pública desde el archivo
    $publicKey = openssl_pkey_get_public(file_get_contents($public_key_file));

    // Verificar si la clave pública se ha cargado correctamente
    if ($publicKey === false) {
        die('No se pudo cargar la clave pública');
    }

    // Encriptar el mensaje con la clave pública
    if (openssl_public_encrypt($message, $encrypted, $publicKey)) {
        // Desencriptar el mensaje con la clave privada
        $p_key = file_get_contents($pem_file);
        if (openssl_private_decrypt($encrypted, $decrypted, $p_key)) {
            // Verificar si los datos desencriptados coinciden con el mensaje original
            if ($decrypted === $message) {
                return true;
            } else {
                return false;
            }
        } else {
            echo 'Error1';
        }
    } else {
        echo 'Error2';
    }

}

// Verificar si se ha enviado el formulario con el archivo PEM
if (isset($_FILES['pem_file'])) {
    $pem_file = $_FILES['pem_file']['tmp_name'];

    // Verificar la autenticación con el archivo PEM proporcionado
    if (verifyAuthentication($pem_file, $public_key_file)) {
        // Autenticación exitosa, ahora se pueden mostrar los archivos disponibles
        
        // Rutas de los directorios de archivos de backup y logs
        $backup_directory = 'backup/';
        $logs_directory = 'logs/';

        // Mostrar los archivos de backup disponibles
        echo "<h2>Archivos de Backup:</h2>";
        echo "<ul>";
        $backup_files = scandir($backup_directory);
        foreach ($backup_files as $file) {
            if ($file != '.' && $file != '..') {
                // Llamar a AESDecode antes de la descarga
                $decoded_content = AESDecode(file_get_contents("$backup_directory/$file"), $key);
                // Crear un enlace para descargar el archivo descifrado
                echo "<li><a href='data:text/plain;charset=utf-8;base64," . base64_encode($decoded_content) . "' download>$file</a></li>";
            }
        }
        echo "</ul>";

        // Mostrar los archivos de logs disponibles
        echo "<h2>Archivos de Logs:</h2>";
        echo "<ul>";
        $logs_files = scandir($logs_directory);
        foreach ($logs_files as $file) {
            if ($file != '.' && $file != '..') {
                // Llamar a AESDecode antes de la descarga
                $decoded_content = AESDecode(file_get_contents("$logs_directory/$file"), $key);
                // Crear un enlace para descargar el archivo descifrado
                echo "<li><a href='data:text/plain;charset=utf-8;base64," . base64_encode($decoded_content) . "' download>$file</a></li>";
            }
        }
        echo "</ul>";
    } else {
        echo "Autenticación fallida. Por favor, verifica tu archivo PEM.";
    }
} else {
    // Si no se ha enviado el formulario, redirigir a access.php
    header("Location: access.php");
    exit();
}
?>
