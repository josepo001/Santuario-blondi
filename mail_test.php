<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar las clases de PHPMailer desde su ubicación actual
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// Crear una instancia de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();                                // Usar SMTP
    $mail->Host = 'smtp.gmail.com';                // Servidor SMTP de Gmail
    $mail->SMTPAuth = true;                        // Habilitar autenticación SMTP
    $mail->Username = 'josebustamantefarias17@gmail.com'; // Tu correo de Gmail
    $mail->Password = 'cplo vbec csro jyrz';       // Contraseña de aplicación de Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Cifrado TLS
    $mail->Port = 587;                             // Puerto para TLS

    // Configuración de remitente y destinatario
    $mail->setFrom('josebustamantefarias17@gmail.com', 'Jose Bustamante'); // Remitente
    $mail->addAddress('pepebusta59.jb9@gmail.com', 'Pepe Busta');         // Destinatario

    // Configuración del contenido del correo
    $mail->isHTML(true);                           // Permitir HTML en el correo
    $mail->Subject = 'Prueba de correo';           // Asunto
    $mail->Body = '<h1>Este es un correo de prueba</h1><p>Enviado desde PHP utilizando PHPMailer.</p>'; // Cuerpo del mensaje
    $mail->AltBody = 'Este es un correo de prueba enviado desde PHP utilizando PHPMailer.'; // Cuerpo alternativo en texto plano

    // Enviar el correo
    $mail->send();
    echo 'Correo enviado correctamente.';
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}
?>
