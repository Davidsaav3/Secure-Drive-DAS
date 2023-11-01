<?php
    function createDataBaseConnection(){
    
        return $conn = new mysqli("localhost", "id21448874_pdli", "Covid-19", "id21448874_pdli");
    }

    function closeDataBaseConnection($conn){
        $conn->close();
    }

?>