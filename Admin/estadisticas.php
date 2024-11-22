<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de conexión a la base de datos
require_once '../Admin/DB.php'; 

try {
    // Obtener conexión a la base de datos
    $db = getDB(); 

    if (!$db) {
        throw new Exception("Error al conectar con la base de datos");
    }

    // Obtener los datos del usuario logueado
    if (isset($_SESSION['user_id'])) {
        $stmt_user = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt_user->bind_param("i", $_SESSION['user_id']);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        $user = $result_user->fetch_assoc();
    } else {
        // Redirigir al login si no hay sesión
        header('Location: index.php');
        exit;
    }

    // Obtener datos diarios acumulados (suma de ingresos, egresos y utilidad del día actual)
    $stmt_diario = $db->prepare("
        SELECT 
            SUM(ingresos) AS ingresos, 
            SUM(egresos) AS egresos, 
            SUM(utilidad) AS utilidad 
        FROM transacciones_diarias 
        WHERE fecha = CURDATE()
    ");
    $stmt_diario->execute();
    $datosDiarios = $stmt_diario->get_result()->fetch_assoc();

    // Obtener datos mensuales del año actual
    $stmt_mensual = $db->prepare("
        SELECT 
            MONTH(fecha) AS mes, 
            SUM(ingresos) AS total_ingresos, 
            SUM(egresos) AS total_egresos, 
            SUM(utilidad) AS total_utilidad 
        FROM transacciones_diarias 
        WHERE YEAR(fecha) = YEAR(CURDATE()) 
        GROUP BY MONTH(fecha) 
        ORDER BY mes
    ");
    $stmt_mensual->execute();
    $datosMensuales = $stmt_mensual->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obtener datos anuales
    $stmt_anual = $db->prepare("
        SELECT 
            YEAR(fecha) AS anio, 
            SUM(ingresos) AS total_ingresos, 
            SUM(egresos) AS total_egresos, 
            SUM(utilidad) AS total_utilidad 
        FROM transacciones_diarias 
        GROUP BY YEAR(fecha) 
        ORDER BY anio DESC
    ");
    $stmt_anual->execute();
    $datosAnuales = $stmt_anual->get_result()->fetch_all(MYSQLI_ASSOC);

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
    <title>Estadísticas Financieras</title>
    <link rel="stylesheet" href="../css/estadistica.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li><a href="usuarios.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas</a></li>
                    <li><a href="historial.php"><i class="fas fa-history"></i> Historial</a></li>
                    <li><a href="perfilAdmin.php"><i class="fas fa-user-circle"></i> Mi Perfil</a></li>
                    <li><a href="reporte.php"><i class="fas fa-file-alt"></i> Reportes</a></li>
                    <li><a href="../cerrar-sesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    
                </ul>
            </nav>
        </div>
    </header>

    <div class="dashboard">
        <h1>Estadísticas Financieras</h1>

        <!-- Resumen Diario -->
        <h3>Resumen Diario (Hoy)</h3>
        <p>Ingresos: $<?php echo number_format($datosDiarios['ingresos'] ?? 0, 0); ?></p>
        <p>Egresos: $<?php echo number_format($datosDiarios['egresos'] ?? 0, 0); ?></p>
        <p>Utilidad: $<?php echo number_format($datosDiarios['utilidad'] ?? 0, 0); ?></p>

        <!-- Gráfico Mensual -->
        <h3>Resumen Mensual (Año Actual)</h3>
        <canvas id="graficoMensual" width="800" height="400"></canvas>

        <!-- Gráfico Anual -->
        <h3>Resumen Anual</h3>
        <canvas id="graficoAnual" width="800" height="400"></canvas>
    </div>

    <!-- Cargar datos para el script -->
    <script>
        const datosEstadisticas = {
            datosMensuales: <?php echo json_encode($datosMensuales); ?>,
            datosAnuales: <?php echo json_encode($datosAnuales); ?>
        };
    </script>

    <script src="js/estadisticas.js"></script>
</body>
</html>
