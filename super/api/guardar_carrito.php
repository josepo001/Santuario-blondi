<?php
session_start();

// Leer los datos enviados en el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data['cart'])) {
    $_SESSION['carrito'] = $data['cart']; // Guardar el carrito en la sesión
    echo json_encode(["status" => "success", "message" => "Carrito guardado en la sesión."]);
    http_response_code(200); // Respuesta exitosa
} else {
    echo json_encode(["status" => "error", "message" => "Datos del carrito no válidos."]);
    http_response_code(400); // Solicitud incorrecta
}
?>
