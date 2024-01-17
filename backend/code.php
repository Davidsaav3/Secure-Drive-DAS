<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    require_once("sql.php");
    require_once("functions.php");


    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));

    $sql = "SELECT cod, password FROM usuarios WHERE username='".$data->username."'";
    $result = $conn->query($sql);
    $response = array("code"  => 2000);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $codeDecoded = AESDecode($row["cod"], prepareHashToUse($row["password"]));
        if(strcmp($codeDecoded, $data->fa) == 0){
            $response = array("code"  => 100);
        }else{
            $response = array("code"  => 400);
        }

    } else {
        $response = array("code"  => 401);
    }

    closeDataBaseConnection($conn);

    echo json_encode($response);
?>