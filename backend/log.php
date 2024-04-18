<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    include_once("sql.php");
    require_once("vendor/autoload.php"); // Incluir la biblioteca JWT
    
    require __DIR__ . '/vendor/autoload.php';
    $conn = createDataBaseConnection();
    
    session_start();
    
    // Verificar credenciales (por ejemplo, nombre de usuario y contraseña)
    // Aquí deberías tener tu lógica de autenticación de usuario
    
    // Si las credenciales son válidas, establecer variables de sesión y registrar evento de inicio de sesión
    if ($credenciales_validas) {
        // Establecer variables de sesión o cookies según sea necesario
        $_SESSION['usuario_id'] = $usuario_id;
    
        // Registrar evento de inicio de sesión en la base de datos
        $login_time = date('Y-m-d H:i:s');
        $IP_address = $_SERVER['REMOTE_ADDR']; // Obtener la dirección IP del usuario
        $query = "INSERT INTO Log (user_id, login_time, IP_address) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("iss", $usuario_id, $login_time, $IP_address);
        $stmt->execute();
        $stmt->close();
    
        // Redirigir al usuario a la página de inicio
        header("Location: inicio.php");
        exit();
    } else {
        // Las credenciales son inválidas, mostrar mensaje de error o redirigir a la página de inicio de sesión
        header("Location: entrar.php?error=1");
        exit();
    }
?>