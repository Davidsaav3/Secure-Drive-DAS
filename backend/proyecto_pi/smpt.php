
<?php
    require 'vendor/autoload.php'; // Asegúrate de reemplazar 'vendor/autoload.php' con la ruta real a tu archivo autoload si es necesario
   use PHPMailer\PHPMailer\PHPMailer;
   use PHPMailer\PHPMailer\Exception;
   
   function enviarCorreoDobleFactor($correoDestino, $nombreUsuario, $rand) {
        
     
        $mail = new PHPMailer(true);
    
        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host = 'smtp.office365.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'protecciondli2023@outlook.es';
            $mail->Password = 'PDLI-2023';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            // Configuración del mensaje
            $mail->setFrom('protecciondli2023@outlook.es', 'Proteccion de la informacion Drive');
            $mail->addAddress($correoDestino, $nombreUsuario);
            $mail->isHTML(true);
            $mail->Subject = 'Doble factor';
            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <title>Código de verificación de doble factor</title>
            </head>
            <body>
                <h2>Autenticación de doble factor</h2>
                <p>Estimado '. $nombreUsuario .', Aquí tienes tu código de verificación de doble factor:</p>
                <h1>'. $rand.'</h1>
                <p>Por favor, ingresa este código en el formulario de autenticación de doble factor para completar el proceso de verificación.</p>
                <p>Si no estás intentando acceder, por favor ignora este mensaje.</p>
                <p>Gracias</p>
            </body>
            </html>';

            $mail->send();
            return 'El mensaje ha sido enviado';
        } catch (Exception $e) {
            return "Error al enviar el mensaje: {$mail->ErrorInfo}";
        }
    }


?>