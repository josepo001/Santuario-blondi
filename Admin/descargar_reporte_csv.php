<?php
// Incluir archivo de conexión a la base de datos
require_once '../Admin/DB.php';

// Validar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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

    // Filtro por mes
    $filtroFecha = $_GET['fecha'] ?? date('Y-m');
    $stmt = $db->prepare("SELECT * FROM transacciones_diarias WHERE DATE_FORMAT(fecha, '%Y-%m') = ?");
    $stmt->bind_param("s", $filtroFecha);
    $stmt->execute();
    $resultados = $stmt->get_result();

    // Encabezados para la descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_' . $filtroFecha . '.csv"');

    // Crear archivo CSV
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Fecha', 'Ingresos', 'Egresos', 'Utilidad']); // Encabezados
    while ($row = $resultados->fetch_assoc()) {
        fputcsv($output, [$row['fecha'], $row['ingresos'], $row['egresos'], $row['utilidad']]);
    }
    fclose($output);
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
