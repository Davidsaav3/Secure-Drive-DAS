<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    header("Referrer-Policy: unsafe-url");
    include_once("sql.php");

    // Obtener el nombre de usuario del parámetro POST
    $username = $_POST['username'];
    if (!isset($username)) {
        echo "Se requiere el parámetro username.";
        exit();
    }

    // Crear una conexión a la base de datos
    $conn = createDataBaseConnection();

    // Consulta SQL para obtener las publicaciones del usuario utilizando subconsulta
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
            WHERE 
                p.id_user = (SELECT id FROM Users WHERE username = ?)
            GROUP BY 
                p.id;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

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

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
?>
