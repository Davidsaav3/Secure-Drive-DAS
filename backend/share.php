<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

include_once("functions.php");
include_once("sql.php");

    $conn = createDataBaseConnection();

    //Leemos los inputs
    $data = json_decode(file_get_contents('php://input'));
    $files_user = $data->files_user;
    $files_name = $data->files_name;
    $share_user = $data->share_user;

    //Se recupera la clave cifrada que se usa para cifrar el archivo en el servidor
    $sql = "SELECT ckey FROM files WHERE user='".$files_user."' AND name='".$files_name."'"  ;
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $encryptedKey = $row["ckey"];

        //Recuperamos el hash modificado y la clave privada cifrada del servidor del usuario que comparte el archivo
        $sql = "SELECT password, k1 FROM usuarios WHERE username='".$files_user."'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashRdy = prepareHashToUse($row["password"]); //Realizamos las modificaciones pertienentes para tener el hash listo, ya que NO se almacena en limpio en el servidor
            $privateRdy = AESDecode($row["k1"], $hashRdy); //Usando el hash original desciframos usando AES la clave privada
            $decryptedKey = RSAdecode($privateRdy, $encryptedKey); //con la clave privada lista, deciframos la clave que cifra el archivo usando RSA

            //Ahora recuperamos la clave publica del usuario al cual se ha compartido el archivo
            $sql = "SELECT k2 FROM usuarios WHERE username='".$share_user."'";
            $result = $conn->query($sql);
    
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $publicKey = $row["k2"];
                //Ciframos la clave AES con RSA usando la clave publica
                $key2Upload = RSAencoding($publicKey, $decryptedKey);
                
                //Insertamos los datos en la tabla correspondiente
                $sql_insert = "INSERT INTO share (file_name, file_owner, shared_user ,ckey) VALUES ('$files_name', '$files_user', '$share_user' ,'$key2Upload')";
                if ($conn->query($sql_insert) === TRUE) {
                    $response = array("code"  => "Se subio dpm");
                } else {
                    $response = array("code"  => "Error con la bbdd");
                }
            } else {
                $response = array("code" => "No se ha encontrado en la base de datos el archivo");
            }
        } else {
            $response = array("code" => "No se ha encontrado en la base de datos el archivo");
        }


    } else {
        $response = array("code"=> $files_name);
    }
    closeDataBaseConnection($conn);

    echo json_encode($response);
?>