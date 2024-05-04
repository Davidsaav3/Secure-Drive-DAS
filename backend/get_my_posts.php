<?php
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: POST");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header("Referrer-Policy: unsafe-url");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
//header("Content-Security-Policy: default-src 'self'");
    
    $allowed_domains = array(
        'http://localhost:4200',
        'https://uabook-81dcf.web.app'
    );
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if (in_array($origin, $allowed_domains)) {
        header("Access-Control-Allow-Origin: $origin");
    } 
    else {
        header("HTTP/1.1 403 Forbidden");
        exit();
    }
    
    include_once("sql.php");
    require_once("vendor/autoload.php");
    include_once("functions.php");

    $key = getConfigVariable('enc');

    // Crear una conexión a la base de datos
    $conn = createDataBaseConnection();
    
    // Obtener el nombre de usuario del parámetro POST
    $username = $_POST['username'];
    if (!isset($username)) {
        echo "Se requiere el parámetro username.";
        exit();
    }
    
    $token = $_POST['token'];
    if (!$token) {
        echo json_encode(array("mensaje" => "Token de autorización no proporcionado"));
        exit();
    }
    try {
        $sql_check_password = "SELECT password FROM Users WHERE id=?";
        $stmt_check_password = $conn->prepare($sql_check_password);
        $stmt_check_password->bind_param("s", $id_user);
        $stmt_check_password->execute();
        $result_check_password = $stmt_check_password->get_result();
        if ($result_check_password->num_rows > 0) {
            $row = $result_check_password->fetch_assoc();
            $hash = prepareHashToUse($row['password']);
            $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($hash, 'HS256'));   
            $id_user_from_token = $decoded->id;

            if ($id_user != $id_user_from_token) {
                echo json_encode(array("mensaje" => "El ID de usuario no coincide con el token."));
                exit();
            }  
        } 
        else {
            $response = array("code"  => 401); // Error con la base de datos
        }
    } 
    catch (Exception $e) {
        echo json_encode(array("mensaje" => "Token de autorización inválido"));
        exit();
    }

    // Consulta SQL para obtener las publicaciones del usuario utilizando subconsulta
    $sql = "SELECT 
            url_image, 
            p.date,
            p.id AS id_post,
            p.text AS text_post,
            p.date,
            u1.username AS username,
            (SELECT COUNT(*) FROM Likes l WHERE l.id_post = p.id) AS likes,
            GROUP_CONCAT(JSON_OBJECT('id', c.id, 'username', u2.username, 'text', c.text)) AS comments
        FROM 
            Posts p
        INNER JOIN 
            Users u1 ON p.id_user = u1.id
        LEFT JOIN 
            Comments c ON p.id = c.id_post
        LEFT JOIN 
            Users u2 ON c.id_user = u2.id
        WHERE 
            p.id_user = (SELECT id FROM Users WHERE username = ?)
        GROUP BY 
            p.id ORDER BY p.date DESC;
        ";
                
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $publicaciones = array();

        while($row = $result->fetch_assoc()) {
            $row['comments'] = json_decode('[' . $row['comments'] . ']', true);

            $file_path = AESDecode($row['url_image'], $key);
            if (file_exists($file_path)) {
                // Desciframos el archivo
                $decryptedContent = AESDecode(file_get_contents($file_path), $key);
                //Pasamos la imagen a base64
                $base64Content = base64_encode($decryptedContent);
                //Asignamos la imagen al array
                $row['image'] = $base64Content;
            } else {
                $row['image'] = 'ImageNotFound';
            }

            unset($row['url_image']);
            $publicaciones[] = $row;
        }
        echo json_encode($publicaciones);
        
    } 
    else {
        echo "No se encontraron publicaciones para el usuario.";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
?>
