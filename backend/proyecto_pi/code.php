<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    require_once("sql.php");
    require_once("functions.php");
    /*
    //Este archivo sirve para realizar la comprobación del codigo de verificación cuando un usuario se registra o accede a la aplicación.
    //Como lo que se almacena es un código cifrado debemos descifrarlo para comprarlo con el que recuperamos
    //de la base de datos.
    */
    $conn = createDataBaseConnection();
    $data = json_decode(file_get_contents('php://input'));//Recogemos el imput

    $sql = "SELECT cod, password FROM usuarios WHERE username='".$data->username."'";//Seleccionamos el codigo a comprobar
    $result = $conn->query($sql);
    $response = array("code"  => 200);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $codeDecoded = AESDecode($row["cod"], prepareHashToUse($row["password"]));//Se descifra usando el hash en limpio, el cual necesita las transformaciones pra llegar a ese estado
        if(strcmp($codeDecoded, $data->fa) == 0){
            $response = array("code"  => 100); //OK
        }else{
            $response = array("code"  => 400); //Codigo incorrecto
        }

    } else {
        $response = array("code"  => 401); //Error al recuperar el codigo de la base de datos
    }

    closeDataBaseConnection($conn);

    echo json_encode($response);
?>