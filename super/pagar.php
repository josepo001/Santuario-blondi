<?php
session_start();
require_once '../Admin/DB.php';

// Verificar si el carrito existe en la sesión
$carrito = $_SESSION['carrito'] ?? []; // Si no hay carrito, usar un array vacío
$subtotal = array_sum(array_column($carrito, 'precio'));
$costo_envio = 7990; // Ejemplo de costo de envío
$total = $subtotal + $costo_envio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/pagar.css">
    <title>Pagar</title>
</head>
<body>
    <div class="container">
        <!-- Sección del formulario -->
        <div class="form-section">
            <h1>Confirma y paga tu compra</h1>
            <form id="payment-form">
                <h2>Tu medio de pago:</h2>
                <label for="numero_tarjeta">Número de Tarjeta:</label>
                <input type="text" id="numero_tarjeta" name="numero_tarjeta" maxlength="16" required>
                <button type="submit" class="btn btn-primary">Pagar ahora</button>
            </form>
        </div>

        <!-- Resumen de la compra -->
        <div class="summary-section">
            <h2>Resumen de la compra</h2>
            <div class="summary-item">
                <p><strong>Productos seleccionados:</strong></p>
                <ul>
                    <?php foreach ($carrito as $producto): ?>
                        <li>
                            <?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="summary-item">
                <p><strong>Sub-Total:</strong> $<?php echo number_format($subtotal, 0, ',', '.'); ?></p>
            </div>
            <div class="summary-item">
                <p><strong>Costo de envío:</strong> $<?php echo number_format($costo_envio, 0, ',', '.'); ?></p>
            </div>
            <div class="total">
                <p><strong>Total:</strong> $<?php echo number_format($total, 0, ',', '.'); ?></p>
            </div>
            <form method="post" action="api/cancelar_compra.php">
                <button type="submit" name="cancelar" class="btn btn-danger">Cancelar compra</button>
            </form>

        </div>
    </div>

    <script>
        document.getElementById('payment-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            fetch('api/procesar_compra.php', { // Cambié la ruta al subdirectorio correcto
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.href = 'tienda.php'; // Redirigir a la tienda
                } else {
                    alert(data.message); // Mostrar mensaje de error
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema al procesar la compra. Inténtelo de nuevo.');
            });
        });
    </script>
</body>
</html>
