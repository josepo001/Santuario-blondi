<?php
// Conexión a la base de datos
include 'DB.php'; // Asegúrate de que este archivo conecta correctamente a la DB
$conn = getDB();

// Variables para filtros
$where = [];

// Mostrar solo registros con tarjeta_id asignado
$where[] = "tarjeta_id IS NOT NULL";

// Filtrar por tarjeta
if (isset($_GET['tarjeta_id']) && !empty($_GET['tarjeta_id'])) {
    $tarjeta_id = intval($_GET['tarjeta_id']);
    $where[] = "tarjeta_id = $tarjeta_id";
}

// Filtrar por rango de fechas
if (!empty($_GET['desde']) && !empty($_GET['hasta'])) {
    $desde = $_GET['desde'];
    $hasta = $_GET['hasta'];
    $where[] = "fecha BETWEEN '$desde' AND '$hasta'";
}

// Generar condición WHERE si hay filtros
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Consultar transacciones
$query = "SELECT fecha, descripcion, ingresos, egresos, utilidad, tarjeta_id FROM transacciones_diarias $where_sql ORDER BY fecha ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/historial.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Historial de Transacciones</title>
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

    <!-- Rest of the page content -->
    <h1>Historial de Transacciones</h1>

    <!-- Formulario de filtros -->
    <form method="GET" action="">
        <label for="tarjeta_id">Tarjeta ID:</label>
        <input type="number" name="tarjeta_id" id="tarjeta_id" value="<?= isset($_GET['tarjeta_id']) ? $_GET['tarjeta_id'] : '' ?>">

        <label for="desde">Desde:</label>
        <input type="date" name="desde" id="desde" value="<?= isset($_GET['desde']) ? $_GET['desde'] : '' ?>">

        <label for="hasta">Hasta:</label>
        <input type="date" name="hasta" id="hasta" value="<?= isset($_GET['hasta']) ? $_GET['hasta'] : '' ?>">

        <button type="submit">Aplicar Filtros</button>
        <a href="historial.php" class="btn-reset">Limpiar Filtros</a>
    </form>

    <!-- Tabla de resultados -->
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Egresos</th>
                <th>Tarjeta ID</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fecha']) ?></td>
                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                        <td>$<?= number_format($row['egresos'], 2) ?></td>
                        <td><?= htmlspecialchars($row['tarjeta_id']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No se encontraron transacciones.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
