<?php
// Incluir el archivo de conexión a la base de datos
include 'DB.php';
$conn = getDB();

// Variables para los filtros
$where = [];
$where[] = "tarjeta_id IS NOT NULL"; // Mostrar solo registros con tarjeta asignada

// Filtros dinámicos
if (isset($_GET['tarjeta_id']) && !empty($_GET['tarjeta_id'])) {
    $tarjeta_id = intval($_GET['tarjeta_id']);
    $where[] = "tarjeta_id = $tarjeta_id";
}

if (!empty($_GET['desde']) && !empty($_GET['hasta'])) {
    $desde = $_GET['desde'];
    $hasta = $_GET['hasta'];
    $where[] = "fecha BETWEEN '$desde' AND '$hasta'";
}

if (!empty($_GET['tipo_compra'])) {
    $tipo_compra = $_GET['tipo_compra'];
    $where[] = "tipo_compra = '$tipo_compra'";
}

if (!empty($_GET['monto_min']) && !empty($_GET['monto_max'])) {
    $monto_min = floatval($_GET['monto_min']);
    $monto_max = floatval($_GET['monto_max']);
    $where[] = "egresos BETWEEN $monto_min AND $monto_max";
}

// Generar consulta SQL
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT fecha, descripcion, egresos, tarjeta_id FROM transacciones_diarias $where_sql ORDER BY fecha ASC";
$result = $conn->query($query);

// Guardar los resultados
$transacciones = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transacciones[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
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

    <h1>Historial de Transacciones</h1>

    <!-- Formulario de Filtros -->
    <form method="GET" action="">
        <label for="tarjeta_id">Tarjeta ID:</label>
        <input type="number" id="tarjeta_id" name="tarjeta_id" value="<?= htmlspecialchars($_GET['tarjeta_id'] ?? '') ?>">

        <label for="desde">Desde:</label>
        <input type="date" id="desde" name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>">

        <label for="hasta">Hasta:</label>
        <input type="date" id="hasta" name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>">

        <label for="monto_min">Monto Mínimo:</label>
        <input type="number" id="monto_min" name="monto_min" step="0.01" value="<?= htmlspecialchars($_GET['monto_min'] ?? '') ?>">

        <label for="monto_max">Monto Máximo:</label>
        <input type="number" id="monto_max" name="monto_max" step="0.01" value="<?= htmlspecialchars($_GET['monto_max'] ?? '') ?>">

        <button type="submit">Aplicar Filtros</button>
        <a href="historial.php" class="btn-reset">Limpiar Filtros</a>
    </form>

    <!-- Tabla de Resultados -->
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Tarjeta ID</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transacciones)): ?>
                <?php foreach ($transacciones as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['fecha']) ?></td>
                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                        <td>$<?= number_format($row['egresos'], 2) ?></td>
                        <td><?= htmlspecialchars($row['tarjeta_id']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No se encontraron transacciones.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
