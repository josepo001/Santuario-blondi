<?php
// Incluir archivo de conexión a la base de datos
require_once '../Admin/DB.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db = getDB();
        if (!$db) {
            throw new Exception("Error al conectar con la base de datos");
        }

        // Recoger datos del formulario
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $telefono = $_POST['telefono'];
        $monto = (int)$_POST['monto'];
        $fecha = date('Y-m-d'); // Fecha de la donación
        $mensaje = $_POST['mensaje'];

        // Iniciar una transacción para asegurarnos de que ambos registros (donador y transacción) se guarden juntos
        $db->begin_transaction();

        // Insertar donación en la tabla `donadores`
        $stmt = $db->prepare("INSERT INTO donadores (nombre, email, telefono, monto, fecha, mensaje) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $nombre, $email, $telefono, $monto, $fecha, $mensaje);

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar la donación: " . $stmt->error);
        }

        // Insertar el monto donado en `transacciones_diarias` como ingreso
        $stmt_transaccion = $db->prepare("INSERT INTO transacciones_diarias (fecha, ingresos, egresos, utilidad) VALUES (?, ?, 0, ?)");
        $stmt_transaccion->bind_param("sii", $fecha, $monto, $monto);

        if (!$stmt_transaccion->execute()) {
            throw new Exception("Error al registrar la transacción de donación: " . $stmt_transaccion->error);
        }

        // Confirmar la transacción si ambas inserciones son exitosas
        $db->commit();

        $success = "¡Gracias por tu donación!";
    } catch (Exception $e) {
        // Si hay algún error, revertir la transacción
        $db->rollback();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donar - Santuario Blondi</title>
    <link rel="stylesheet" href="../css/donadores.css">
</head>
<body>
<div class="container">
    <h1>Realiza una Donación</h1>

    <!-- Mostrar mensajes de éxito o error -->
    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Formulario para realizar la donación -->
    <form action="donar.php" method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required placeholder="Ingrese su nombre">

        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" placeholder="Ingrese su correo electrónico">

        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" placeholder="Ingrese su número de teléfono">

        <label for="monto">Monto (CLP):</label>
        <input type="number" id="monto" name="monto" required min="1" placeholder="Ingrese el monto de la donación">

        <label for="mensaje">Mensaje (Opcional):</label>
        <textarea id="mensaje" name="mensaje" placeholder="Ingrese un mensaje si desea"></textarea>

        <button type="submit" class="btn-submit">Donar</button>
    </form>

    <!-- Botón de Volver -->
    <a href="homeDona.html" class="btn-volver">Volver</a>
</div>
</body>
</html>
