<?php
    function createDataBaseConnection(){
        $config_file = '../includes/db.conf';
        $config = parse_ini_file($config_file);
        return $conn = new mysqli($config['db_host'], $config['db_username'], $config['db_password'], $config['db_name']);
    }

    function closeDataBaseConnection($conn){
        $conn->close();
    }

?>