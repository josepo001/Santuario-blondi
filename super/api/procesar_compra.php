<?php
session_start();
require_once '../../Admin/DB.php'; // Ajusta la ruta según tu proyecto
require_once '../../PHPMailer/src/PHPMailer.php';
require_once '../../PHPMailer/src/SMTP.php';
require_once '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configurar el encabezado para devolver JSON
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
        exit;
    }

    // Verificar si se envió el número de tarjeta
    $numero_tarjeta = trim($_POST['numero_tarjeta'] ?? '');
    if (empty($numero_tarjeta)) {
        echo json_encode(['status' => 'error', 'message' => 'Debe ingresar un número de tarjeta válido.']);
        exit;
    }

    // Verificar si el carrito no está vacío
    $carrito = $_SESSION['carrito'] ?? [];
    if (empty($carrito)) {
        echo json_encode(['status' => 'error', 'message' => 'El carrito está vacío.']);
        exit;
    }

    // Conectar a la base de datos
    $db = new Database();
    $sql = "SELECT id, nombre FROM tarjetas WHERE numero = ?";
    $result = $db->query($sql, [$numero_tarjeta]);

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Número de tarjeta inválido.']);
        exit;
    }

    // Obtener datos de la tarjeta
    $tarjeta = $result->fetch_assoc();
    $tarjeta_id = $tarjeta['id'];
    $nombre_tarjeta = $tarjeta['nombre'];

    $detalles_compra = "";
    $total_compra = 0;

    // Procesar cada producto del carrito
    foreach ($carrito as $producto) {
        if (!isset($producto['nombre']) || !isset($producto['precio'])) {
            continue; // Omitir si el producto no tiene datos válidos
        }

        // Insertar en la base de datos
        $sql = "INSERT INTO historial_compras_super (producto_id, cantidad, precio_total, fecha, tarjeta_id) VALUES (?, ?, ?, NOW(), ?)";
        $db->query($sql, [
            $producto['id'], 
            1, 
            $producto['precio'], 
            $tarjeta_id
        ]);

        // Calcular utilidad
        $ingreso = 0;
        $egreso = $producto['precio'];
        $utilidad = $ingreso - $egreso;

        $descripcion = "Compra de {$producto['nombre']} con {$nombre_tarjeta}";
        $sql_transaccion = "INSERT INTO transacciones_diarias (fecha, ingresos, egresos, utilidad, tarjeta_id, descripcion) VALUES (NOW(), ?, ?, ?, ?, ?)";
        $db->query($sql_transaccion, [$ingreso, $egreso, $utilidad, $tarjeta_id, $descripcion]);

        // Agregar detalles al correo
        $detalles_compra .= "<li>Producto: " . htmlspecialchars($producto['nombre']) . " - Precio: $" . number_format($producto['precio'], 0, ',', '.') . "</li>";
        $total_compra += $producto['precio'];
    }

    // Vaciar el carrito
    $_SESSION['carrito'] = [];

    // Configurar PHPMailer para enviar el correo
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'josebustamantefarias17@gmail.com'; // Cambia por tu correo
        $mail->Password = 'cplo vbec csro jyrz'; // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('josebustamantefarias17@gmail.com', 'Tu Nombre');
        $mail->addAddress('pepebusta59.jb9@gmail.com', 'Pepe Busta'); // Cambia por el destinatario

        $mail->isHTML(true);
        $mail->Subject = "Nueva compra registrada con la tarjeta ID $tarjeta_id";
        $mail->Body = "
            <h1>Nueva compra registrada</h1>
            <p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p><strong>Tarjeta:</strong> $nombre_tarjeta (ID: $tarjeta_id)</p>
            <p><strong>Detalles de la compra:</strong></p>
            <ul>
                $detalles_compra
            </ul>
            <p><strong>Total:</strong> $" . number_format($total_compra, 0, ',', '.') . "</p>
            <p>Este es un correo automático. Por favor, no respondas.</p>
        ";

        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Compra realizada con éxito.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'success', 'message' => 'Compra realizada, pero no se pudo enviar el correo: ' . $mail->ErrorInfo]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ocurrió un error inesperado: ' . $e->getMessage()]);
}
?>
