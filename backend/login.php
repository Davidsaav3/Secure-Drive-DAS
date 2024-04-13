<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("functions.php");
    include_once("sql.php");

    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    
    $sql = "SELECT id, password, username FROM Users WHERE username='".$data->username."'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if(checkHash($row['password'], $data->password)){// Se comprueba si la contraseña coincide
            $response = array("code" => 100, "id" => $row['id'], "username" => $row['username']); // Devolver el id y el username
        }
        else{
            $response = array("code" => 400);
        }
    } else {
        $response = array("code"=> 401);
    }
    
    closeDataBaseConnection($conn);
    echo json_encode($response);
?>
