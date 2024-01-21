<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        include_once("sql.php");
        include_once("smpt.php");
        require_once("functions.php");
    //Archivo para gestionar la eliminacioón de lo almacenado en la aplicacion por parte de los usuarios
        $conn = createDataBaseConnection();
    //Se reciben y comprueban los parametros por el POST    
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_name"])) {
        $archivo_name = $_POST["file_name"];

        // Directorio donde se almacenan los archivos
        $destiny = "storage/";

        // Ruta completa del archivo
        $file_path = $destiny . $archivo_name;

        // Verificar si el archivo existe antes de intentar eliminarlo
        if (file_exists($file_path)) {
            unlink($file_path); // Elimina el archivo
            
            $response = array("code" => "Archivo eliminado correctamente");
            echo json_encode($response);
            
            //Se eliminan las entradas de dicho archivo en la BBDD tanto en la tabla files como en share
            $user = explode("/", $archivo_name)[0];
            $file_name = explode("/", $archivo_name)[1];
            $sql_delete = "DELETE FROM files WHERE user = '$user' AND name = '$file_name'";
            if ($conn->query($sql_delete) === TRUE) {
                $sql_select = "SELECT id FROM share WHERE file_name = '$file_name' AND file_owner = '$user'";
                $result = $conn->query($sql_select);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                    $sql_delete = "DELETE FROM share WHERE id = '".$row['id']."'";
                    $conn->query($sql_delete);
                    }
            } else {   
            } 
                echo "El archivo se ha eliminado correctamente de la tabla files."; //ok
            } else {
                echo "Error al eliminar información en la tabla files: " . $conn->error; //Excepción
            }
            closeDataBaseConnection($conn);
            
        } else {
            $response = array("code" => "El archivo no existe".$file_path); //Archivo no encontrado
            echo json_encode($response);
        }
    } else {
        $response = array("code" => "Petición no válida"); //POST vacio
        echo json_encode($response);
    }
?>