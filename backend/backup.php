<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    include_once("functions.php");
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
            $result = $pdo->query("SELECT * FROM `{$table}`");
            $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $dump .= $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC)['Create Table'] . ";\n";
            foreach ($result as $row) {
                $row = array_map(array($pdo, 'quote'), $row);
                $dump .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $row) . ");\n";
            }
            $dump .= "\n";
        }
    
        file_put_contents($backup_path, $dump);
    
        echo "Copia de seguridad realizada con éxito. Archivo guardado en: {$backup_path}";
    } catch (PDOException $e) {
        echo "Error al conectar con la base de datos: " . $e->getMessage();
    }
?>