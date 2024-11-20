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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capturar los datos del formulario
    $nombre = trim($_POST['nombre']);
    $peso = (float) $_POST['peso'];
    $unidad = trim($_POST['unidad']);
    $precio = (int) $_POST['precio'];
    $marca_id = (int) $_POST['marca_id'];

    try {
        // Obtener conexión a la base de datos
        $db = getDB();

        // Verificar que la conexión se realizó correctamente
        if (!$db) {
            throw new Exception("Error al conectar con la base de datos");
        }

        // Preparar consulta para insertar un nuevo producto
        $stmt = $db->prepare("INSERT INTO productos (nombre, peso, unidad, precio, marca_id) 
                              VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $db->error);
        }

        // Vincular los parámetros y ejecutar la consulta
        $stmt->bind_param("sdssi", $nombre, $peso, $unidad, $precio, $marca_id);
        if ($stmt->execute()) {
            $success = "Producto añadido exitosamente";
        } else {
            throw new Exception("Error al insertar el producto: " . $stmt->error);
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/agregar_producto.css">
    <title>Añadir Producto</title>
</head>
<body>
    <!-- Botón de Volver -->
    <div class="volver-container">
        <a href="inventario.php" class="btn-volver">Volver</a>
    </div>

    <!-- Formulario para añadir productos -->
    <div class="container">
        <h1>Añadir Nuevo Producto</h1>

        <!-- Mostrar mensaje de éxito o error -->
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="agregar_producto.php" method="post">
            <label for="nombre">Nombre del Producto:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="peso">Peso (gramos o mililitros):</label>
            <input type="number" step="0.1" id="peso" name="peso" required>

            <label for="unidad">Unidad de Medida:</label>
            <select id="unidad" name="unidad" required>
                <option value="gramos">Gramos</option>
                <option value="mililitros">Mililitros</option>
            </select>

            <label for="precio">Precio:</label>
            <input type="number" step="1" id="precio" name="precio" required>

            <label for="marca_id">ID de la Marca:</label>
            <input type="number" id="marca_id" name="marca_id" required> <!-- Marca debe existir previamente en la tabla de marcas -->

            <button type="submit">Añadir Producto</button>
        </form>
    </div>
</body>
</html>
