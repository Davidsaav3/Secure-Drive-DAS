<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("functions.php");
    include_once("sql.php");
    include_once("smpt.php");

    //400 usuario existe 100 todo ok 401 otro problema

    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));

    //Se comprueba si el usuario existe
    $sql = "SELECT * FROM usuarios WHERE username='".$data->username."'";
    $result = $conn->query($sql);
    $debug = array();

    if ($result->num_rows > 0) {
        $response = array("code" => 400); //El nombre del usuario esta en uso
    } else {//Se acepta el registro, se generan y gestionan las claves del usuario, el código de autentificaion. Todo se sube de manera protegida
        $randomNumber = rand(100000, 999999);
        $keysToUse = generateRSA();
        $ogHash    = generateHash($data->password);
        $pvKey = AESEncoding($keysToUse["private"], $ogHash);
        $sql2 = "INSERT INTO usuarios (username, password, email, k1, k2, cod)
        VALUES ('".$data->username."', '".prepareHashToUpload($ogHash)."', '".AESEncoding($data->email, $ogHash)."', '".$pvKey."', '".$keysToUse["public"]."', '".AESEncoding($randomNumber, $ogHash)."')";
        if ($conn->query($sql2) === TRUE) {
            $response = array("code"  => 100);
            enviarCorreoDobleFactor($data->email, $data->username, $randomNumber);
        } else {
            $response = array("code"  => 401);
        }
    }

    closeDataBaseConnection($conn);

    echo json_encode($response);

?>