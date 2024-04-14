<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Referrer-Policy: unsafe-url");
    include_once("sql.php");
    require_once("vendor/autoload.php"); 

    $id_user = $_POST['id_user'];
    if (!isset($id_user)) {
        echo "Se requiere el parámetro id_user.";
        exit();
    }
    
    $token = $_POST['token'];
    if (!$token) {
        echo json_encode(array("mensaje" => "Token de autorización no proporcionado"));
        exit();
    }
    try {
        $key = 'your_secret_key';
        $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($key, 'HS256'));   
        $id_user = $decoded->id;
    } 
    catch (Exception $e) {
        echo json_encode(array("mensaje" => "Token de autorización inválido"));
        exit();
    }
    
    $conn = createDataBaseConnection();
    $sql = "SELECT 
                url_image, 
                p.date,
                p.id AS id_post,
                p.text AS text_post,
                p.date,
                u1.username AS username,
                COUNT(l.id) AS likes,
                GROUP_CONCAT(JSON_OBJECT('id', c.id, 'username', u2.username, 'text', c.text)) AS comments
            FROM 
                Posts p
            INNER JOIN 
                Users u1 ON p.id_user = u1.id
            LEFT JOIN 
                Likes l ON p.id = l.id_post
            LEFT JOIN 
                Comments c ON p.id = c.id_post
            LEFT JOIN 
                Users u2 ON c.id_user = u2.id
            WHERE 
                p.id_user IN (
                    SELECT id_receiver FROM Requests WHERE id_sender = ? AND status = 1
                    UNION
                    SELECT ?
                )
            GROUP BY 
                p.id ORDER BY p.date DESC;";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_user, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $publicaciones = array();
    
        while ($row = $result->fetch_assoc()) {
            $row['comments'] = json_decode('[' . $row['comments'] . ']', true);
            $publicaciones[] = $row;
        }
        echo json_encode($publicaciones);
    } else {
        echo "No se encontraron publicaciones para el usuario.";
    }
    
    $stmt->close();
    $conn->close();
?>
