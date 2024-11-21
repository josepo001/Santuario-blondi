<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de conexión a la base de datos
require_once '../Admin/DB.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Conexión a la base de datos
try {
    $db = getDB();

    if (!$db) {
        throw new Exception("Error al conectar con la base de datos");
    }

    // Obtener información del usuario logueado
    $stmt_user = $db->prepare("SELECT nombre, apellido, tipo_usuario FROM usuarios WHERE id = ?");
    $stmt_user->bind_param("i", $_SESSION['user_id']);
    $stmt_user->execute();
    $user = $stmt_user->get_result()->fetch_assoc();

    if (!$user) {
        throw new Exception("Usuario no encontrado.");
    }

    // Consultar transacciones o datos de ejemplo para el reporte
    $filtroFecha = $_GET['fecha'] ?? date('Y-m'); // Filtro de ejemplo por mes actual
    $stmt = $db->prepare("SELECT * FROM transacciones_diarias WHERE DATE_FORMAT(fecha, '%Y-%m') = ?");
    $stmt->bind_param("s", $filtroFecha);
    $stmt->execute();
    $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Financiero</title>
    <link rel="stylesheet" href="../css/reporte.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <h2>Santuario Blondi</h2>
        </div>
        <nav class="nav-menu">
            <ul>
                <li><a href="usuarios.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas</a></li>
                <li><a href="historial.php"><i class="fas fa-history"></i> Historial</a></li>
                <li><a href="perfilAdmin.php"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
                <li><a href="reporte.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                <li><a href="../cerrar-sesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <div class="dashboard">
        <h1>Reporte Financiero</h1>
        
        <!-- Filtros -->
        <form method="GET" action="reporte.php" class="form-filtros">
            <label for="fecha">Filtrar por mes:</label>
            <input type="month" id="fecha" name="fecha" value="<?php echo htmlspecialchars($filtroFecha); ?>">
            <button type="submit" class="btn-filtrar">Aplicar Filtro</button>
        </form>

        <!-- Tabla de Resultados -->
        <table class="tabla-reporte">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ingresos</th>
                    <th>Egresos</th>
                    <th>Utilidad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        <td>$<?php echo number_format($row['ingresos'], 0); ?></td>
                        <td>$<?php echo number_format($row['egresos'], 0); ?></td>
                        <td>$<?php echo number_format($row['utilidad'], 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Botón para descargar el reporte -->
        <a href="descargar_reporte.php?fecha=<?php echo urlencode($filtroFecha); ?>" class="btn-descargar">Descargar Reporte en PDF</a>
    </div>
</body>
</html>
