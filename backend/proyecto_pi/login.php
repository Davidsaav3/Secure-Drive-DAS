<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("functions.php");
    include_once("sql.php");
    include_once("smpt.php");
/*
Este archivo hace las comprobaciones pertienentes a la hora de que un usuario inicie sesión en la aplicación
*/
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));
    $sql = "SELECT password, email FROM usuarios WHERE username='".$data->username."'"  ;//Se recupera la contraseña y el email del usuario en la base de datos
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if(checkHash($row['password'], $data->password)){//Se comprueba si la contraseña coincide
            $response = array("code" => 100);
            $mail = $row['email'];
            $passRdy = prepareHashToUse($row["password"]);
            $randomNumber = rand(100000, 999999);
            $sql = "UPDATE usuarios SET cod = '".AESEncoding($randomNumber, $passRdy)."' WHERE username = '".$data->username."'";//Se actualiza la columna cod
            if ($conn->query($sql) === TRUE){
                enviarCorreoDobleFactor(AESDecode($mail, $passRdy), $data->username, $randomNumber);//Se procede al envio del codigo de autentificacion
            }
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