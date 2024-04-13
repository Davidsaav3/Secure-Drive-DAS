<?php
    function createDataBaseConnection(){
    
        return $conn = new mysqli("localhost", "id22036088_david", "Das2024@", "id22036088_uabook");
    }

    function closeDataBaseConnection($conn){
        $conn->close();
    }

?>