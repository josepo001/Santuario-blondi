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

    // Obtener los datos del usuario logueado
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta del usuario: " . $db->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception("Usuario no encontrado.");
    }

    // Obtener la suma de ingresos, egresos y utilidad del día actual
    $stmt_transaccion = $db->prepare("
        SELECT SUM(IFNULL(ingresos, 0)) AS ingresos, 
               SUM(IFNULL(egresos, 0)) AS egresos, 
               SUM(IFNULL(utilidad, 0)) AS utilidad 
        FROM transacciones_diarias 
        WHERE fecha >= CURDATE() AND fecha < CURDATE() + INTERVAL 1 DAY
    ");
    $stmt_transaccion->execute();
    $result_transaccion = $stmt_transaccion->get_result();
    $transaccionHoy = $result_transaccion->fetch_assoc();

    // Asignar los valores para mostrar en el dashboard
    $ingresosHoy = $transaccionHoy['ingresos'] ?? 0;
    $egresosHoy = $transaccionHoy['egresos'] ?? 0;
    $utilidadHoy = $transaccionHoy['utilidad'] ?? 0;

} catch (Exception $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/homeEm.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Gestión Empleado</title>
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

    <!-- Bienvenida y Tarjetas de Resumen -->
    <div class="dashboard">
        <h1>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?>!</h1>
        
        <div class="card-container">
            <div class="card ingresos">
                <h3>Ingresos Hoy</h3>
                <p class="amount">$<?php echo number_format($ingresosHoy, 0); ?></p>
            </div>
            <div class="card egresos">
                <h3>Egresos Hoy</h3>
                <p class="amount">$<?php echo number_format($egresosHoy, 0); ?></p>
            </div>
            <div class="card utilidad">
                <h3>Utilidad Hoy</h3>
                <p class="amount">$<?php echo number_format($utilidadHoy, 0); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
