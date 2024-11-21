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
        header('Location: login.php');
        exit;
    }

    // Obtener datos diarios
    $stmt_diario = $db->prepare("SELECT ingresos, egresos, utilidad FROM transacciones_diarias WHERE fecha = CURDATE()");
    $stmt_diario->execute();
    $datosDiarios = $stmt_diario->get_result()->fetch_assoc();

    // Obtener datos mensuales del año actual
    $stmt_mensual = $db->prepare("SELECT MONTH(fecha) AS mes, SUM(ingresos) AS total_ingresos, SUM(egresos) AS total_egresos, SUM(utilidad) AS total_utilidad FROM transacciones_diarias WHERE YEAR(fecha) = YEAR(CURDATE()) GROUP BY MONTH(fecha) ORDER BY mes");
    $stmt_mensual->execute();
    $datosMensuales = $stmt_mensual->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obtener datos anuales
    $stmt_anual = $db->prepare("SELECT YEAR(fecha) AS anio, SUM(ingresos) AS total_ingresos, SUM(egresos) AS total_egresos, SUM(utilidad) AS total_utilidad FROM transacciones_diarias GROUP BY YEAR(fecha) ORDER BY anio DESC");
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
        <h2>Resumen Diario (Hoy)</h2>
        <p>Ingresos: $<?php echo number_format($datosDiarios['ingresos'] ?? 0, 0); ?></p>
        <p>Egresos: $<?php echo number_format($datosDiarios['egresos'] ?? 0, 0); ?></p>
        <p>Utilidad: $<?php echo number_format($datosDiarios['utilidad'] ?? 0, 0); ?></p>

        <!-- Gráfico Mensual -->
        <h2>Resumen Mensual (Año Actual)</h2>
        <canvas id="graficoMensual"></canvas>

        <!-- Gráfico Anual -->
        <h2>Resumen Anual</h2>
        <canvas id="graficoAnual"></canvas>
    </div>

    <script>
        // Datos Mensuales para el Gráfico
        const labelsMensuales = <?php echo json_encode(array_column($datosMensuales, 'mes')); ?>;
        const ingresosMensuales = <?php echo json_encode(array_column($datosMensuales, 'total_ingresos')); ?>;
        const egresosMensuales = <?php echo json_encode(array_column($datosMensuales, 'total_egresos')); ?>;
        const utilidadMensual = <?php echo json_encode(array_column($datosMensuales, 'total_utilidad')); ?>;

        // Datos Anuales para el Gráfico
        const labelsAnuales = <?php echo json_encode(array_column($datosAnuales, 'anio')); ?>;
        const ingresosAnuales = <?php echo json_encode(array_column($datosAnuales, 'total_ingresos')); ?>;
        const egresosAnuales = <?php echo json_encode(array_column($datosAnuales, 'total_egresos')); ?>;
        const utilidadAnual = <?php echo json_encode(array_column($datosAnuales, 'total_utilidad')); ?>;

        // Gráfico Mensual
        const ctxMensual = document.getElementById('graficoMensual').getContext('2d');
        const graficoMensual = new Chart(ctxMensual, {
            type: 'bar',
            data: {
                labels: labelsMensuales,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: ingresosMensuales,
                        backgroundColor: 'rgba(56, 142, 60, 0.7)',
                    },
                    {
                        label: 'Egresos',
                        data: egresosMensuales,
                        backgroundColor: 'rgba(198, 40, 40, 0.7)',
                    },
                    {
                        label: 'Utilidad',
                        data: utilidadMensual,
                        backgroundColor: 'rgba(21, 101, 192, 0.7)',
                    }
                ]
            },
            options: {
                scales: {
                    x: { title: { display: true, text: 'Mes' } },
                    y: { title: { display: true, text: 'Monto ($)' } }
                }
            }
        });

        // Gráfico Anual - Barra Horizontal
        const ctxAnual = document.getElementById('graficoAnual').getContext('2d');
        const graficoAnual = new Chart(ctxAnual, {
            type: 'bar',
            data: {
                labels: labelsAnuales,
                datasets: [
                    {
                        label: 'Ingresos',
                        data: ingresosAnuales,
                        backgroundColor: 'rgba(56, 142, 60, 0.7)',
                    },
                    {
                        label: 'Egresos',
                        data: egresosAnuales,
                        backgroundColor: 'rgba(198, 40, 40, 0.7)',
                    },
                    {
                        label: 'Utilidad',
                        data: utilidadAnual,
                        backgroundColor: 'rgba(21, 101, 192, 0.7)',
                    }
                ]
            },
            options: {
                indexAxis: 'y', // Cambia el gráfico a barras horizontales
                scales: {
                    x: { title: { display: true, text: 'Monto ($)' } },
                    y: { title: { display: true, text: 'Año' } }
                }
            }
        });

    </script>
</body>
</html>
