<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de conexión a la base de datos
require_once '../Admin/DB.php'; // Asegúrate de que la ruta sea correcta

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirigir al login si no hay sesión
    exit;
}

$success = '';
$error = '';

try {
    // Obtener conexión a la base de datos
    $db = getDB();
    if (!$db) {
        throw new Exception("Error al conectar con la base de datos");
    }

    // Procesar el formulario de registro de compra si es enviado
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Obtener los datos del formulario
        $producto_id = (int) $_POST['producto_id'];
        $cantidad = (int) $_POST['cantidad'];
        $precio_total = (int) $_POST['precio_total']; // Cambiado a entero para la moneda CLP sin decimales
        $fecha = $_POST['fecha_compra'];
        $usuario_id = $_SESSION['user_id']; // ID del usuario actual

        // Preparar la consulta para insertar la compra
        $stmt = $db->prepare("INSERT INTO compras (producto_id, cantidad, fecha, precio_total, usuario_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $db->error);
        }

        $stmt->bind_param("iisdi", $producto_id, $cantidad, $fecha, $precio_total, $usuario_id);
        if ($stmt->execute()) {
            $success = "Compra registrada exitosamente.";

            // Insertar el egreso en la tabla transacciones_diarias
            $stmt_transaccion = $db->prepare("INSERT INTO transacciones_diarias (fecha, ingresos, egresos, utilidad) VALUES (?, 0, ?, -?)");
            if (!$stmt_transaccion) {
                throw new Exception("Error en la preparación de la consulta para transacción: " . $db->error);
            }

            // Bind de fecha y precio total como egreso, con utilidad negativa (igual al egreso)
            $stmt_transaccion->bind_param("sii", $fecha, $precio_total, $precio_total);
            if (!$stmt_transaccion->execute()) {
                throw new Exception("Error al registrar el egreso en transacciones_diarias: " . $stmt_transaccion->error);
            }

            header('Location: compras.php'); // Redirigir a la página principal de compras
            exit;
        } else {
            throw new Exception("Error al registrar la compra: " . $stmt->error);
        }
    }

    // Consulta para obtener la lista de productos
    $productos = $db->query("SELECT id, nombre FROM productos");

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/registrar_compras.css">
    <title>Registrar Nueva Compra</title>
</head>
<body>
<!-- Contenedor principal -->
<div class="container">
    <h1>Registrar Nueva Compra</h1>

    <!-- Mostrar mensaje de error -->
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Formulario para registrar una nueva compra -->
    <form action="registrar_compras.php" method="post">
        <label for="producto_id">Producto o Insumo:</label>
        <select id="producto_id" name="producto_id" required>
            <?php while ($producto = $productos->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($producto['id']); ?>">
                    <?php echo htmlspecialchars($producto['nombre']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="cantidad">Cantidad:</label>
        <input type="number" id="cantidad" name="cantidad" required min="1" placeholder="Ingrese la cantidad">

        <label for="precio_total">Precio Total (CLP):</label>
        <input type="number" id="precio_total" name="precio_total" required placeholder="Ingrese el precio total">

        <label for="fecha_compra">Fecha de Compra:</label>
        <input type="date" id="fecha_compra" name="fecha_compra" value="<?php echo date('Y-m-d'); ?>" required>

        <button type="submit" class="btn-submit">Registrar Compra</button>
    </form>

    <!-- Botón de Volver -->
    <a href="compras.php" class="btn-volver">Volver</a>
</div>
</body>
</html>
