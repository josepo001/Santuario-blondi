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

try {
    // Obtener conexión a la base de datos
    $db = getDB(); 

    // Verificar que la conexión se realizó correctamente
    if (!$db) {
        throw new Exception("Error al conectar con la base de datos");
    }

    // Preparar consulta para obtener datos del usuario logueado
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta del usuario: " . $db->error);
    }

    // Vincular el parámetro de la sesión
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // Obtener los datos del usuario logueado

    // Verificar que el usuario fue encontrado
    if (!$user) {
        throw new Exception("Usuario no encontrado.");
    }

    // Preparar consulta para obtener productos y el nombre de la marca
    $stmt = $db->prepare("SELECT productos.*, marca.nombre AS marca_nombre 
                          FROM productos 
                          LEFT JOIN marca ON productos.marca_id = marca.id");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta de productos: " . $db->error);
    }

    // Ejecutar la consulta
    $stmt->execute();
    $productos = $stmt->get_result(); // Obtener el resultado de los productos

} catch (Exception $e) {
    // Mostrar el error directamente para depuración
    echo "Error en la consulta: " . $e->getMessage();
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inventario.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Gestión de Inventario</title>
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
                    <li><a href="inventario.php"><i class="fas fa-box"></i> Inventario</a></li> <!-- Icono de caja para inventario -->
                    <li><a href="compras.php"><i class="fas fa-shopping-cart"></i> Registro de compras</a></li> <!-- Icono de carrito para registro de compras -->
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

    <!-- Contenedor para la tabla de inventario -->
    <div class="tabla-inventario-container">
        <h2>Productos en Inventario</h2>
        <table class="tabla-inventario">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Peso</th>
                    <th>Unidad</th>
                    <th>Precio</th>
                    <th>Marca</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <!-- Bucle para obtener los productos -->
                <?php while ($producto = $productos->fetch_assoc()): 
                    // Determinar el estado del producto basado en la cantidad
                    $estadoClase = '';
                    if ($producto['cantidad'] > 20) {
                        $estadoClase = 'estado-verde'; // Lleno
                    } elseif ($producto['cantidad'] > 10) {
                        $estadoClase = 'estado-amarillo'; // Le queda poco
                    } else {
                        $estadoClase = 'estado-rojo'; // Vacío
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['id']); ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['peso']); ?></td>
                        <td><?php echo htmlspecialchars($producto['unidad']); ?></td>
                        <td><?php echo htmlspecialchars($producto['precio']); ?></td>
                        <td><?php echo htmlspecialchars($producto['marca_nombre']); ?></td> <!-- Cambiado a marca_nombre -->
                        <td class="<?php echo $estadoClase; ?>">
                            <?php echo ucfirst(str_replace('estado-', '', $estadoClase)); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Botón para añadir un nuevo producto -->
        <div style="text-align: right; margin-top: 20px;">
            <a href="agregar_producto.php" class="btn-agregar">Añadir Producto</a>
        </div>
    </div>
</body>
</html>
