<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("functions.php");
    include_once("sql.php");

    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));

    $sql = "SELECT * FROM Users WHERE username='".$data->username."'";
    $result = $conn->query($sql);
    $debug = array();

    if ($result->num_rows > 0) {
        $response = array("code" => 400); //El nombre del usuario esta en uso
    } 
    else { 
        $randomNumber = rand(100000, 999999);
        $keysToUse = generateRSA();
        $ogHash    = generateHash($data->password);
        $pvKey = AESEncoding($keysToUse["private"], $ogHash);
        $sql2 = "INSERT INTO Users (username, password, status)
        VALUES ('".$data->username."', '".prepareHashToUpload($ogHash)."', '0')";
        if ($conn->query($sql2) === TRUE) {
            $response = array("code"  => 100);
        } 
        else {
            $response = array("code"  => 401);
        }
    }

    closeDataBaseConnection($conn);
    echo json_encode($response);

?>