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
$user = null;

try {
    // Obtener conexión a la base de datos
    $db = getDB();
    if (!$db) {
        throw new Exception("Error al conectar con la base de datos");
    }

    // Obtener datos del usuario actual usando su ID de la sesión
    $stmt = $db->prepare("SELECT nombre, apellido, tipo_usuario FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Almacenar los datos del usuario en $user
    } else {
        throw new Exception("Error al obtener los datos del usuario: " . $stmt->error);
    }

    // Consulta para obtener todas las compras registradas, usando JOIN para obtener el nombre del producto y usuario
    $stmt = $db->prepare("SELECT compras.id, compras.cantidad, compras.fecha, compras.precio_total, productos.nombre AS producto_nombre, usuarios.nombre AS usuario_nombre
                          FROM compras
                          JOIN productos ON compras.producto_id = productos.id
                          JOIN usuarios ON compras.usuario_id = usuarios.id
                          ORDER BY compras.fecha DESC");

    if ($stmt->execute()) {
        $compras = $stmt->get_result();
    } else {
        throw new Exception("Error al obtener las compras registradas: " . $stmt->error);
    }

} catch (Exception $e) {
    $error = $e->getMessage();
    $compras = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/compras.css">
    <title>Registro de Compras</title>
</head>
<body>
<!-- Header -->
<header class="header">
    <div class="header-content">
        <div class="logo">
            <h2>Santuario Blondi</h2>
        </div>

        <nav class="nav-menu">
            <ul>
                <li><a href="homeEm.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="inventario.php"><i class="fas fa-box"></i> Inventario</a></li>
                <li><a href="compras.php"><i class="fas fa-shopping-cart"></i> Registro de compras</a></li>
                <li><a href="perfilEm.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <li><a href="../cerrar-sesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </nav>
        <div class="user-info">
            <i class="fas fa-user-circle" style="font-size: 24px;"></i>
            <span><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></span>
            <small><?php echo ucfirst($user['tipo_usuario']); ?></small>
        </div>
    </div>
</header>

<div class="container">
    <h1>Compras Registradas</h1>

    <!-- Botón para registrar una nueva compra -->
    <div class="button-container">
        <a href="registrar_compras.php" class="btn-registrar">Registrar Nueva Compra</a>
    </div>

    <!-- Mostrar mensaje de éxito o error -->
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Tabla para mostrar compras registradas -->
    <div class="tabla-container">
        <?php if ($compras && $compras->num_rows > 0): ?>
            <table class="tabla-compras">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto/Insumo</th>
                        <th>Cantidad</th>
                        <th>Precio Total</th>
                        <th>Fecha de Compra</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($compra = $compras->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($compra['id']); ?></td>
                            <td><?php echo htmlspecialchars($compra['producto_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($compra['cantidad']); ?></td>
                            <td><?php echo htmlspecialchars($compra['precio_total']); ?></td>
                            <td><?php echo htmlspecialchars($compra['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($compra['usuario_nombre']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay compras registradas o se produjo un error al obtener los datos.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
