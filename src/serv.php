<?php
    $servername = "localhost";
    $username = "root";
    $password = "";

    // Create connection
    $conn = new mysqli($servername, $username, $password);
    $data = json_decode(file_get_contents('php://input'));


    $sql = "INSERT INTO usuarios (username, password, email, k1, k2)
    VALUES ('".$data->username."', '".password_hash($data->password, PASSWORD_BCRYPT)."', '".$data->email."', a, b)";

    if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
    } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
    //echo json_encode($data);
?>