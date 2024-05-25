<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST, GET");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Referrer-Policy: unsafe-url");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'");
include_once("functions.php");
$key = getConfigVariable('key');
if (isset($_SERVER['HTTP_KEY'])) {
    $api_key = $_SERVER['HTTP_KEY'];
    if ($api_key === $key) {
        echo "Clave API válida";
    } 
    else {
        header("HTTP/1.1 401 Unauthorized");
        echo "Clave API inválida";
        exit();
    }
} 
else {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

include_once("sql.php");
$enc = getConfigVariable('enc');
require __DIR__ . '/vendor/autoload.php';

$config_file = '../includes/db.conf';
$config = parse_ini_file($config_file);
$host = $config['db_host'];
$user = $config['db_username'];
$password = $config['db_password'];
$database = $config['db_name'];

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $backup_file = 'backup_' . date('Ymd_His') . '.sql';
    $backup_path = 'backup/' . $backup_file;

    $dump = '';
    $tables = $pdo->query("SHOW TABLES");
    while ($row = $tables->fetch(PDO::FETCH_NUM)) {
        $table = $row[0];
        $result = $pdo->prepare("SELECT * FROM `{$table}`");
        $result->execute();
        $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $dump .= $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC)['Create Table'] . ";\n";
        foreach ($result as $row) {
            $placeholders = rtrim(str_repeat('?, ', count($row)), ', ');
            $dump .= "INSERT INTO `{$table}` VALUES ({$placeholders});\n";
        }
        $dump .= "\n";
    }

    file_put_contents($backup_path, AESEncoding($dump, $enc));

    //echo "Copia de seguridad realizada con éxito. Archivo guardado en: {$backup_path}";

    // Escanea el directorio de copias de seguridad
    $backup_directory = 'backup/';
    $files = scandir($backup_directory);
    // Elimina todos los archivos, excepto los últimos 5
    $files_to_keep = array_slice($files, -5);
    foreach ($files as $file) {
        if (!in_array($file, $files_to_keep) && $file != '.' && $file != '..') {
            unlink($backup_directory . $file);
        }
    }
} catch (PDOException $e) {
    echo "Error al conectar con la base de datos: " . $e->getMessage();
}
?>
