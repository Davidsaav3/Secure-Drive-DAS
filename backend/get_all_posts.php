<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Referrer-Policy: unsafe-url");
    include_once("sql.php");
    
    $id_user = $_POST['id_user'];
    if (!isset($id_user)) {
        echo "Se requiere el parámetro id_user.";
        exit();
    }
    
    $conn = createDataBaseConnection();
    $sql = "SELECT 
                url_image, 
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
            INNER JOIN 
                Requests s ON p.id_user = s.id_receiver
            WHERE 
                s.id_sender = $id_user AND s.status = 1
            GROUP BY 
                p.id";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $publicaciones = array();

        while($row = $result->fetch_assoc()) {
            $row['comments'] = json_decode('[' . $row['comments'] . ']', true);
            $publicaciones[] = $row;
        }
        echo json_encode($publicaciones);
    } 
    else {
        echo "No se encontraron publicaciones para el usuario.";
    }
    
    $conn->close();
?>