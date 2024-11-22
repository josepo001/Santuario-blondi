<?php
// Incluir el archivo de conexión a la base de datos
include 'DB.php';
$conn = getDB();

// Consultar todas las transacciones con tarjeta
$query = "SELECT fecha, descripcion, egresos, tarjeta_id FROM transacciones_diarias WHERE tarjeta_id IS NOT NULL ORDER BY fecha ASC";
$result = $conn->query($query);

// Convertir los resultados a JSON
$transacciones = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transacciones[] = $row;
    }
}

// Enviar respuesta JSON
header('Content-Type: application/json');
echo json_encode($transacciones);
?>
